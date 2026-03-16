<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Alert;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Partager le nombre d'alertes non lues avec toutes les vues
        View::composer('layouts.app', function ($view) {
            $unreadAlerts = Alert::where('acknowledged', false)->count();
            $view->with('globalUnreadAlerts', $unreadAlerts);
        });
    }
}
