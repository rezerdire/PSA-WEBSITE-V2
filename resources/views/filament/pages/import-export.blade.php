<x-filament-panels::page>

    <div class="grid grid-cols-1 gap-6">

        {{-- IMPORT CARD --}}
        <x-filament::section>
            <x-slot name="heading">Import</x-slot>
            <x-slot name="description">Import a single table (CSV or SQL).</x-slot>

            <form wire:submit="runImport">
                {{ $this->importForm }}

                <div class="mt-4">
                    <x-filament::button type="submit">
                        Import
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    </div>

    {{-- Modal: shown when an imported table doesn't exist yet and needs a name --}}
    @if ($showTableNameModal)
        <div
            x-data
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-semibold mb-2">Name your new table</h2>

                <p class="text-sm text-gray-500 mb-3">
                    No existing table was selected, so this will create a new one with these columns:
                </p>

                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach ($pendingImportHeaders as $header)
                        <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-xs">{{ $header }}</span>
                    @endforeach
                </div>

                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="newTableName"
                        placeholder="e.g. patients, suppliers, invoices"
                    />
                </x-filament::input.wrapper>

                <div class="flex justify-end gap-2 mt-4">
                    <x-filament::button color="gray" wire:click="cancelTableNameModal">
                        Cancel
                    </x-filament::button>
                    <x-filament::button wire:click="confirmNewTableName">
                        Create Table &amp; Import
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>