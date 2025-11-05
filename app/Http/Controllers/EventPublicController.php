<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PriceTier;
use App\Models\EventAnalytic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Exception;
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

        if ($event->status !== 'active' && !$event->is_public) {
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
            'available_slots' => $event->getAvailableSlots(),
        ]);
    }

    public function participate(Request $request, $slug)
    {
        Log::info('Participate method called for event: ' . $slug);
        Log::info('Request data: ', $request->all());

        $event = Event::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        Log::info('Event found: ', [$event->id, $event->name, $event->is_free]);

        $priceTier = PriceTier::findOrFail($request->price_tier_id);

        Log::info('Price tier found: ', [$priceTier->id, $priceTier->name, $priceTier->is_active]);

        $validator = Validator::make($request->all(), [
            'participants' => 'required|array|min:1|max:10',
            'participants.*.full_name' => 'required|string|max:255',
            'participants.*.email' => 'nullable|email',
            'participants.*.phone' => 'required|string|max:20',
            'participants.*.cpf' => 'required|string|size:11',
            'price_tier_id' => 'required|exists:price_tiers,id',
        ], [
            'participants.*.cpf.size' => 'O CPF deve ter 11 dígitos.',
            'participants.*.cpf.required' => 'O CPF é obrigatório para cada participante.',
            'participants.*.phone.required' => 'O telefone é obrigatório para cada participante.',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ', $validator->errors()->toArray());
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$priceTier->is_active || ($priceTier->max_quantity && $priceTier->current_quantity + count($request->participants) > $priceTier->max_quantity)) {
            Log::error('Price tier not available');
            return back()->withErrors(['price_tier' => 'Este lote não tem ingressos suficientes.']);
        }

        $confirmedCount = Participant::where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->count();

        if ($event->max_participants && $confirmedCount + count($request->participants) > $event->max_participants) {
            Log::error('Event full');
            return back()->withErrors(['limit' => 'Este evento não tem vagas suficientes.']);
        }

        $cpfs = collect($request->participants)->pluck('cpf');
        $phones = collect($request->participants)->pluck('phone');

        if ($cpfs->count() !== $cpfs->unique()->count()) {
            Log::error('Duplicate CPFs');
            return back()->withErrors(['cpf' => 'Não é possível usar o mesmo CPF para múltiplos participantes.']);
        }

        if ($phones->count() !== $phones->unique()->count()) {
            Log::error('Duplicate phones');
            return back()->withErrors(['phone' => 'Não é possível usar o mesmo telefone para múltiplos participantes.']);
        }

        $existingCpfs = Participant::where('event_id', $event->id)
            ->whereIn('cpf', $cpfs)
            ->pluck('cpf')
            ->toArray();

        $existingPhones = Participant::where('event_id', $event->id)
            ->whereIn('phone', $phones)
            ->pluck('phone')
            ->toArray();

        if (!empty($existingCpfs)) {
            Log::error('CPFs already registered');
            return back()->withErrors(['cpf' => 'Os seguintes CPFs já estão inscritos neste evento: ' . implode(', ', $existingCpfs)]);
        }

        if (!empty($existingPhones)) {
            Log::error('Phones already registered');
            return back()->withErrors(['phone' => 'Os seguintes telefones já estão inscritos neste evento: ' . implode(', ', $existingPhones)]);
        }

        $paymentStatus = $event->is_free ? 'paid' : 'pending';
        $confirmedAt = $event->is_free ? now() : null;

        $createdParticipants = [];

        try {
            foreach ($request->participants as $participantData) {
                $participantData = [
                    'event_id' => $event->id,
                    'price_tier_id' => $priceTier->id,
                    'full_name' => $participantData['full_name'],
                    'email' => $participantData['email'] ?? null,
                    'phone' => $participantData['phone'],
                    'cpf' => $participantData['cpf'],
                    'payment_amount' => $priceTier->price,
                    'payment_status' => $paymentStatus,
                    'confirmed_at' => $confirmedAt,
                ];

                if (Auth::check() && Auth::user()->profile) {
                    $participantData['profile_id'] = Auth::user()->profile->id;
                }

                Log::info('Creating participant: ', $participantData);
                $participant = Participant::create($participantData);
                Log::info('Participant created: ' . $participant->id);
                $createdParticipants[] = $participant;
            }

            $priceTier->increment('current_quantity', count($request->participants));
            Log::info('Price tier quantity incremented.');

            if ($event->is_free) {
                Log::info('Redirecting to success for free event');
                return redirect()->route('payment.success', $createdParticipants[0]->id);
            }

            Log::info('Redirecting to checkout for paid event');
            return redirect()->route('payment.checkout', $createdParticipants[0]->id);
        } catch (Exception $e) {
            Log::error('Error creating participant: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Erro interno ao processar inscrição.']);
        }
    }

    private function incrementAnalytics(Event $event)
    {
        $today = now()->format('Y-m-d');

        $analytic = EventAnalytic::firstOrCreate(
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
        $analytic->increment('unique_visitors');
    }
}
