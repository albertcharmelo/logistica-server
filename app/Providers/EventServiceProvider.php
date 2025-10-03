<?php

namespace App\Providers;

use App\Events\ReclamoFilesAttached;
use App\Listeners\NotifyReclamoFilesAttached;
use App\Events\ReclamoCommentCreated;
use App\Listeners\NotifyReclamoCommentCreated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ReclamoFilesAttached::class => [
            NotifyReclamoFilesAttached::class,
        ],
        ReclamoCommentCreated::class => [
            NotifyReclamoCommentCreated::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
