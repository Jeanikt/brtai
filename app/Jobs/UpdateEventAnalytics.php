<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventAnalytic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEventAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        $today = now()->format('Y-m-d');

        $analytic = EventAnalytic::firstOrCreate(
            [
                'event_id' => $this->event->id,
                'date' => $today
            ],
            [
                'page_views' => 0,
                'unique_visitors' => 0,
                'tickets_sold' => 0,
                'total_revenue' => 0,
                'conversion_rate' => 0
            ]
        );

        // Update with current data
        $analytic->update([
            'tickets_sold' => $this->event->confirmed_count,
            'total_revenue' => $this->event->total_revenue,
        ]);

        // Calculate conversion rate
        $analytic->updateConversionRate();
    }
}
