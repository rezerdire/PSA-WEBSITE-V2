@props(['errors'])

<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5" id="error-summary">
    <div class="flex gap-3">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">!</div>
        <div>
            <p class="text-sm font-bold text-red-800">Please review the following fields</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li class="text-xs leading-5 text-red-700">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>