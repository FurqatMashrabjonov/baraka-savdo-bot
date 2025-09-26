@php
    use App\Enums\ParcelStatus;
    
    $deliverySteps = [
        ['status' => ParcelStatus::CREATED, 'label' => __('filament.status_created'), 'icon' => 'heroicon-o-plus-circle'],
        ['status' => ParcelStatus::ARRIVED_CHINA, 'label' => __('filament.status_arrived_china'), 'icon' => 'heroicon-o-truck'],
        ['status' => ParcelStatus::ARRIVED_UZB, 'label' => __('filament.status_arrived_uzb'), 'icon' => 'heroicon-o-building-office'],
        ['status' => ParcelStatus::DELIVERED, 'label' => __('filament.status_delivered'), 'icon' => 'heroicon-o-check-circle'],
    ];
    
    $currentStepIndex = array_search($record->status, array_column($deliverySteps, 'status'));
@endphp

<div class="space-y-6">
    {{-- Delivery Progress Tracker --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <x-heroicon-o-map class="w-4 h-4 mr-2 text-blue-500"/>
            {{ __('filament.delivery_progress') }}
        </h3>
        
        <div class="flex items-center justify-between">
            @foreach ($deliverySteps as $index => $step)
                @php
                    $isCompleted = $index <= $currentStepIndex;
                    $isCurrent = $index === $currentStepIndex;
                @endphp
                
                <div class="flex flex-col items-center flex-1">
                    {{-- Step Circle --}}
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300
                            {{ $isCompleted 
                                ? 'bg-green-500 border-green-500 text-white' 
                                : 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400'
                            }}
                            {{ $isCurrent ? 'ring-2 ring-green-200 dark:ring-green-800' : '' }}">
                            @if ($isCompleted)
                                <x-heroicon-s-check class="w-4 h-4"/>
                            @else
                                <x-dynamic-component :component="$step['icon']" class="w-4 h-4"/>
                            @endif
                        </div>
                        
                        {{-- Current Step Pulse --}}
                        @if ($isCurrent)
                            <div class="absolute -inset-1 rounded-full border-2 border-green-400 animate-pulse"></div>
                        @endif
                    </div>
                    
                    {{-- Step Label --}}
                    <div class="text-center mt-3">
                        <p class="text-sm font-medium {{ $isCompleted ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $step['label'] }}
                        </p>
                        
                        {{-- Date Information --}}
                        @if ($step['status'] === ParcelStatus::CREATED && $record->created_at)
                            <p class="text-xs text-gray-400 mt-1">{{ $record->created_at->format('d.m.Y H:i') }}</p>
                        @elseif ($step['status'] === ParcelStatus::ARRIVED_CHINA && $record->china_uploaded_at)
                            <p class="text-xs text-gray-400 mt-1">{{ $record->china_uploaded_at->format('d.m.Y H:i') }}</p>
                        @elseif ($step['status'] === ParcelStatus::ARRIVED_UZB && $record->uzb_uploaded_at)
                            <p class="text-xs text-gray-400 mt-1">{{ $record->uzb_uploaded_at->format('d.m.Y H:i') }}</p>
                        @elseif ($step['status'] === ParcelStatus::DELIVERED && $record->delivery_date)
                            <p class="text-xs text-gray-400 mt-1">{{ $record->delivery_date->format('d.m.Y H:i') }}</p>
                        @endif
                    </div>
                    
                    {{-- Connecting Line --}}
                    @if (!$loop->last)
                        <div class="absolute top-6 left-1/2 w-full h-0.5 -translate-y-1/2 translate-x-6
                            {{ $index < $currentStepIndex ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Parcel Information Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Basic Information --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <x-heroicon-o-information-circle class="w-4 h-4 mr-2 text-blue-500"/>
                {{ __('filament.basic_information') }}
            </h3>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.track_number') }}:</span>
                    <p class="text-sm text-gray-900 dark:text-white font-mono">{{ $record->track_number }}</p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.client_name') }}:</span>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $record->client ? $record->client->full_name . ' (' . $record->client->phone . ')' : __('filament.unassigned') }}
                    </p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.weight') }}:</span>
                    <p class="text-sm text-gray-900 dark:text-white">
                        {{ $record->weight ? number_format($record->weight, 3) . ' kg' : __('filament.not_set') }}
                    </p>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.status') }}:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ match($record->status) {
                            ParcelStatus::CREATED => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                            ParcelStatus::ARRIVED_CHINA => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-300',
                            ParcelStatus::ARRIVED_UZB => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300',
                            ParcelStatus::DELIVERED => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                        } }}">
                        {{ $record->status->getLabel() }}
                    </span>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.is_banned') }}:</span>
                    <span class="inline-flex items-center">
                        @if ($record->is_banned)
                            <x-heroicon-s-x-circle class="w-4 h-4 text-red-500 mr-1"/>
                            <span class="text-sm text-red-600 dark:text-red-400">{{ __('filament.banned') }}</span>
                        @else
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 mr-1"/>
                            <span class="text-sm text-green-600 dark:text-green-400">{{ __('filament.allowed') }}</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <x-heroicon-o-currency-dollar class="w-4 h-4 mr-2 text-green-500"/>
                {{ __('filament.payment_information') }}
            </h3>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.payment_status') }}:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ match($record->payment_status) {
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-300',
                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-300',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-300',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                        } }}">
                        {{ match($record->payment_status) {
                            'pending' => __('filament.payment_pending'),
                            'paid' => __('filament.payment_paid'),
                            'cancelled' => __('filament.payment_cancelled'),
                            default => __('filament.not_set')
                        } }}
                    </span>
                </div>
                
                @if ($record->payment_amount_usd || $record->payment_amount_uzs)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.payment_amount') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->getFormattedPaymentAmount() }}</p>
                    </div>
                @endif
                
                @if ($record->payment_date)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.payment_date') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->payment_date->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
                
                @if ($record->payment_type)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.payment_type') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->payment_type === 'offline' ? __('filament.cash_payment') : __('filament.online_payment') }}
                        </p>
                    </div>
                @endif
                
                @if ($record->payment_notes)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.payment_notes') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->payment_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Timestamps --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <x-heroicon-o-clock class="w-5 h-5 mr-2 text-purple-500"/>
                {{ __('filament.timeline') }}
            </h3>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.created_at') }}:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('d.m.Y H:i') }}</p>
                </div>
                
                @if ($record->china_uploaded_at)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.china_uploaded_at') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->china_uploaded_at->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
                
                @if ($record->uzb_uploaded_at)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.uzb_uploaded_at') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->uzb_uploaded_at->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
                
                @if ($record->delivery_date)
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.delivery_date') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->delivery_date->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
                
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.updated_at') }}:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->updated_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Expected Delivery Cost (if pricing is available) --}}
        @php
            $pricing = \App\Models\PricingSetting::getActivePricing();
        @endphp
        @if ($pricing && $record->weight)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <x-heroicon-o-calculator class="w-5 h-5 mr-2 text-indigo-500"/>
                    {{ __('filament.expected_delivery_cost') }}
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.delivery_rate') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ '$' . number_format($pricing->price_per_kg_usd, 2) }} {{ __('filament.per_kg') }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.calculation') }}:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->weight }} kg × ${{ number_format($pricing->price_per_kg_usd, 2) }} = ${{ number_format($pricing->calculateExpectedAmountUsd($record->weight), 2) }}
                        </p>
                    </div>
                    
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.expected_amount') }}:</span>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($pricing->calculateExpectedAmountUsd($record->weight), 2) }}
                            </span>
                            <span class="text-sm text-gray-500">≈</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($pricing->calculateExpectedAmountUzs($record->weight), 0, '.', ' ') }} UZS
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>