<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\Registrations\Widgets\RegistrationStatsOverview;

// New Feature: SPINNER
//  THIS IS THE EXPORT BELOW?
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->favicon(asset('Images/PSA_LOGO.png'))
            ->brandName('PSA Admin')
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                RegistrationStatsOverview::class,
            ])

            // New Feature: SPINNER
            // upd: modal now closes based on an actual fetch() + blob download completing,
            // triggered by a Livewire 'download-pdf' event dispatched from the action,
            // instead of watching Livewire's request/succeed hook (which only reflected
            // the redirect instruction, not the PDF actually finishing).
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render(<<<'BLADE'
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

                    <x-filament::modal
                        id="pdf-error-modal"
                        width="xs"
                        alignment="center"
                        icon="heroicon-o-x-circle"
                        icon-color="danger"
                    >
                        <x-slot name="heading">
                            PDF generation failed
                        </x-slot>
                    </x-filament::modal>

                    <script>
                        async function psaDownloadPdf(url) {
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'pdf-loading-modal' } }));

                            try {
                                const response = await fetch(url, {
                                    headers: { 'Accept': 'application/pdf' },
                                });

                                if (!response.ok) {
                                    throw new Error('Export failed with status ' + response.status);
                                }

                                const blob = await response.blob();

                                // pull a filename from Content-Disposition if the server sent one,
                                // otherwise fall back to a generic name
                                let filename = 'registrations.pdf';
                                const disposition = response.headers.get('Content-Disposition');
                                if (disposition) {
                                    const match = disposition.match(/filename="?([^"]+)"?/);
                                    if (match && match[1]) {
                                        filename = match[1];
                                    }
                                }

                                const link = document.createElement('a');
                                link.href = URL.createObjectURL(blob);
                                link.download = filename;
                                document.body.appendChild(link);
                                link.click();
                                link.remove();
                                URL.revokeObjectURL(link.href);

                                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-loading-modal' } }));
                                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'pdf-success-modal' } }));
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-success-modal' } }));
                                }, 2000);
                            } catch (e) {
                                console.error('PDF export failed:', e);
                                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-loading-modal' } }));
                                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'pdf-error-modal' } }));
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'pdf-error-modal' } }));
                                }, 3000);
                            }
                        }

                        document.addEventListener('livewire:init', () => {
                            Livewire.on('download-pdf', (event) => {
                                // Filament/Livewire may pass the payload either as
                                // {url: '...'} or as the first item of an array
                                const payload = Array.isArray(event) ? event[0] : event;
                                const url = payload?.url ?? payload;
                                if (url) {
                                    psaDownloadPdf(url);
                                }
                            });
                        });
                    </script>
                BLADE),
            )

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}