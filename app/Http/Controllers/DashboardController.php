<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile ?? $user->profile()->create([
            'full_name' => $user->name ?? 'Usuário sem nome',
            'plan_type' => 'freemium',
        ]);

        $events = Event::with(['confirmedParticipants', 'priceTiers'])
            ->where('organizer_id', $profile->id)
            ->orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'event_date' => $event->event_date,
                    'location' => $event->location,
                    'header_image_url' => $event->header_image_url,
                    'status' => $event->status,
                    'confirmed_count' => $event->confirmed_count,
                    'total_revenue' => $event->total_revenue,
                ];
            });

        $activeEventsCount = Event::where('organizer_id', $profile->id)
            ->where('status', 'active')
            ->count();

        $stats = [
            'total_events' => $events->count(),
            'total_revenue' => number_format($events->sum('total_revenue'), 2, ',', '.'),
            'total_participants' => $events->sum('confirmed_count'),
            'upcoming_events' => $events->where('event_date', '>=', now())->count(),
        ];

        return Inertia::render('Dashboard/Index', [
            'events' => $events,
            'stats' => $stats,
            'plan' => [
                'type' => $profile->plan_type,
                'event_limit' => $profile->getEventLimit(),
                'participant_limit' => $profile->getParticipantLimit(),
                'active_events_count' => $activeEventsCount,
            ]
        ]);
    }

    public function analytics(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile ?? $user->profile()->create([
            'full_name' => $user->name ?? 'Usuário sem nome',
            'plan_type' => 'freemium',
        ]);

        $events = Event::with(['analytics', 'confirmedParticipants'])
            ->where('organizer_id', $profile->id)
            ->where('event_date', '>=', now()->subMonths(3))
            ->get();

        $analyticsData = $events->map(function ($event) {
            return [
                'name' => $event->name,
                'date' => $event->event_date->format('d/m/Y'),
                'participants' => $event->confirmed_count,
                'revenue' => (float) $event->total_revenue,
                'conversion_rate' => (float) $event->conversion_rate,
            ];
        });

        return Inertia::render('Dashboard/Analytics', [
            'analytics' => $analyticsData,
            'total_revenue' => $events->sum('total_revenue'),
            'total_participants' => $events->sum('confirmed_count'),
            'average_conversion' => $events->avg('conversion_rate'),
        ]);
    }
}
