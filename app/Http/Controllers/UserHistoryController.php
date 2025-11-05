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

        // Buscar eventos onde o usuário é participante (ingressos comprados)
        // EXCLUINDO eventos onde ele é organizador
        $participations = Participant::with(['event.organizer', 'priceTier'])
            ->where('profile_id', $profile->id)
            ->where('payment_status', 'paid')
            ->whereNotNull('confirmed_at')
            ->whereHas('event', function ($query) use ($profile) {
                $query->where('organizer_id', '!=', $profile->id); // Exclui eventos onde é organizador
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'event_name' => $participant->event->name,
                    'event_date' => $participant->event->event_date,
                    'event_location' => $participant->event->location,
                    'organizer_name' => $participant->event->organizer->full_name,
                    'ticket_type' => $participant->priceTier->name ?? 'Entrada Geral',
                    'paid_amount' => $participant->payment_amount,
                    'confirmed_at' => $participant->confirmed_at,
                    'checked_in_at' => $participant->checked_in_at,
                    'type' => 'participation', // Tipo: participação
                    'event_status' => $participant->event->status,
                    'is_organizer' => false,
                ];
            });

        // Buscar eventos onde o usuário é organizador
        $organizedEvents = Event::withCount(['participants as confirmed_participants_count' => function ($query) {
            $query->where('payment_status', 'paid');
        }])
            ->where('organizer_id', $profile->id)
            ->where(function ($query) {
                $query->where('event_date', '<', now())
                    ->orWhere('status', 'completed')
                    ->orWhere('status', 'cancelled')
                    ->orWhere('status', 'active'); // Inclui eventos ativos também
            })
            ->orderBy('event_date', 'desc')
            ->get()
            ->map(function ($event) use ($profile) {
                return [
                    'id' => $event->id,
                    'event_name' => $event->name,
                    'event_date' => $event->event_date,
                    'event_location' => $event->location,
                    'organizer_name' => $profile->full_name, // Você é o organizador
                    'ticket_type' => 'Organizador',
                    'paid_amount' => 0, // Como organizador, não pagou
                    'confirmed_at' => $event->created_at,
                    'checked_in_at' => null,
                    'type' => 'organized', // Tipo: organizado
                    'event_status' => $event->status,
                    'is_organizer' => true,
                    'confirmed_participants_count' => $event->confirmed_participants_count,
                    'total_revenue' => $event->participants()->where('payment_status', 'paid')->sum('payment_amount'),
                ];
            });

        // Combinar e ordenar por data do evento (mais recente primeiro)
        $allHistory = $participations->concat($organizedEvents)
            ->sortByDesc('event_date')
            ->values();

        // Paginação manual - garantir que estamos usando arrays simples
        $page = request('page', 1);
        $perPage = 15;

        // Converter para array para evitar problemas com objetos
        $allHistoryArray = $allHistory->toArray();
        $paginated = array_slice($allHistoryArray, ($page - 1) * $perPage, $perPage);

        return Inertia::render('User/History', [
            'participations' => new \Illuminate\Pagination\LengthAwarePaginator(
                $paginated,
                count($allHistoryArray),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query()
                ]
            )
        ]);
    }
}
