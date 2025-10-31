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

        $query = Event::with(['confirmedParticipants', 'priceTiers', 'participants'])
            ->where('organizer_id', $profile->id);

        if ($request->has('filter') && $request->filter !== 'all') {
            $query->where('status', $request->filter);
        }

        $events = $query->orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) {
                // Calcular preço mais baixo
                $lowestPrice = $event->priceTiers->min('price') ?? 0;

                // Calcular revenue total (somente participantes pagos)
                $totalRevenue = $event->confirmedParticipants->sum('payment_amount');

                // Contar participantes confirmados
                $confirmedCount = $event->confirmedParticipants->count();

                // Contar participantes pendentes
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
                    'price' => $lowestPrice,
                    'slug' => $event->slug,
                    'max_participants' => $event->max_participants,
                ];
            });

        $activeEventsCount = Event::where('organizer_id', $profile->id)
            ->where('status', 'active')
            ->count();

        // Calcular estatísticas totais
        $totalRevenue = $events->sum('total_revenue');
        $totalParticipants = $events->sum('confirmed_count');
        $totalPending = $events->sum('pending_count');

        $stats = [
            'total_events' => $events->count(),
            'total_revenue' => number_format($totalRevenue, 2, ',', '.'),
            'total_participants' => $totalParticipants,
            'total_pending' => $totalPending,
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
            ],
            'filters' => $request->only(['filter'])
        ]);
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
