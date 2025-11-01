<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;
use App\Listeners\SendLogToDiscord;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MessageLogged::class => [
            SendLogToDiscord::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
