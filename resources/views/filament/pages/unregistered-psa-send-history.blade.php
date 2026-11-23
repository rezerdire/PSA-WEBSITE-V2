<x-filament-panels::page>
    @if ($batchId && $batch)
        <div wire:poll.2s="refreshBatch" class="space-y-6">
            @php
                $statusColors = [
                    'queued' => 'warning',
                    'running' => 'info',
                    'completed' => 'success',
                    'completed_with_failures' => 'danger',
                    'queue_failed' => 'danger',
                ];
            @endphp
            <x-filament::section>
                <x-slot name="heading">Email batch #{{ $batch->id }}</x-slot>
                <x-slot name="description">Progress refreshes every two seconds while this page is open.</x-slot>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    <div><div class="text-xs text-gray-500">Status</div><x-filament::badge :color="$statusColors[$batch->status] ?? 'gray'">{{ str($batch->status)->replace('_', ' ')->headline() }}</x-filament::badge></div>
                    <div><div class="text-xs text-gray-500">Processed</div><div class="font-semibold">{{ $batch->processed_count }} / {{ $batch->total_count }}</div></div>
                    <div><div class="text-xs text-gray-500">Successfully sent</div><div class="font-semibold text-success-600">{{ $batch->sent_count }}</div></div>
                    <div><div class="text-xs text-gray-500">Failed</div><div class="font-semibold text-danger-600">{{ $batch->failed_count }}</div></div>
                    <div><div class="text-xs text-gray-500">Requested limit</div><div class="font-semibold">{{ $batch->requested_limit }}</div></div>
                </div>
                <div class="mt-5">
                    <div class="mb-1 flex justify-between text-xs text-gray-500"><span>Progress</span><span>{{ $this->progressPercent($batch) }}%</span></div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-full rounded-full bg-primary-600 transition-all" style="width: {{ $this->progressPercent($batch) }}%"></div></div>
                </div>
                <div class="mt-5 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Current / last recipient</div>
                    <div class="mt-1 font-semibold">{{ $batch->current_name ?: 'Waiting for queue worker…' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $batch->current_email }}</div>
                    @if ($batch->current_status)<div class="mt-1 text-xs uppercase text-gray-500">{{ str($batch->current_status)->replace('_', ' ')->headline() }}</div>@endif
                </div>
                @if ($batch->error_message)<p class="mt-4 text-sm text-danger-600">{{ $batch->error_message }}</p>@endif
                <div class="mt-4"><x-filament::button tag="a" color="gray" :href="\App\Filament\Pages\UnregisteredPsaSendHistory::getUrl()">Back to history</x-filament::button></div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Recipients</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-700"><tr><th class="px-3 py-2">#</th><th class="px-3 py-2">PSA ID</th><th class="px-3 py-2">Name</th><th class="px-3 py-2">Email</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Error</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($batch->recipients as $item)
                                <tr><td class="px-3 py-2">{{ $item->position }}</td><td class="px-3 py-2 font-mono">{{ $item->psa_id }}</td><td class="px-3 py-2 font-medium">{{ $item->name }}</td><td class="px-3 py-2">{{ $item->email ?: 'No email on file' }}</td><td class="px-3 py-2">{{ str($item->status)->replace('_', ' ')->headline() }}</td><td class="px-3 py-2 text-danger-600">{{ $item->error_message }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @else
        <div wire:poll.2s="$refresh">
            <x-filament::section>
                <x-slot name="heading">Bulk email batches</x-slot>
                <x-slot name="description">Each batch shows live delivery progress. Open a batch to see the outcome for every recipient.</x-slot>
                @if ($batches->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No bulk email batches yet. Start a bulk send from Unregistered PSA IDs.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-700"><tr><th class="px-3 py-2">Batch</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Progress</th><th class="px-3 py-2">Sent</th><th class="px-3 py-2">Failed</th><th class="px-3 py-2">Current / last recipient</th><th class="px-3 py-2">Started</th><th class="px-3 py-2"></th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($batches as $row)
                                    @php $percent = $this->progressPercent($row); @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-medium">#{{ $row->id }}</td>
                                        <td class="px-3 py-2">{{ str($row->status)->replace('_', ' ')->headline() }}</td>
                                        <td class="px-3 py-2 min-w-36"><div>{{ $row->processed_count }} / {{ $row->total_count }} ({{ $percent }}%)</div><div class="mt-1 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-full rounded-full bg-primary-600" style="width: {{ $percent }}%"></div></div></td>
                                        <td class="px-3 py-2 text-success-600">{{ $row->sent_count }}</td>
                                        <td class="px-3 py-2 text-danger-600">{{ $row->failed_count }}</td>
                                        <td class="px-3 py-2">{{ $row->current_name ?: 'Waiting for worker' }}<div class="text-xs text-gray-500">{{ $row->current_email }}</div></td>
                                        <td class="px-3 py-2">{{ $row->started_at?->format('Y-m-d H:i:s') ?? $row->created_at?->format('Y-m-d H:i:s') }}</td>
                                        <td class="px-3 py-2"><x-filament::button tag="a" size="sm" color="gray" :href="\App\Filament\Pages\UnregisteredPsaSendHistory::getUrl(['batch' => $row->id])">Details</x-filament::button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
