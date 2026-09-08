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
