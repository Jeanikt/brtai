<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use App\Http\Resources\ParticipantResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ParticipantController extends Controller
{
    use AuthorizesRequests;

    public function index(Event $event)
    {
        $this->authorize('view', $event);

        $participants = $event->participants()
            ->with('priceTier')
            ->orderBy('created_at', 'desc')
            ->get();

        return ParticipantResource::collection($participants);
    }

    public function show(Participant $participant)
    {
        $this->authorize('view', $participant->event);

        return new ParticipantResource($participant->load('priceTier', 'paymentTransaction'));
    }

    public function update(Request $request, Participant $participant)
    {
        $this->authorize('update', $participant->event);

        $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|required|string|max:20',
            'payment_status' => 'sometimes|in:pending,paid,refunded',
        ]);

        $participant->update($request->all());

        return redirect()->back()->with('success', 'Participante atualizado com sucesso!');
    }

    public function destroy(Participant $participant)
    {
        $this->authorize('delete', $participant->event);

        if ($participant->isPaid()) {
            $participant->priceTier->decrement('current_quantity');
        }

        $participant->delete();

        return redirect()->back()->with('success', 'Participante removido com sucesso!');
    }

    public function checkIn(Participant $participant)
    {
        $this->authorize('update', $participant->event);

        if (!$participant->isPaid()) {
            return redirect()->back()->withErrors([
                'error' => 'Não é possível fazer check-in de um participante que não pagou.'
            ]);
        }

        $participant->update(['checked_in_at' => now()]);

        return redirect()->back()->with('success', 'Check-in realizado com sucesso!');
    }

    public function export(Event $event)
    {
        $this->authorize('view', $event);

        $participants = $event->participants()
            ->with('priceTier')
            ->get();

        $filename = "participantes-{$event->slug}-" . now()->format('d-m-Y') . ".csv";

        $handle = fopen('php://output', 'w');
        fputcsv($handle, [
            'Nome',
            'Email',
            'Telefone',
            'Valor Pago',
            'Status',
            'Data de Confirmação',
            'Check-in',
        ]);

        foreach ($participants as $participant) {
            fputcsv($handle, [
                $participant->full_name,
                $participant->email,
                $participant->phone,
                $participant->payment_amount,
                $participant->payment_status,
                $participant->confirmed_at?->format('d/m/Y H:i'),
                $participant->checked_in_at ? 'Sim' : 'Não',
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($handle) {}, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
