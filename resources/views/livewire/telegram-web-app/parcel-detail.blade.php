{{-- Parcel Detail Page --}}
<div class="max-w-md mx-auto bg-white dark:bg-neutral-900 min-h-screen">
    @if(!$client || !$parcel)
        {{-- No Parcel Found --}}
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 mb-2">{{ __('dashboard.parcel_not_found') }}</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400">{{ __('dashboard.invalid_parcel') }}</p>
                <button wire:click="goBack" class="mt-4 text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('dashboard.go_back') }}
                </button>
            </div>
        </div>
    @else
        {{-- Header --}}
        <div class="p-4 border-b border-gray-100 dark:border-neutral-800">
            <div class="flex items-center gap-3 mb-4">
                <button wire:click="goBack" class="p-2 hover:bg-gray-100 dark:hover:bg-neutral-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">{{ __('dashboard.parcel_details') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">{{ $parcel->track_number }}</p>
                </div>
                <x-ui.theme-switcher variant="simple" />
            </div>
        </div>

        {{-- Parcel Summary Card --}}
        <div class="p-4">
            {{-- Additional Information --}}
            <div class="space-y-4">
                <x-ui.card class="w-full p-4">
                    <h4 class="font-medium text-gray-900 dark:text-neutral-100 mb-3">{{ __('dashboard.parcel_info') }}</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-neutral-400">{{ __('dashboard.track_number') }}:</span>
                            <span class="text-gray-900 dark:text-neutral-100 font-mono">{{ $parcel->track_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-neutral-400">{{ __('dashboard.weight') }}:</span>
                            <span class="text-gray-900 dark:text-neutral-100">2.5 {{ __('dashboard.kg') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-neutral-400">{{ __('dashboard.delivery_cost') }}:</span>
                            <span class="text-gray-900 dark:text-neutral-100">6 {{ __('dashboard.currency') }}</span>
                        </div>
{{--                        <div class="flex justify-between">--}}
{{--                            <span class="text-gray-500 dark:text-neutral-400">{{ __('dashboard.status') }}:</span>--}}
{{--                            <span class="text-gray-900 dark:text-neutral-100">{{ $parcel->getStatusLabel() }}</span>--}}
{{--                        </div>--}}
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-neutral-400">{{ __('dashboard.created_date') }}:</span>
                            <span class="text-gray-900 dark:text-neutral-100">{{ $parcel->created_at ? $parcel->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
            <x-ui.separator class="my-2" vertical />
            {{-- Delivery Progress --}}
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-4">{{ __('dashboard.delivery_progress') }}</h3>

                <div class="space-y-4">
                    @foreach($progressSteps as $key => $step)
                        <div class="flex items-start gap-3">
                            {{-- Progress Icon --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step['completed'] ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-neutral-800' }}">
                                    @if($step['completed'])
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <div class="w-2 h-2 bg-gray-400 dark:bg-neutral-600 rounded-full"></div>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                    <div class="w-0.5 h-8 {{ $step['completed'] ? 'bg-green-200 dark:bg-green-800' : 'bg-gray-200 dark:bg-neutral-700' }} mt-1"></div>
                                @endif
                            </div>

                            {{-- Progress Content --}}
                            <div class="flex-1 pb-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium {{ $step['current'] ? 'text-blue-600 dark:text-blue-400' : ($step['completed'] ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-neutral-400') }}">
                                        {{ $step['label'] }}
                                    </h4>
                                    @if($step['current'])
                                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full">
                                            {{ __('dashboard.current') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Mock timestamps for demonstration --}}
                                @if($step['completed'])
                                    <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                                        @if($key === \App\Enums\ParcelStatus::CREATED->value)
                                            {{ now()->subDays(7)->format('M d, Y H:i') }}
                                        @elseif($key === \App\Enums\ParcelStatus::ARRIVED_CHINA->value)
                                            {{ now()->subDays(5)->format('M d, Y H:i') }}
                                        @elseif($key === \App\Enums\ParcelStatus::ARRIVED_UZB->value)
                                            {{ now()->subDays(2)->format('M d, Y H:i') }}
                                        @elseif($key === \App\Enums\ParcelStatus::DELIVERED->value)
                                            {{ now()->format('M d, Y H:i') }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
