<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
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

        // Partager le nombre d'alertes non lues avec toutes les vues
        View::composer('layouts.app', function ($view) {
            $unreadAlerts = Alert::where('acknowledged', false)->count();
            $view->with('globalUnreadAlerts', $unreadAlerts);
        });
    }
}
