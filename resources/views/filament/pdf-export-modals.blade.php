<x-filament::modal
    id="pdf-loading-modal"
    width="xs"
    alignment="center"
    :close-button="false"
    :close-by-clicking-away="false"
    :close-by-escaping="false"
>
    <div class="flex flex-col items-center gap-4 py-2">
        <x-filament::loading-indicator class="h-10 w-10 text-primary-600" />
        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Generating PDF, please wait...
        </p>
    </div>
</x-filament::modal>

<x-filament::modal
    id="pdf-success-modal"
    width="xs"
    alignment="center"
    icon="heroicon-o-check-circle"
    icon-color="success"
>
    <x-slot name="heading">
        PDF generated successfully
    </x-slot>
</x-filament::modal>

<script>
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (btn && /generate pdf/i.test(btn.textContent)) {
            window.pdfRequestPending = true;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'pdf-loading-modal' } }));
        }
    });

    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ succeed, fail }) => {
            succeed(() => {
                if (!window.pdfRequestPending) return;
                window.pdfRequestPending = false;
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-loading-modal' } }));
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'pdf-success-modal' } }));
                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-success-modal' } }));
                    }, 2000);
                }, 1000);
            });
            fail(() => {
                if (!window.pdfRequestPending) return;
                window.pdfRequestPending = false;
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-loading-modal' } }));
            });
        });
    });
</script>