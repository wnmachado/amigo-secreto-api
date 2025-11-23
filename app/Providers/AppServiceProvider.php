<?php

namespace App\Providers;

use App\Mail\Transports\ZeptoMailTransport;
use App\Modules\Events\Models\Event;
use App\Policies\EventPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => EventPolicy::class,
    ];

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
        // Register policies
        Gate::policy(Event::class, EventPolicy::class);

        Mail::extend('zeptomail', function () {
            return new ZeptoMailTransport(env('MAIL_PASSWORD'));
        });
    }
}
