@php
    use App\Models\Parcel;
    use Carbon\Carbon;
    
    // Basic stats
    $totalPayments = $user->processedPayments()->count();
    $totalUsdAmount = $user->processedPayments()->sum('payment_amount_usd');
    $totalUzsAmount = $user->processedPayments()->sum('payment_amount_uzs');
    
    // This month stats
    $thisMonthPayments = $user->processedPayments()
        ->whereMonth('payment_date', now()->month)
        ->whereYear('payment_date', now()->year)
        ->count();
    
    $thisMonthUsd = $user->processedPayments()
        ->whereMonth('payment_date', now()->month)
        ->whereYear('payment_date', now()->year)
        ->sum('payment_amount_usd');
    
    $thisMonthUzs = $user->processedPayments()
        ->whereMonth('payment_date', now()->month)
        ->whereYear('payment_date', now()->year)
        ->sum('payment_amount_uzs');
    
    // Today stats
    $todayPayments = $user->processedPayments()
        ->whereDate('payment_date', today())
        ->count();
    
    // Average per day this month  
    $daysInMonth = now()->day;
    $avgPerDay = $daysInMonth > 0 ? round($thisMonthPayments / $daysInMonth, 1) : 0;
    
    // Last 7 days activity
    $last7Days = collect();
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $count = $user->processedPayments()
            ->whereDate('payment_date', $date)
            ->count();
        $last7Days->push([
            'date' => $date->format('M j'),
            'count' => $count
        ]);
    }
    
    // Peak performance day
    $peakDay = $user->processedPayments()
        ->selectRaw('DATE(payment_date) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('count', 'desc')
        ->first();
        
    // Role info
    $userRoles = $user->roles->pluck('name')->implode(', ');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
    {{-- Overview Stats --}}
    <div class="col-span-full">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('filament.overview_statistics') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalPayments) }}</div>
                <div class="text-sm text-blue-600 dark:text-blue-400">{{ __('filament.total_payments') }}</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($totalUsdAmount, 2) }}</div>
                <div class="text-sm text-green-600 dark:text-green-400">{{ __('filament.total_usd') }}</div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($totalUzsAmount, 0, '.', ' ') }} UZS</div>
                <div class="text-sm text-yellow-600 dark:text-yellow-400">{{ __('filament.total_uzs') }}</div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $avgPerDay }}</div>
                <div class="text-sm text-purple-600 dark:text-purple-400">{{ __('filament.avg_per_day') }}</div>
            </div>
        </div>
    </div>

    {{-- This Month Performance --}}
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg>
            {{ __('filament.this_month_performance') }}
        </h4>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.payments_processed') }}:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($thisMonthPayments) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.usd_processed') }}:</span>
                <span class="font-semibold text-green-600 dark:text-green-400">${{ number_format($thisMonthUsd, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.uzs_processed') }}:</span>
                <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($thisMonthUzs, 0, '.', ' ') }} UZS</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.today_payments') }}:</span>
                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ number_format($todayPayments) }}</span>
            </div>
        </div>
    </div>

    {{-- User Information --}}
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            {{ __('filament.user_information') }}
        </h4>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.name') }}:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.email') }}:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $user->email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.roles') }}:</span>
                <span class="font-medium">
                    @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($role->name === 'admin') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($role->name === 'kassir_china') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">{{ __('filament.location') }}:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    @if($user->location === 'china')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            {{ __('filament.china') }}
                        </span>
                    @elseif($user->location === 'uzbekistan')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ __('filament.uzbekistan') }}
                        </span>
                    @else
                        <span class="text-gray-500">—</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Last 7 Days Activity Chart --}}
    <div class="col-span-full bg-white dark:bg-gray-900 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
            </svg>
            {{ __('filament.last_7_days_activity') }}
        </h4>
        <div class="flex items-end justify-between h-32 space-x-2">
            @foreach($last7Days as $day)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full bg-blue-500 rounded-t" style="height: {{ $day['count'] > 0 ? max(8, ($day['count'] / max($last7Days->max('count'), 1)) * 100) : 4 }}px;"></div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">{{ $day['date'] }}</div>
                    <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ $day['count'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Performance Insights --}}
    <div class="col-span-full bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 rounded-lg border border-blue-200 dark:border-blue-700">
        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            {{ __('filament.performance_insights') }}
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    @if($peakDay)
                        {{ $peakDay->count }} 
                    @else
                        0
                    @endif
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('filament.best_single_day') }}</div>
                @if($peakDay)
                    <div class="text-xs text-gray-500">{{ Carbon::parse($peakDay->date)->format('M j, Y') }}</div>
                @endif
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->created_at->diffForHumans() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('filament.member_since') }}</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    @if($totalPayments > 0)
                        {{ round(($totalUsdAmount + ($totalUzsAmount / 12000)) / $totalPayments, 2) }}
                    @else
                        0
                    @endif
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ __('filament.avg_payment_value') }} (USD)</div>
            </div>
        </div>
    </div>
</div>