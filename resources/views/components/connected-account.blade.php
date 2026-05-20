@props(['provider', 'createdAt' => null])

<div>
    <div class="pl-3 flex items-center justify-between">
        <div class="flex items-center">
            <div class="h-6 w-6 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-600">{{ strtoupper(substr($provider['id'], 0, 1)) }}</div>

            <div class="ml-2">
                <div class="text-sm font-semibold text-gray-600">
                    {{ __($provider['name']) }}
                </div>

                @if (! empty($createdAt))
                    <div class="text-xs text-gray-500">
                        {{ __('Connected :createdAt', ['createdAt' => $createdAt]) }}
                    </div>
                @else
                    <div class="text-xs text-gray-500">
                        {{ __('Not connected.') }}
                    </div>
                @endif
            </div>
        </div>

        <div>
            {{ $action }}
        </div>
    </div>

    @error($provider['id'].'_connect_error')
    <div class="text-sm font-semibold text-red-500 px-3 mt-2">
        {{ $message }}
    </div>
    @enderror
</div>
