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
        $sort = $request->get('sort', 'distance');

        $eventsQuery = Event::with(['organizer', 'priceTiers' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('status', 'active')
            ->where('is_public', true)
            ->where('event_date', '>=', now())
            ->withCount(['participants as confirmed_count' => function ($query) {
                $query->where('payment_status', 'paid');
            }]);

        // Add distance calculation if location is available
        if ($userLat && $userLng) {
            $eventsQuery->addSelect([
                'distance' => DB::raw("
                    (6371 * acos(cos(radians({$userLat})) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians({$userLng})) +
                    sin(radians({$userLat})) * sin(radians(latitude))))
                ")
            ]);

            // Apply sorting based on user selection
            switch ($sort) {
                case 'date':
                    $eventsQuery->orderBy('event_date', 'asc');
                    break;
                case 'participants':
                    $eventsQuery->orderBy('confirmed_count', 'desc');
                    break;
                case 'distance':
                default:
                    $eventsQuery->orderBy('distance')->orderBy('event_date', 'asc');
                    break;
            }
        } else {
            // Default sorting when no location
            switch ($sort) {
                case 'participants':
                    $eventsQuery->orderBy('confirmed_count', 'desc');
                    break;
                case 'date':
                default:
                    $eventsQuery->orderBy('event_date', 'asc');
                    break;
            }
        }

        $events = $eventsQuery->paginate(12);

        // Transform events to include calculated fields
        $events->getCollection()->transform(function ($event) {
            $event->is_sold_out = $this->isEventSoldOut($event);
            $event->available_slots = $this->getAvailableSlots($event);
            return $event;
        });

        return Inertia::render('Events/PublicIndex', [
            'events' => $events,
            'hasLocation' => !is_null($userLat) && !is_null($userLng),
        ]);
    }

    private function isEventSoldOut($event)
    {
        // Check if event reached max participants
        if ($event->max_participants && $event->confirmed_count >= $event->max_participants) {
            return true;
        }

        // Check if all active price tiers are sold out
        $activeTiers = $event->priceTiers->where('is_active', true);
        if ($activeTiers->isEmpty()) {
            return true;
        }

        // If any tier has available slots, event is not sold out
        foreach ($activeTiers as $tier) {
            if (is_null($tier->max_quantity) || $tier->current_quantity < $tier->max_quantity) {
                return false;
            }
        }

        return true;
    }

    private function getAvailableSlots($event)
    {
        if ($event->max_participants) {
            return max(0, $event->max_participants - $event->confirmed_count);
        }

        return null; // Unlimited
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
        ]);
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
