<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PriceTier;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventPublicController extends Controller
{
    public function show($slug)
    {
        $event = Event::with([
            'priceTiers' => fn($query) => $query->where('is_active', true),
            'organizer'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($event->status !== 'active' || !$event->is_public) {
            abort(404, 'Evento não encontrado.');
        }

        $confirmedCount = Participant::where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->count();

        $this->incrementAnalytics($event);

        return Inertia::render('Events/PublicShow', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'slug' => $event->slug,
                'event_date' => $event->event_date,
                'location' => $event->location,
                'location_reveal_after_payment' => $event->location_reveal_after_payment,
                'header_image_url' => $event->header_image_url,
                'max_participants' => $event->max_participants,
                'is_free' => $event->is_free,
                'organizer' => [
                    'full_name' => $event->organizer->full_name,
                ],
                'price_tiers' => $event->priceTiers->map(fn($tier) => [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'description' => $tier->description,
                    'price' => $tier->price,
                    'max_quantity' => $tier->max_quantity,
                    'current_quantity' => $tier->current_quantity,
                    'is_active' => $tier->is_active,
                ]),
            ],
            'confirmed_count' => $confirmedCount,
            'available_slots' => $this->getAvailableSlots($event),
            'isAuthenticated' => Auth::check(),
        ]);
    }

    public function participate(Request $request, $slug)
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login', ['return_url' => url()->current()])
                ->with('error', 'Você precisa estar logado para participar deste evento.');
        }

        $event = Event::where('slug', $slug)->firstOrFail();

        if ($event->status !== 'active' || !$event->is_public) {
            return back()->with('error', 'Este evento não está mais disponível.');
        }

        $validator = Validator::make($request->all(), [
            'participants' => 'required|array|min:1',
            'participants.*.full_name' => 'required|string|max:255',
            'participants.*.email' => 'nullable|email',
            'participants.*.phone' => 'required|string|max:20',
            'participants.*.cpf' => 'required|string|max:14',
            'price_tier_id' => 'required|exists:price_tiers,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $priceTier = PriceTier::find($request->price_tier_id);

        // Verificar se o tier pertence ao evento
        if ($priceTier->event_id !== $event->id) {
            return back()->with('error', 'Tier de preço inválido.');
        }

        // Verificar disponibilidade
        if ($priceTier->max_quantity && $priceTier->current_quantity >= $priceTier->max_quantity) {
            return back()->with('error', 'Este tipo de ingresso está esgotado.');
        }

        try {
            DB::beginTransaction();

            $participantsData = [];
            foreach ($request->participants as $participantData) {
                // Limpar CPF para validação
                $cleanCpf = preg_replace('/\D/', '', $participantData['cpf']);

                // Verificar se CPF já está inscrito neste evento
                $existingParticipant = Participant::where('event_id', $event->id)
                    ->where('cpf', $cleanCpf)
                    ->first();

                if ($existingParticipant) {
                    DB::rollBack();
                    return back()->with('error', "O CPF {$participantData['cpf']} já está inscrito neste evento.");
                }

                // Verificar se telefone já está inscrito
                $cleanPhone = preg_replace('/\D/', '', $participantData['phone']);
                $existingPhone = Participant::where('event_id', $event->id)
                    ->where('phone', $cleanPhone)
                    ->first();

                if ($existingPhone) {
                    DB::rollBack();
                    return back()->with('error', "O telefone {$participantData['phone']} já está inscrito neste evento.");
                }

                $participant = new Participant([
                    'event_id' => $event->id,
                    'price_tier_id' => $priceTier->id,
                    'profile_id' => Auth::id(),
                    'full_name' => $participantData['full_name'],
                    'email' => $participantData['email'],
                    'phone' => $cleanPhone,
                    'cpf' => $cleanCpf,
                    'payment_status' => $event->is_free ? 'paid' : 'pending',
                    'payment_amount' => $event->is_free ? 0 : $priceTier->price,
                    'confirmed_at' => $event->is_free ? now() : null,
                ]);

                $participant->save();
                $participantsData[] = $participant;

                // Atualizar quantidade do tier
                $priceTier->increment('current_quantity');
            }

            DB::commit();

            if ($event->is_free) {
                return redirect()->route('payment.success', $participantsData[0]->id)
                    ->with('success', 'Inscrição confirmada com sucesso!');
            } else {
                // Redirecionar para página de pagamento
                return redirect()->route('payment.checkout', $participantsData[0]->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Log do erro para debugging
            Log::error('Erro ao processar inscrição: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return back()->with('error', 'Erro ao processar inscrição. Tente novamente.');
        }
    }

    private function getAvailableSlots($event)
    {
        if ($event->max_participants) {
            $confirmedCount = Participant::where('event_id', $event->id)
                ->where('payment_status', 'paid')
                ->count();
            return max(0, $event->max_participants - $confirmedCount);
        }
        return null;
    }

    private function incrementAnalytics(Event $event)
    {
        $today = now()->format('Y-m-d');

        $analytic = \App\Models\EventAnalytic::firstOrCreate(
            [
                'event_id' => $event->id,
                'date' => $today,
            ],
            [
                'page_views' => 0,
                'unique_visitors' => 0,
                'tickets_sold' => 0,
                'total_revenue' => 0,
                'conversion_rate' => 0,
            ]
        );

        $analytic->increment('page_views');

        // Incrementar visitantes únicos baseado em IP ou sessão
        $analytic->increment('unique_visitors');
    }
}
