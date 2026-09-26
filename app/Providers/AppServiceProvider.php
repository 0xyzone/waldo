<?php

namespace App\Providers;

use App\Listeners\RecordScheduleRunListener;
use App\Models\BiometricAllotment;
use App\Models\Employee;
use App\Observers\BiometricAllotmentObserver;
use App\Observers\EmployeeObserver;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        Model::unguard();
        Employee::observe(EmployeeObserver::class);
        BiometricAllotment::observe(BiometricAllotmentObserver::class);
        Event::subscribe(RecordScheduleRunListener::class);

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
