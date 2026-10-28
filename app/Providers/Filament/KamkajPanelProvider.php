<?php

namespace App\Providers\Filament;

use Agroezinger\FilamentShieldEnhanced\FilamentShieldEnhancedPlugin;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Helpers\FaviconHelper;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\FilamentAuthenticate;
use App\Http\Middleware\RequirePasswordChange;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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
use Illuminate\Support\Facades\Blade;
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
            ->login(Login::class)
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile(EditProfile::class)
            ->favicon(fn () => FaviconHelper::getFaviconUrl())
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('filament.pwa-scripts')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('<script>
                    (function() {
                        var h = window.location.hostname;
                        var isLocal = '.(FaviconHelper::isLocal() ? 'true' : 'false').' || h === "localhost" || h === "127.0.0.1" || h === "waldo" || h.endsWith(".test") || h.endsWith(".local") || h.startsWith("192.168.") || h.startsWith("10.");
                        var target = isLocal ? "'.asset('img/logo-http.ico').'" : "'.asset('img/logo.ico').'";
                        var links = document.querySelectorAll("link[rel*=\'icon\']");
                        links.forEach(function(l) { if (l.href !== target) l.href = target; });
                    })();
                </script>')
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.components.sidebar-version'),
            )
            ->globalSearch(false)
            ->databaseNotifications()
            ->broadcasting()
            ->font('Plus Jakarta Sans', preload: [])
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.25rem')
            ->colors([
                'primary' => Color::Orange,
                'gray' => Color::Slate,
                'danger' => Color::Red,
                'warning' => Color::Amber,
                'info' => Color::Sky,
                'success' => Color::Emerald,
            ])
            ->navigationItems([
                NavigationItem::make('Letters & Templates')
                    ->url('/letters')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->activeIcon('heroicon-s-document-text')
                    ->sort(9)
                    ->group('HR & Admin')
                    ->visible(fn () => auth()->user()?->can('access_letters_and_templates') || (auth()->user()?->hasRole(['super_admin', 'HR', 'HR Assist']) ?? false)),
                NavigationItem::make('Employee SSF IDs')
                    ->url('/employee-ssids')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->activeIcon('heroicon-s-document-text')
                    ->sort(10)
                    ->group('HR & Admin')
                    ->visible(fn () => auth()->user()?->can('access_employee_ssf_ids') || (auth()->user()?->hasRole(['super_admin', 'HR']) ?? false)),
                NavigationItem::make('Salary Calculator')
                    ->url('/salary-calculator')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-calculator')
                    ->activeIcon('heroicon-s-calculator')
                    ->sort(1)
                    ->group('Finance')
                    ->visible(fn () => auth()->user()?->can('access_salary_calculator') || (auth()->user()?->hasRole(['super_admin', 'Finance']) ?? false)),
            ])
            ->navigationGroups([
                'HR & Admin',
                'Roster Management',
                'IT',
                'Finance',
                'Tips',
                'Purchase & Store',
                'Filament Shield',
                'System Settings',
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
                EnsureUserIsActive::class,
                FilamentAuthenticate::class,
                RequirePasswordChange::class,
            ])
            ->unsavedChangesAlerts()
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
                FilamentShieldEnhancedPlugin::make(),
                FilamentUnsavedChangesModalPlugin::make(),
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
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.pwa-install-button')
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => auth()->check() ? view('filament.push-notification-button') : ''
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => auth()->check() ? new HtmlString(Blade::render('@livewire(\App\Livewire\WebsocketStatusIndicator::class)')) : ''
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => auth()->check() ? view('filament.chat-topbar-button') : ''
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => auth()->check() ? new HtmlString(Blade::render('@livewire(\App\Livewire\FloatingChatWidget::class)')) : ''
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.wizard-keyboard-shortcuts')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.mobile-sidebar-gestures')
            );
    }
}
