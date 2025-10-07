<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy {--dry-run : Show commands without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy application to production server via SSH';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting deployment...');

        // Get SSH credentials from environment
        $sshHost = config('app.ssh.host');
        $sshUsername = config('app.ssh.username');
        $sshPort = config('app.ssh.port');
        $sshPassword = config('app.ssh.password');

        if (!$sshHost || !$sshUsername || !$sshPort || !$sshPassword) {
            $this->error('❌ SSH credentials not found in environment variables.');
            $this->error('Please ensure SSH_HOST, SSH_USERNAME, SSH_PORT, and SSH_PASSWORD are set in .env file.');
            return 1;
        }

        // Define deployment commands
        $deploymentCommands = [
            'cd public_html',
            'git pull origin main',
//            'composer install --no-dev --optimize-autoloader',
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache',
            'php artisan migrate --force',
//            'php artisan queue:restart',
//            'npm install --production',
//            'npm run build',
        ];

        $this->info("📡 Connecting to {$sshUsername}@{$sshHost}:{$sshPort}");

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - Commands will not be executed');
            $this->info('SSH Connection: sshpass -p "***" ssh -p ' . $sshPort . ' ' . $sshUsername . '@' . $sshHost);
            $this->info('Commands to execute:');
            foreach ($deploymentCommands as $command) {
                $this->line("  → {$command}");
            }
            return 0;
        }

        // Check if sshpass is available
        if (!$this->commandExists('sshpass')) {
            $this->error('❌ sshpass command not found. Please install it: sudo apt-get install sshpass');
            return 1;
        }

        // Combine all commands with proper error handling
        $remoteCommands = implode(' && ', array_map(function ($cmd) {
            return "echo '🔄 Executing: {$cmd}' && {$cmd}";
        }, $deploymentCommands));

        // Construct SSH command with password
        $sshCommand = [
            'sshpass',
            '-p',
            $sshPassword,
            'ssh',
            '-p',
            $sshPort,
            '-o',
            'StrictHostKeyChecking=no',
            '-o',
            'UserKnownHostsFile=/dev/null',
            "{$sshUsername}@{$sshHost}",
            $remoteCommands
        ];

        $this->info('🔄 Executing deployment commands...');

        // Execute the SSH command
        $process = new Process($sshCommand);
        $process->setTimeout(300); // 5 minutes timeout

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error($buffer);
            } else {
                $this->line($buffer);
            }
        });

        if ($process->isSuccessful()) {
            $this->info('✅ Deployment completed successfully!');
            return 0;
        } else {
            $this->error('❌ Deployment failed!');
            $this->error('Exit code: ' . $process->getExitCode());
            return 1;
        }
    }

    /**
     * Check if a command exists on the system
     */
    private function commandExists(string $command): bool
    {
        $process = new Process(['which', $command]);
        $process->run();

        return $process->isSuccessful();
    }
}
