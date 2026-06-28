<x-filament-panels::page>
    @php($subscribed = $this->currentTeam()->subscribed('default'))

    @if ($subscribed)
        <x-filament::section>
            <x-slot name="heading">Current subscription</x-slot>
            <p>Your team has an active subscription.</p>
            <x-filament::button tag="a" :href="route('cashier.billing-portal') ?? '#'" color="gray">
                Manage billing
            </x-filament::button>
        </x-filament::section>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($this->plans() as $plan)
            <x-filament::section>
                <x-slot name="heading">{{ $plan->name }}</x-slot>

                <ul class="list-disc ps-5 mb-4">
                    @foreach ($plan->features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>

                @if ($plan->priceId)
                    <x-filament::button wire:click="subscribe('{{ $plan->key }}')">
                        Subscribe
                    </x-filament::button>
                @else
                    <x-filament::button disabled color="gray">Unavailable</x-filament::button>
                @endif
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
