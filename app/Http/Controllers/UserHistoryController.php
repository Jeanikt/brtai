<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Buscar eventos onde o usuário é organizador e tem participantes confirmados
        $events = Event::with(['participants' => function ($query) {
            $query->where('payment_status', 'paid')
                ->whereNotNull('confirmed_at');
        }, 'participants.priceTier'])
            ->where('organizer_id', $profile->id)
            ->where(function ($query) {
                $query->where('event_date', '<', now())
                    ->orWhere('status', 'completed');
            })
            ->orderBy('event_date', 'desc')
            ->get();

        $participations = collect();

        foreach ($events as $event) {
            foreach ($event->participants as $participant) {
                $participations->push([
                    'id' => $participant->id,
                    'event_name' => $event->name,
                    'event_date' => $event->event_date,
                    'event_location' => $event->location,
                    'organizer_name' => $profile->full_name,
                    'ticket_type' => $participant->priceTier->name ?? 'Entrada Geral',
                    'paid_amount' => $participant->payment_amount,
                    'confirmed_at' => $participant->confirmed_at,
                    'checked_in_at' => $participant->checked_in_at,
                ]);
            }
        }

        // Paginação manual
        $page = request('page', 1);
        $perPage = 10;
        $paginated = $participations->slice(($page - 1) * $perPage, $perPage)->values();

        return Inertia::render('User/History', [
            'participations' => new \Illuminate\Pagination\LengthAwarePaginator(
                $paginated,
                $participations->count(),
                $perPage,
                $page,
                ['path' => request()->url()]
            )
        ]);
    }
}
