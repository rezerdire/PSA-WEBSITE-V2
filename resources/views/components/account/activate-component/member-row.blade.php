@props(['member'])

<button type="button" wire:click="selectMember('{{ $member->member_id_no }}')"
    wire:key="member-{{ $member->member_id_no }}"
    class="group flex w-full items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/30 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-600/10">

    <x-account.activate-component.avatar :first="$member->mem_first_name" :last="$member->mem_last_name"
        class="transition group-hover:bg-blue-100" />

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-bold text-slate-800">
            {{ $member->mem_last_name }}, {{ $member->mem_first_name }} {{ $member->mem_middle_name }}
        </p>

        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
            <span class="text-xs text-slate-400">
                PSA ID: <span class="font-medium text-slate-500">{{ $member->member_id_no }}</span>
            </span>
        </div>
    </div>

    <div
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition group-hover:bg-blue-100 group-hover:text-blue-700">
        <x-account.activate-component.icon name="arrow-right"
            class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" />
    </div>
</button>
