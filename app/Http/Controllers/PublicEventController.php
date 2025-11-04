<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\PriceTier;
use App\Models\EventAnalytic;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublicEventController extends Controller
{
    public function index(Request $request)
    {
        $userLat = $request->session()->get('user_lat');
        $userLng = $request->session()->get('user_lng');

        $eventsQuery = Event::with(['organizer', 'priceTiers' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('status', 'active')
            ->where('is_public', true)
            ->where('event_date', '>=', now())
            ->withCount(['participants as confirmed_count' => function ($query) {
                $query->where('payment_status', 'paid');
            }]);

        if ($userLat && $userLng) {
            $eventsQuery->addSelect([
                'distance' => DB::raw("
                    (6371 * acos(cos(radians({$userLat})) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians({$userLng})) +
                    sin(radians({$userLat})) * sin(radians(latitude))))
                ")
            ])
                ->orderBy('distance')
                ->orderBy('event_date', 'asc');
        } else {
            $eventsQuery->orderBy('event_date', 'asc');
        }

        $events = $eventsQuery->paginate(12);

        return Inertia::render('Events/PublicIndex', [
            'events' => $events,
            'hasLocation' => !is_null($userLat) && !is_null($userLng),
        ]);
    }

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
        $event = Event::where('slug', $slug)
            ->where('status', 'active')
            ->where('is_public', true)
            ->firstOrFail();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|max:20',
            'price_tier_id' => 'required|exists:price_tiers,id',
        ]);

        $priceTier = PriceTier::findOrFail($request->price_tier_id);

        if (!$priceTier->is_active || ($priceTier->max_quantity && $priceTier->current_quantity >= $priceTier->max_quantity)) {
            return back()->withErrors(['price_tier' => 'Este lote não está mais disponível.']);
        }

        $confirmedCount = Participant::where('event_id', $event->id)
            ->where('payment_status', 'paid')
            ->count();

        if ($event->max_participants && $confirmedCount >= $event->max_participants) {
            return back()->withErrors(['limit' => 'Este evento está lotado.']);
        }

        $paymentStatus = $event->is_free ? 'paid' : 'pending';
        $confirmedAt = $event->is_free ? now() : null;

        $participantData = [
            'event_id' => $event->id,
            'price_tier_id' => $priceTier->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'payment_amount' => $priceTier->price,
            'payment_status' => $paymentStatus,
            'confirmed_at' => $confirmedAt,
        ];

        if (Auth::check() && Auth::user()->profile) {
            $participantData['profile_id'] = Auth::user()->profile->id;
        }

        $participant = Participant::create($participantData);
        $priceTier->increment('current_quantity');

        if ($event->is_free) {
            return redirect()->route('payment.success', $participant->id);
        }

        return redirect()->route('payment.checkout', $participant->id);
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        session([
            'user_lat' => $request->latitude,
            'user_lng' => $request->longitude,
        ]);

        return response()->json(['success' => true]);
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
