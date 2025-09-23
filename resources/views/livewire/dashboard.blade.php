{{-- Mobile Shipment Management Interface --}}
<div class="max-w-md mx-auto bg-white dark:bg-neutral-900 min-h-screen">
    @if(!$client)
        {{-- No Client Found --}}
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 mb-2">{{ __('dashboard.client_not_found') }}</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400">{{ __('dashboard.invalid_uid') }}</p>
            </div>
        </div>
    @else
        {{-- Header with Title and Theme Switcher --}}
        <div class="p-4 border-b border-gray-100 dark:border-neutral-800">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">{{ __('dashboard.title') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">{{ __('dashboard.subtitle') }}</p>
                </div>
                <x-ui.theme-switcher variant="dropdown" />
            </div>

            {{-- Search Bar --}}
            <x-ui.input
                type="text"
                placeholder="{{ __('dashboard.search_placeholder') }}"
                prefixIcon="magnifying-glass"
                class="w-full"
                wire:model.live="searchQuery"
            />
        </div>

        {{-- Payment Status Tabs --}}
        <div class="flex border-b border-gray-100 dark:border-neutral-800">
            <button
                wire:click="setTab('unpaid')"
                class="flex-1 py-3 px-4 text-sm font-medium text-center {{ $selectedTab === 'unpaid' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-neutral-300' }}">
                {{ __('dashboard.unpaid') }}
            </button>
            <button
                wire:click="setTab('paid')"
                class="flex-1 py-3 px-4 text-sm font-medium text-center {{ $selectedTab === 'paid' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-neutral-300' }}">
                {{ __('dashboard.paid') }}
            </button>
        </div>

        {{-- Status Filters --}}
        <div class="p-4 border-b border-gray-100 dark:border-neutral-800">
            <div class="flex gap-2 mb-3 overflow-x-auto">
                <x-ui.button
                    wire:click="setStatus('all')"
                    variant="{{ $selectedStatus === 'all' ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ __('dashboard.status.all') }}
                </x-ui.button>
                <x-ui.button
                    wire:click="setStatus('created')"
                    variant="{{ $selectedStatus === 'created' ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ __('dashboard.status.created') }}
                </x-ui.button>
                <x-ui.button
                    wire:click="setStatus('arrived_china')"
                    variant="{{ $selectedStatus === 'arrived_china' ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ __('dashboard.status.in_transit') }}
                </x-ui.button>
                <x-ui.button
                    wire:click="setStatus('arrived_uzb')"
                    variant="{{ $selectedStatus === 'arrived_uzb' ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ __('dashboard.status.in_tashkent') }}
                </x-ui.button>
                <x-ui.button
                    wire:click="setStatus('delivered')"
                    variant="{{ $selectedStatus === 'delivered' ? 'primary' : 'outline' }}"
                    size="sm">
                    {{ __('dashboard.status.delivered') }}
                </x-ui.button>
            </div>

            {{-- Select All Checkbox --}}
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    id="select-all"
                    class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-neutral-600 dark:bg-neutral-800 focus:ring-blue-500"
                    wire:model.live="selectAll"
                    wire:click="toggleSelectAll">
                <label for="select-all" class="text-sm text-gray-600 dark:text-neutral-400">{{ __('dashboard.select_all') }}</label>
            </div>
        </div>

        {{-- Shipment Cards --}}
        <div class="p-4 space-y-3 pb-24">
            @forelse($parcels as $parcel)
                <x-ui.card class="w-full p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-neutral-600 dark:bg-neutral-800 focus:ring-blue-500"
                                wire:model.live="selectedParcels"
                                value="{{ $parcel->id }}">
                            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $parcel->track_number }}</div>
                                <div class="text-sm text-gray-500 dark:text-neutral-400">
                                    2.5 {{ __('dashboard.kg') }} • 6 {{ __('dashboard.currency') }}
                                </div>
                                <span class="inline-block mt-1 px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full">
                                    {{ $parcel->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </x-ui.card>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-neutral-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-1">{{ __('dashboard.no_parcels') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">{{ __('dashboard.no_parcels_description') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Sticky Payment Bar --}}
        @if(count($selectedParcels) > 0)
            <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-md bg-white dark:bg-neutral-900 border-t border-gray-200 dark:border-neutral-800 p-4">
                <x-ui.button class="w-full" size="lg">
                    <div class="flex items-center justify-between w-full">
                        <span class="font-medium">{{ __('dashboard.pay') }}</span>
                        <span class="text-sm">{{ number_format($this->getTotalWeight(), 2) }} {{ __('dashboard.kg') }} • {{ number_format($this->getTotalCost()) }} {{ __('dashboard.currency') }}</span>
                    </div>
                </x-ui.button>
            </div>
        @endif
    @endif
</div>
