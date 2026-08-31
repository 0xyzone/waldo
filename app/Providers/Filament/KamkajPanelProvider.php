<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RequirePasswordChange;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KamkajPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('kamkaj')
            ->path('kamkaj')
            ->viteTheme('resources/css/filament/kamkaj/theme.css')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile()
            ->favicon(asset('img/logo.ico'))
            ->globalSearch(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationItems([
                NavigationItem::make('Letters & Templates')
                    ->url('/letters')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->activeIcon('heroicon-s-document-text')
                    ->sort(9)
                    ->group('HR & Admin')
                    ->visible(fn () => auth()->user()->hasRole(['super_admin','HR'])),
                NavigationItem::make('Employee SSF IDs')
                    ->url('/employee-ssids')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->activeIcon('heroicon-s-document-text')
                    ->sort(10)
                    ->group('HR & Admin')
                    ->visible(fn () => auth()->user()->hasRole(['super_admin','HR'])),
            ])
            ->navigationGroups([
                'HR & Admin',
                'IT',
                'Finance',
                'Basic Info',
                'Filament Shield',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RequirePasswordChange::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
                    <script>
                        (function () {
                            function fallbackCopy(text) {
                                return new Promise(function (resolve, reject) {
                                    try {
                                        var textarea = document.createElement("textarea");
                                        textarea.value = text;
                                        textarea.style.position = "fixed";
                                        textarea.style.top = "0";
                                        textarea.style.left = "0";
                                        textarea.style.width = "2em";
                                        textarea.style.height = "2em";
                                        textarea.style.padding = "0";
                                        textarea.style.border = "none";
                                        textarea.style.outline = "none";
                                        textarea.style.boxShadow = "none";
                                        textarea.style.background = "transparent";
                                        textarea.style.opacity = "0.01";

                                        var container = document.querySelector(".fi-modal, [role=\"dialog\"]") || document.body;
                                        container.appendChild(textarea);

                                        textarea.focus();
                                        textarea.select();
                                        textarea.setSelectionRange(0, 99999);

                                        var successful = false;
                                        try {
                                            successful = document.execCommand("copy");
                                        } catch (err) {
                                            successful = false;
                                        }

                                        container.removeChild(textarea);

                                        if (successful) {
                                            resolve();
                                        } else {
                                            reject(new Error("Copy command failed"));
                                        }
                                    } catch (err) {
                                        reject(err);
                                    }
                                });
                            }

                            var nativeWriteText = (navigator.clipboard && typeof navigator.clipboard.writeText === "function")
                                ? navigator.clipboard.writeText.bind(navigator.clipboard)
                                : null;

                            function safeWriteText(text) {
                                if (nativeWriteText) {
                                    return nativeWriteText(text).catch(function () {
                                        return fallbackCopy(text);
                                    });
                                }
                                return fallbackCopy(text);
                            }

                            if (!navigator.clipboard) {
                                try {
                                    Object.defineProperty(navigator, "clipboard", {
                                        value: { writeText: safeWriteText },
                                        writable: true,
                                        configurable: true
                                    });
                                } catch (e) {
                                    try {
                                        window.navigator.clipboard = { writeText: safeWriteText };
                                    } catch (err) {}
                                }
                            } else if (!navigator.clipboard.writeText) {
                                try {
                                    navigator.clipboard.writeText = safeWriteText;
                                } catch (e) {
                                    try {
                                        Object.defineProperty(navigator.clipboard, "writeText", {
                                            value: safeWriteText,
                                            writable: true,
                                            configurable: true
                                        });
                                    } catch (err) {}
                                }
                            }
                        })();
                    </script>
                '),
            );
    }
}
