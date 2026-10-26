<?php

namespace App\Providers;

use App\Events\MessageSent;
use App\Http\Requests\Auth\CustomEmailVerificationRequest;
use App\Listeners\SendWebPushOnChatMessage;
use App\Listeners\SendWebPushOnDatabaseNotification;
use App\Models\BiometricAllotment;
use App\Models\Employee;
use App\Observers\BiometricAllotmentObserver;
use App\Observers\EmployeeObserver;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EmailVerificationRequest::class,
            CustomEmailVerificationRequest::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        if (! getenv('OPENSSL_CONF')) {
            $defaultConf = 'D:\\wamp64\\bin\\php\\php8.4.15\\extras\\ssl\\openssl.cnf';
            if (file_exists($defaultConf)) {
                putenv('OPENSSL_CONF='.$defaultConf);
                $_ENV['OPENSSL_CONF'] = $defaultConf;
            }
        }

        if (str_starts_with(config('app.url'), 'https://') && (app()->isProduction() || request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https')) {
            URL::forceScheme('https');
        }

        Model::unguard();
        Employee::observe(EmployeeObserver::class);
        BiometricAllotment::observe(BiometricAllotmentObserver::class);

        Event::listen(NotificationSent::class, SendWebPushOnDatabaseNotification::class);
        Event::listen(MessageSent::class, SendWebPushOnChatMessage::class);

        // Transform all tables into clean, responsive mobile cards
        Table::configureUsing(function (Table $table): void {
            $table->stackedOnMobile();
        });

        // Globally configure CreateAction and EditAction to slideover with 7xl modal width
        CreateAction::configureUsing(function (CreateAction $action): void {
            $action->modal(true)
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge);
        });

        EditAction::configureUsing(function (EditAction $action): void {
            $action->modal(true)
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge);
        });

        // Inject custom CSS to style employees table rows based on status
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): HtmlString => new HtmlString('
                <style>
                    /* Inactive - muted gray */
                    .bg-gray-row { background-color: rgba(229, 231, 235, 0.4) !important; color: rgb(107, 114, 128) !important; opacity: 0.8; }
                    .bg-gray-row td { background-color: rgba(229, 231, 235, 0.4) !important; }
                    .dark .bg-gray-row { background-color: rgba(75, 85, 99, 0.2) !important; color: rgb(156, 163, 175) !important; }
                    .dark .bg-gray-row td { background-color: rgba(75, 85, 99, 0.2) !important; }

                    /* Resigned - light red */
                    .bg-rose-row { background-color: rgba(254, 226, 226, 0.6) !important; color: rgb(185, 28, 28) !important; }
                    .bg-rose-row td { background-color: rgba(254, 226, 226, 0.6) !important; }
                    .dark .bg-rose-row { background-color: rgba(153, 27, 27, 0.2) !important; color: rgb(252, 165, 165) !important; }
                    .dark .bg-rose-row td { background-color: rgba(153, 27, 27, 0.2) !important; }

                    /* Resigning this month - purple/violet */
                    .bg-violet-row { background-color: rgba(237, 233, 254, 0.6) !important; color: rgb(109, 40, 217) !important; }
                    .bg-violet-row td { background-color: rgba(237, 233, 254, 0.6) !important; }
                    .dark .bg-violet-row { background-color: rgba(109, 40, 217, 0.25) !important; color: rgb(196, 181, 253) !important; }
                    .dark .bg-violet-row td { background-color: rgba(109, 40, 217, 0.25) !important; }

                    /* Terminated - crimson red */
                    .bg-red-row { background-color: rgba(254, 202, 202, 0.4) !important; color: rgb(153, 27, 27) !important; font-weight: 600; }
                    .bg-red-row td { background-color: rgba(254, 202, 202, 0.4) !important; }
                    .dark .bg-red-row { background-color: rgba(255, 0, 0, 0.6) !important; color: rgb(254, 202, 202) !important; }
                    .dark .bg-red-row td { background-color: rgba(255, 0, 0, 0.6) !important; }

                    /* Remove number input spinners / arrows globally */
                    input[type="number"]::-webkit-outer-spin-button,
                    input[type="number"]::-webkit-inner-spin-button {
                        -webkit-appearance: none !important;
                        margin: 0 !important;
                    }
                    input[type="number"] {
                        -moz-appearance: textfield !important;
                        appearance: textfield !important;
                    }
                </style>
                <script>
                    // Prevent mouse wheel from accidentally altering number inputs
                    document.addEventListener("wheel", function (event) {
                        if (document.activeElement && document.activeElement.tagName === "INPUT" && document.activeElement.type === "number") {
                            document.activeElement.blur();
                        }
                    }, { passive: true });

                    // Prevent ArrowUp and ArrowDown keys from changing number values
                    document.addEventListener("keydown", function (event) {
                        if (event.target && event.target.tagName === "INPUT" && event.target.type === "number") {
                            if (event.key === "ArrowUp" || event.key === "ArrowDown") {
                                event.preventDefault();
                            }
                        }
                    });

                    // Tips Report: Cycle through chips_amount and cash_amount inputs on Tab or Enter
                    document.addEventListener("keydown", function (event) {
                        if (event.key !== "Tab" && event.key !== "Enter") {
                            return;
                        }

                        // Allow default Enter if modifier keys are pressed
                        if (event.key === "Enter" && (event.shiftKey || event.ctrlKey || event.metaKey || event.altKey)) {
                            return;
                        }

                        var target = event.target;
                        if (!target || target.tagName !== "INPUT") {
                            return;
                        }

                        var isChips = target.classList.contains("collection-chips-input");
                        var isCash = target.classList.contains("collection-cash-input");

                        if (!isChips && !isCash) {
                            return;
                        }

                        var chipsInputs = Array.from(document.querySelectorAll(".collection-chips-input"))
                            .filter(function (el) { return el.offsetParent !== null && !el.disabled && !el.readOnly; });
                        var cashInputs = Array.from(document.querySelectorAll(".collection-cash-input"))
                            .filter(function (el) { return el.offsetParent !== null && !el.disabled && !el.readOnly; });

                        if (chipsInputs.length === 0 || cashInputs.length === 0) {
                            return;
                        }

                        // Build ordered sequence alternating [chips_0, cash_0, chips_1, cash_1, ...]
                        var sequence = [];
                        var maxLen = Math.max(chipsInputs.length, cashInputs.length);
                        for (var i = 0; i < maxLen; i++) {
                            if (chipsInputs[i]) {
                                sequence.push(chipsInputs[i]);
                            }
                            if (cashInputs[i]) {
                                sequence.push(cashInputs[i]);
                            }
                        }

                        var currentIndex = sequence.indexOf(target);
                        if (currentIndex === -1) {
                            return;
                        }

                        event.preventDefault();

                        var targetIndex;
                        if (event.shiftKey) {
                            // Move backward
                            targetIndex = (currentIndex - 1 + sequence.length) % sequence.length;
                        } else {
                            // Move forward
                            targetIndex = (currentIndex + 1) % sequence.length;
                        }

                        var nextEl = sequence[targetIndex];
                        if (nextEl) {
                            nextEl.focus();
                            if (typeof nextEl.select === "function") {
                                nextEl.select();
                            }
                        }
                    });

                    // Auto-select numeric text on focus for collection inputs
                    document.addEventListener("focus", function (event) {
                        if (event.target && event.target.tagName === "INPUT") {
                            if (event.target.classList.contains("collection-chips-input") || event.target.classList.contains("collection-cash-input")) {
                                setTimeout(function () {
                                    if (document.activeElement === event.target && typeof event.target.select === "function") {
                                        event.target.select();
                                    }
                                }, 30);
                            }
                        }
                    }, true);
                </script>
            ')
        );

        // Force Filament's VerifyEmail notification to generate the correct panel URL
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return Filament::getVerifyEmailUrl($notifiable);
        });

        // Use a custom, branded verification email template
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('🔐 One tiny step left – Verify your '.config('app.name').' email')
                ->view('emails.verify-email', ['url' => $url, 'user' => $notifiable]);
        });
    }
}
