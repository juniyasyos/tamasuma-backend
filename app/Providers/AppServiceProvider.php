<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        parent::register();
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));

        // Globally hide breadcrumbs if disabled via config, without touching each page.
        if (! config('ui.breadcrumbs.enabled', false)) {
            FilamentView::registerRenderHook('panels::body.start', function (): string {
                return '<style>.fi-breadcrumbs{display:none!important}</style>';
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Gate::define('viewApiDocs', function (User $user) {
            return true;
        });
        // Gate::policy()
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });

        // Model observers
        Enrollment::observe(EnrollmentObserver::class);
    }
}
