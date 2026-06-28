<x-filament-panels::page>
    @if ($this->hasActiveSubscription())
        <x-filament::section>
            <x-slot name="heading">Current subscription</x-slot>
            <p>Your team has an active subscription.</p>
            <x-filament::button wire:click="manageBilling" color="gray">
                Manage billing
            </x-filament::button>
        </x-filament::section>

        @php($invoices = $this->invoices())
        @if (count($invoices))
            <x-filament::section>
                <x-slot name="heading">Invoices</x-slot>
                <ul class="divide-y">
                    @foreach ($invoices as $invoice)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $invoice->date()->toFormattedDateString() }} — {{ $invoice->total() }}</span>
                            <x-filament::link :href="$invoice->hostedInvoiceUrl()" target="_blank">View</x-filament::link>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>
        @endif
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
