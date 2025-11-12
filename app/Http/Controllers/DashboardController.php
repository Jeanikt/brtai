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
            'plan_type' => $user->profile?->plan_type ?? 'freemium',
        ]);

        $query = Event::with(['confirmedParticipants', 'priceTiers', 'participants'])
            ->where('organizer_id', $profile->id);

        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('status', $request->filter);
        }

        $events = $query->orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) {
                $lowestPrice = $event->priceTiers->min('price') ?? 0;
                $totalRevenue = $event->confirmedParticipants->sum('payment_amount');
                $confirmedCount = $event->confirmedParticipants->count();
                $pendingCount = $event->participants()->where('payment_status', 'pending')->count();

                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'event_date' => $event->event_date,
                    'location' => $event->location,
                    'header_image_url' => $event->header_image_url,
                    'status' => $event->status,
                    'confirmed_count' => $confirmedCount,
                    'pending_count' => $pendingCount,
                    'total_revenue' => $totalRevenue,
                    'price' => (float) $lowestPrice,
                    'slug' => $event->slug,
                    'max_participants' => $event->max_participants,
                ];
            });

        $activeEventsCount = Event::where('organizer_id', $profile->id)
            ->where('status', 'active')
            ->count();

        $canCreateEvent = $this->canCreateEvent($profile->plan_type, $profile->id, $activeEventsCount);

        return Inertia::render('Dashboard/Index', [
            'events' => $events,
            'user_plan' => $profile->plan_type,
            'active_events_count' => $activeEventsCount,
            'can_create_event' => $canCreateEvent,
            'filters' => $request->only(['filter'])
        ]);
    }

    private function canCreateEvent($planType, $userId, $activeEventsCount)
    {
        if ($planType === 'freemium') {
            return $activeEventsCount < 1;
        }

        return true;
    }

    public function analytics(Request $request)
    {
        $user = $request->user();

        $profile = $user->profile ?? $user->profile()->create([
            'full_name' => $user->name ?? 'Usuário sem nome',
            'plan_type' => 'freemium',
        ]);

        $events = Event::with(['analytics', 'confirmedParticipants', 'participants'])
            ->where('organizer_id', $profile->id)
            ->where('event_date', '>=', now()->subMonths(3))
            ->get()
            ->map(function ($event) {
                $confirmedCount = $event->confirmedParticipants->count();
                $totalParticipants = $event->participants->count();
                $totalRevenue = $event->confirmedParticipants->sum('payment_amount');

                $conversionRate = $totalParticipants > 0
                    ? ($confirmedCount / $totalParticipants) * 100
                    : 0;

                return [
                    'name' => $event->name,
                    'date' => $event->event_date->format('d/m/Y'),
                    'participants' => $confirmedCount,
                    'revenue' => (float) $totalRevenue,
                    'conversion_rate' => (float) $conversionRate,
                ];
            });

        $totalRevenue = $events->sum('revenue');
        $totalParticipants = $events->sum('participants');
        $averageConversion = $events->avg('conversion_rate');

        return Inertia::render('Dashboard/Analytics', [
            'analytics' => $events,
            'total_revenue' => $totalRevenue,
            'total_participants' => $totalParticipants,
            'average_conversion' => $averageConversion,
        ]);
    }
}
