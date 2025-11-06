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
            'isAuthenticated' => Auth::check(), // Adicionado para verificar autenticação no frontend
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
}
