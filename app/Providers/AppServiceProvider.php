<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use App\Models\Alert;
use App\Events\IntranetDataChanged;
use App\Listeners\ProcessIntranetDataChange;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        IntranetDataChanged::class => [
            ProcessIntranetDataChange::class,
        ],
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
        // Enregistrer les événements
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        Paginator::defaultView('vendor.pagination.cyberguard');
        Paginator::defaultSimpleView('vendor.pagination.cyberguard-simple');

        // Partager le nombre d'alertes non lues avec toutes les vues
        View::composer('layouts.app', function ($view) {
            $view->with('globalUnreadAlerts', Alert::getCachedUnreadCount());
        });
    }
}
