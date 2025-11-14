<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PriceTier;
use App\Models\Profile;
use App\Services\SupabaseAuthService;
use App\Services\EventCacheService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PublicEventController extends Controller
{
    use AuthorizesRequests;

    protected $supabaseAuth;
    protected $cacheService;
    protected $imageService;

    public function __construct(
        SupabaseAuthService $supabaseAuth,
        EventCacheService $cacheService,
        ImageService $imageService
    ) {
        $this->supabaseAuth = $supabaseAuth;
        $this->cacheService = $cacheService;
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of public events
     */
    public function index(Request $request): Response
    {
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');
        $sort = $request->query('sort', 'distance');

        $query = Event::with(['priceTiers', 'organizer'])
            ->where('is_public', true)
            ->where('status', 'active')
            ->where('event_date', '>', now());

        // Apply location-based filtering if coordinates are provided
        if ($latitude && $longitude) {
            $earthRadius = 6371; // kilometers

            $query->select('*')
                ->selectRaw(
                    "(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance",
                    [$earthRadius, $latitude, $longitude, $latitude]
                )
                ->having('distance', '<', 50) // Within 50km radius
                ->orderBy('distance');
        } else {
            // Default sorting by date
            $query->orderBy('event_date', 'asc');
        }

        // Apply other sorting options
        if ($sort === 'date') {
            $query->orderBy('event_date', 'asc');
        } elseif ($sort === 'participants') {
            $query->withCount('confirmedParticipants')
                ->orderBy('confirmed_participants_count', 'desc');
        }

        $events = $query->paginate(12);

        // Add additional data to each event without modifying the model
        $events->getCollection()->transform(function ($event) {
            $eventData = $event->toArray();
            $eventData['confirmed_count'] = $event->confirmedParticipants()->count();
            $eventData['available_slots'] = $event->getAvailableSlots();
            $eventData['distance'] = $event->distance ?? null;

            return $eventData;
        });

        return Inertia::render('Events/PublicIndex', [
            'events' => $events,
            'hasLocation' => !empty($latitude) && !empty($longitude),
            'filters' => [
                'sort' => $sort,
            ]
        ]);
    }

    /**
     * Store user location for event filtering
     */
    public function storeLocation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            // Store location in session
            session([
                'user_latitude' => $request->latitude,
                'user_longitude' => $request->longitude,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Localização salva com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar localização: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar localização'
            ], 500);
        }
    }

    /**
     * Display the specified public event
     */
    public function show(Request $request, string $slug): Response
    {
        $event = Event::with(['priceTiers', 'organizer'])
            ->where('slug', $slug)
            ->where('is_public', true)
            ->where('status', 'active')
            ->firstOrFail();

        $eventData = $event->toArray();
        $eventData['confirmed_count'] = $event->confirmedParticipants()->count();
        $eventData['available_slots'] = $event->getAvailableSlots();

        // Get current user profile if authenticated
        $profile = null;
        try {
            $profile = $this->getCurrentProfile($request);
        } catch (\Exception $e) {
            // User is not authenticated, continue without profile
        }

        return Inertia::render('Events/PublicShow', [
            'event' => $eventData,
            'currentProfile' => $profile,
            'isSoldOut' => !$event->canAcceptMoreParticipants(),
            'isAlmostSoldOut' => $event->getAvailableSlots() !== null && $event->getAvailableSlots() <= 10,
        ]);
    }

    /**
     * Search events - Simplificado para usar a mesma view do index
     */
    public function search(Request $request): Response
    {
        $searchQuery = $request->query('q');
        $latitude = $request->query('lat');
        $longitude = $request->query('lng');

        $eventsQuery = Event::with(['priceTiers', 'organizer'])
            ->where('is_public', true)
            ->where('status', 'active')
            ->where('event_date', '>', now());

        if ($searchQuery) {
            $eventsQuery->where(function ($q) use ($searchQuery) {
                $q->where('name', 'ilike', "%{$searchQuery}%")
                    ->orWhere('description', 'ilike', "%{$searchQuery}%")
                    ->orWhere('location', 'ilike', "%{$searchQuery}%")
                    ->orWhere('theme', 'ilike', "%{$searchQuery}%");
            });
        }

        // Apply location-based filtering if coordinates are provided
        if ($latitude && $longitude) {
            $earthRadius = 6371;

            $eventsQuery->select('*')
                ->selectRaw(
                    "(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance",
                    [$earthRadius, $latitude, $longitude, $latitude]
                )
                ->having('distance', '<', 50)
                ->orderBy('distance');
        } else {
            $eventsQuery->orderBy('event_date', 'asc');
        }

        $events = $eventsQuery->paginate(12);

        // Add additional data to each event without modifying the model
        $events->getCollection()->transform(function ($event) {
            $eventData = $event->toArray();
            $eventData['confirmed_count'] = $event->confirmedParticipants()->count();
            $eventData['available_slots'] = $event->getAvailableSlots();
            $eventData['distance'] = $event->distance ?? null;
            return $eventData;
        });

        return Inertia::render('Events/PublicIndex', [
            'events' => $events,
            'searchQuery' => $searchQuery,
            'hasLocation' => !empty($latitude) && !empty($longitude),
        ]);
    }

    /**
     * Get current user profile
     */
    private function getCurrentProfile(Request $request): ?Profile
    {
        $profile = $this->supabaseAuth->getUserFromRequest($request);

        if ($profile) {
            return $profile;
        }

        if (Auth::check() && Auth::user()->profile) {
            return Auth::user()->profile;
        }

        if (app()->environment('local')) {
            return $this->createTestProfile();
        }

        return null;
    }

    /**
     * Create test profile for local development
     */
    private function createTestProfile(): Profile
    {
        return Profile::firstOrCreate(
            ['metadata->is_test_user' => true],
            [
                'id' => (string) Str::uuid(),
                'full_name' => 'Usuário de Teste',
                'plan_type' => 'freemium',
                'metadata' => ['is_test_user' => true],
            ]
        );
    }
}
