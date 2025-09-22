<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_dashboard',
            'manage_parcels',
            'create_parcels',
            'edit_parcels',
            'delete_parcels',
            'import_china_excel',
            'import_uzb_excel',
            'manage_clients',
            'view_feedback',
            'reply_feedback',
            'view_monitoring',
            'make_payments',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Admin role with all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create China Kassir role
        $chinaKassirRole = Role::create(['name' => 'kassir_china']);
        $chinaKassirRole->givePermissionTo([
            'import_china_excel',
            'make_payments',
        ]);

        // Create Uzbekistan Kassir role
        $uzbKassirRole = Role::create(['name' => 'kassir_uzb']);
        $uzbKassirRole->givePermissionTo([
            'import_uzb_excel',
            'make_payments',
        ]);

        // Create Admin user
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@baraka.uz',
            'password' => Hash::make('admin123'),
            'location' => null,
        ]);
        $admin->assignRole('admin');

        // Create China Kassir user
        $chinaKassir = User::create([
            'name' => 'China Kassir',
            'email' => 'kassir.china@baraka.uz',
            'password' => Hash::make('china123'),
            'location' => 'china',
        ]);
        $chinaKassir->assignRole('kassir_china');

        // Create Uzbekistan Kassir user
        $uzbKassir = User::create([
            'name' => 'Uzbekistan Kassir',
            'email' => 'kassir.uzb@baraka.uz',
            'password' => Hash::make('uzbekistan123'),
            'location' => 'uzbekistan',
        ]);
        $uzbKassir->assignRole('kassir_uzb');

        $this->command->info('Roles, permissions, and users created successfully!');
        $this->command->info('Admin: admin@baraka.uz / admin123');
        $this->command->info('China Kassir: kassir.china@baraka.uz / china123');
        $this->command->info('Uzbekistan Kassir: kassir.uzb@baraka.uz / uzbekistan123');
    }
}
