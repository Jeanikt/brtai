<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PriceTier;
use App\Models\EventAnalytic;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventPublicController extends Controller
{
    public function show($slug)
    {
        try {
            $event = Event::with(['priceTiers' => function ($query) {
                $query->where('is_active', true);
            }])
                ->where('slug', $slug)
                ->where('status', 'active')
                ->firstOrFail();

            // Increment page views
            $this->incrementAnalytics($event);

            return Inertia::render('Events/PublicShow', [
                'event' => $event,
                'confirmed_count' => $event->confirmed_count,
                'available_slots' => $event->getAvailableSlots(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir evento público: ' . $e->getMessage());
            abort(404, 'Evento não encontrado.');
        }
    }

    public function participate(Request $request, $slug)
    {
        try {
            $event = Event::where('slug', $slug)
                ->where('status', 'active')
                ->firstOrFail();

            $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'required|string|max:20',
                'price_tier_id' => 'required|exists:price_tiers,id',
            ]);

            $priceTier = PriceTier::findOrFail($request->price_tier_id);

            // Check if price tier is available
            if (!$priceTier->isAvailable()) {
                return redirect()->back()->withErrors([
                    'price_tier' => 'Este lote não está mais disponível.'
                ]);
            }

            // Check participant limit for free plan
            $organizer = $event->organizer;
            if ($organizer->plan_type === 'free' && $event->confirmed_count >= 70) {
                return redirect()->back()->withErrors([
                    'limit' => 'Este evento atingiu o limite de 70 participantes do plano Free. Peça ao organizador para fazer upgrade.'
                ]);
            }

            // Check event participant limit
            if (!$event->canAcceptMoreParticipants()) {
                return redirect()->back()->withErrors([
                    'limit' => 'Este evento está lotado.'
                ]);
            }

            $participant = Participant::create([
                'event_id' => $event->id,
                'price_tier_id' => $priceTier->id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'payment_amount' => $priceTier->price,
                'payment_status' => 'pending',
            ]);

            // Increment price tier quantity
            $priceTier->incrementQuantity();

            return redirect()->route('payment.checkout', $participant->id);
        } catch (\Exception $e) {
            Log::error('Erro ao processar participação: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'error' => 'Ocorreu um erro ao processar sua inscrição. Tente novamente.'
            ]);
        }
    }

    private function incrementAnalytics(Event $event)
    {
        try {
            $today = now()->format('Y-m-d');

            $analytic = EventAnalytic::firstOrCreate(
                [
                    'event_id' => $event->id,
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

            $analytic->incrementPageViews();
            $analytic->incrementUniqueVisitors();
        } catch (\Exception $e) {
            Log::error('Erro ao incrementar analytics: ' . $e->getMessage());
        }
    }
}
