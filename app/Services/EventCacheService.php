<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventCacheService
{
    const CACHE_DURATION = 3600;
    const SHORT_CACHE_DURATION = 300;

    public function getUserEvents($userId)
    {
        return Cache::remember(
            "user:{$userId}:events",
            self::CACHE_DURATION,
            function () use ($userId) {
                return Event::with(['confirmedParticipants', 'priceTiers'])
                    ->where('organizer_id', $userId)
                    ->orderByDesc('created_at')
                    ->get();
            }
        );
    }

    public function getUserActiveEventsCount($userId)
    {
        return Cache::remember(
            "user:{$userId}:active_events_count",
            self::CACHE_DURATION,
            function () use ($userId) {
                return Event::where('organizer_id', $userId)
                    ->whereIn('status', ['draft', 'active'])
                    ->count();
            }
        );
    }

    public function getEventDetails($eventId)
    {
        return Cache::remember(
            "event:{$eventId}:details",
            self::CACHE_DURATION,
            function () use ($eventId) {
                $event = Event::with(['priceTiers', 'participants', 'confirmedParticipants'])
                    ->findOrFail($eventId);

                return [
                    'event' => $event,
                    'participants' => $event->participants()->orderBy('created_at', 'desc')->get(),
                    'stats' => [
                        'total_revenue' => $event->total_revenue,
                        'confirmed_count' => $event->confirmed_count,
                        'conversion_rate' => $event->conversion_rate,
                    ]
                ];
            }
        );
    }

    public function getEventForEdit($eventId)
    {
        return Cache::remember(
            "event:{$eventId}:edit",
            self::CACHE_DURATION,
            function () use ($eventId) {
                return Event::with('priceTiers')->findOrFail($eventId);
            }
        );
    }

    public function getEventAnalytics($eventId)
    {
        return Cache::remember(
            "event:{$eventId}:analytics",
            self::SHORT_CACHE_DURATION,
            function () use ($eventId) {
                $event = Event::with(['analytics', 'participants'])->findOrFail($eventId);

                return [
                    'event' => $event,
                    'analytics' => $event->analytics,
                    'participants' => $event->participants,
                ];
            }
        );
    }

    public function invalidateUserCaches($userId)
    {
        $keys = [
            "user:{$userId}:events",
            "user:{$userId}:active_events_count",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function invalidateEventCaches($eventId)
    {
        $keys = [
            "event:{$eventId}:details",
            "event:{$eventId}:edit",
            "event:{$eventId}:analytics",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
