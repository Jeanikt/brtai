<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PriceTier;
use App\Models\EventAnalytic;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventPublicController extends Controller
{
    public function show($slug)
    {
        $event = Event::with(['priceTiers' => function ($query) {
            $query->where('is_active', true);
        }, 'organizer'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

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
                'price_tiers' => $event->priceTiers->map(function ($tier) {
                    return [
                        'id' => $tier->id,
                        'name' => $tier->name,
                        'description' => $tier->description,
                        'price' => $tier->price,
                        'max_quantity' => $tier->max_quantity,
                        'current_quantity' => $tier->current_quantity,
                        'is_active' => $tier->is_active,
                    ];
                }),
            ],
            'confirmed_count' => $confirmedCount,
            'available_slots' => $event->getAvailableSlots(),
        ]);
    }

    public function participate(Request $request, $slug)
    {
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

        if (!$priceTier->is_active || ($priceTier->max_quantity && $priceTier->current_quantity >= $priceTier->max_quantity)) {
            return redirect()->back()->withErrors([
                'price_tier' => 'Este lote não está mais disponível.'
            ]);
        }

        $confirmedCount = Participant::where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->count();

        if ($event->max_participants && $confirmedCount >= $event->max_participants) {
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

        $priceTier->increment('current_quantity');

        return redirect()->route('payment.checkout', $participant->id);
    }

    private function incrementAnalytics(Event $event)
    {
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

        $analytic->increment('page_views');
        $analytic->increment('unique_visitors');
    }
}
