<x-filament-panels::page>
    <div wire:poll.2s="refreshBatch" class="space-y-6">
        @if (! $batch)
            <x-filament::section>
                <x-slot name="heading">No bulk QR batch selected</x-slot>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Start a bulk QR send from the registrations list. Approved member registrations without a previous QR send are selected oldest first.
                </p>
            </x-filament::section>
        @else
            @php
                $statusLabels = [
                    'queued' => 'Queued',
                    'running' => 'Sending',
                    'completed' => 'Completed',
                    'completed_with_failures' => 'Completed with failures',
                ];
                $statusColors = [
                    'queued' => 'warning',
                    'running' => 'info',
                    'completed' => 'success',
                    'completed_with_failures' => 'danger',
                ];
            @endphp

            <x-filament::section>
                <x-slot name="heading">Bulk QR sending #{{ $batch->id }}</x-slot>
                <x-slot name="description">
                    Approved member registrations are processed oldest first. Non-member registrations are excluded. The queue worker must be running while the status is queued or sending.
                </x-slot>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
                    <div>
                        <div class="text-xs text-gray-500">Status</div>
                        <x-filament::badge :color="$statusColors[$batch->status] ?? 'gray'">
                            {{ $statusLabels[$batch->status] ?? str($batch->status)->headline() }}
                        </x-filament::badge>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Requested limit</div>
                        <div class="font-semibold">{{ $batch->requested_limit }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Processed</div>
                        <div class="font-semibold">{{ $batch->processed_count }} / {{ $batch->total_count }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Sent</div>
                        <div class="font-semibold text-success-600">{{ $batch->sent_count }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Failed</div>
                        <div class="font-semibold text-danger-600">{{ $batch->failed_count }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Skipped</div>
                        <div class="font-semibold">{{ $batch->skipped_count }}</div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="mb-1 flex justify-between text-xs text-gray-500">
                        <span>Progress</span>
                        <span>{{ $this->progressPercent() }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-primary-600 transition-all" style="width: {{ $this->progressPercent() }}%"></div>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Current / last recipient</div>
                    <div class="mt-1 font-semibold">{{ $batch->current_name ?: 'Waiting for queue worker…' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $batch->current_email }}</div>
                    @if ($batch->current_status)
                        <div class="mt-1 text-xs uppercase text-gray-500">{{ str($batch->current_status)->replace('_', ' ')->headline() }}</div>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <x-filament::button
                        tag="a"
                        color="gray"
                        :href="route('admin.registrations.qr-batches.export', ['batch' => $batch->id])"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Export CSV for Excel
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        color="gray"
                        :href="\App\Filament\Resources\Registrations\RegistrationResource::getUrl('index')"
                    >
                        Back to registrations
                    </x-filament::button>
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    Immediate SMTP rejections and invalid addresses are recorded as failed. A delayed bounce that the mail provider accepts first requires a provider webhook to be detected automatically.
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Recipients</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-700">
                            <tr>
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Name</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($batch->recipients as $recipient)
                                <tr class="{{ $recipient->status === 'not_selected_limit' ? 'bg-warning-50 dark:bg-warning-950/20' : '' }}">
                                    <td class="px-3 py-2">{{ $recipient->position }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $recipient->name }}</td>
                                    <td class="px-3 py-2">{{ $recipient->email ?: 'No email on file' }}</td>
                                    <td class="px-3 py-2">{{ str($recipient->status)->replace('_', ' ')->headline() }}</td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $recipient->error_message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($batch->not_selected_count > 0)
                    <p class="mt-4 text-sm text-warning-700 dark:text-warning-300">
                        {{ $batch->not_selected_count }} approved member registration(s) remain outside this limit. The first excluded registrant is highlighted above and included in the CSV.
                    </p>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
