<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PriceTier;
use App\Models\Profile;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\SupabaseAuthService;
use App\Services\EventCacheService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
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

    private function getCurrentProfile()
    {
        $profile = $this->supabaseAuth->getUserFromRequest(request());

        if ($profile) {
            return $profile;
        }

        if (Auth::check() && Auth::user()->profile) {
            return Auth::user()->profile;
        }

        if (app()->environment('local')) {
            return $this->createTestProfile();
        }

        abort(403, 'Usuário não autenticado.');
    }

    private function createTestProfile()
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

    public function index(Request $request)
    {
        $profile = $this->getCurrentProfile();

        $events = $this->cacheService->getUserEvents($profile->id);
        $activeEventsCount = $this->cacheService->getUserActiveEventsCount($profile->id);

        return Inertia::render('Events/Index', [
            'events' => $events,
            'user_plan' => $profile->plan_type,
            'active_events_count' => (int) $activeEventsCount,
            'can_create_event' => $this->canCreateEvent($profile->plan_type, $profile->id),
        ]);
    }

    public function create()
    {
        $profile = $this->getCurrentProfile();
        $activeEventsCount = $this->cacheService->getUserActiveEventsCount($profile->id);

        return Inertia::render('Events/Create', [
            'user_plan' => $profile->plan_type,
            'active_events_count' => (int) $activeEventsCount,
            'can_create_event' => $this->canCreateEvent($profile->plan_type, $profile->id),
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $profile = $this->getCurrentProfile();

        if (!$this->canCreateEvent($profile->plan_type, $profile->id)) {
            return redirect()->back()->withErrors([
                'plan_limit' => $this->getPlanLimitMessage($profile->plan_type)
            ]);
        }

        $validated = $request->validated();

        $eventDateTime = $validated['event_date'] . ' ' . $validated['event_time'];

        $eventData = [
            'organizer_id' => $profile->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'event_date' => $eventDateTime,
            'location' => $validated['location'],
            'location_reveal_after_payment' => (bool) ($validated['location_reveal_after_payment'] ?? true),
            'theme' => $validated['theme'] ?? null,
            'rules' => $validated['rules'] ?? null,
            'max_participants' => $this->getMaxParticipantsForPlan($profile->plan_type, $validated['max_participants'] ?? null),
            'status' => 'draft',
            'is_public' => true,
        ];

        if (request()->hasFile('header_image')) {
            $headerImage = request()->file('header_image');

            if ($headerImage && $headerImage->isValid()) {
                try {
                    $imagePath = $this->imageService->uploadEventBanner(
                        $headerImage,
                        $profile->id
                    );

                    $eventData['header_image_url'] = $imagePath;
                } catch (\Exception $e) {
                    Log::error('Erro no upload da imagem: ' . $e->getMessage());

                    return redirect()->back()
                        ->withErrors(['header_image' => 'Erro ao fazer upload da imagem: ' . $e->getMessage()])
                        ->withInput();
                }
            }
        }

        try {
            $event = Event::create($eventData);
            $this->createPriceTiers($event, $validated);
            $this->cacheService->invalidateUserCaches($profile->id);

            return redirect()->route('events.show', $event->id)
                ->with('success', 'Evento criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar evento: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para visualizar este evento.');
        }

        $confirmedParticipants = $event->confirmedParticipants()->count();
        $pendingParticipants = $event->participants()->where('payment_status', 'pending')->count();
        $totalRevenue = $event->confirmedParticipants()->sum('payment_amount');

        $participants = $event->participants()
            ->with('priceTier')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'full_name' => $participant->full_name,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'payment_status' => $participant->payment_status,
                    'payment_amount' => $participant->payment_amount,
                    'checked_in_at' => $participant->checked_in_at,
                    'confirmed_at' => $participant->confirmed_at,
                ];
            });

        $stats = [
            'total_revenue' => number_format($totalRevenue, 2, ',', '.'),
            'confirmed_count' => $confirmedParticipants,
            'pending_payments' => $pendingParticipants,
            'pending_participants' => $pendingParticipants,
        ];

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'event_date' => $event->event_date,
                'location' => $event->location,
                'header_image_url' => $event->header_image_url,
                'max_participants' => $event->max_participants,
                'status' => $event->status,
                'is_public' => $event->is_public,
            ],
            'participants' => $participants,
            'stats' => $stats,
        ]);
    }

    public function edit(Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para editar este evento.');
        }

        $eventData = $this->cacheService->getEventForEdit($event->id);

        return Inertia::render('Events/Edit', [
            'event' => $eventData,
            'user_plan' => $profile->plan_type,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para atualizar este evento.');
        }

        $validated = $request->validated();

        if (isset($validated['max_participants'])) {
            $validated['max_participants'] = $this->getMaxParticipantsForPlan(
                $profile->plan_type,
                $validated['max_participants']
            );
        }

        if (request()->hasFile('header_image')) {
            $headerImage = request()->file('header_image');
            if ($headerImage && $headerImage->isValid()) {
                try {
                    if ($event->header_image_url) {
                        $this->imageService->deleteImage($event->header_image_url);
                    }

                    $validated['header_image_url'] = $this->imageService->uploadEventBanner(
                        $headerImage,
                        $profile->id
                    );
                } catch (\Exception $e) {
                    Log::error('Erro no upload da imagem durante atualização: ' . $e->getMessage());
                    return redirect()->back()
                        ->withErrors(['header_image' => 'Erro ao atualizar imagem: ' . $e->getMessage()])
                        ->withInput();
                }
            }
        }

        $event->update($validated);

        $this->cacheService->invalidateEventCaches($event->id);
        $this->cacheService->invalidateUserCaches($profile->id);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Evento atualizado com sucesso!');
    }

    public function destroy(Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para excluir este evento.');
        }

        if ($event->header_image_url) {
            $this->imageService->deleteImage($event->header_image_url);
        }

        $event->delete();

        $this->cacheService->invalidateEventCaches($event->id);
        $this->cacheService->invalidateUserCaches($profile->id);

        return redirect()->route('events.index')
            ->with('success', 'Evento excluído com sucesso!');
    }

    public function publish(Request $request, Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para publicar este evento.');
        }

        if (!$this->canCreateEvent($profile->plan_type, $profile->id)) {
            return redirect()->back()->withErrors([
                'plan_limit' => $this->getPlanLimitMessage($profile->plan_type)
            ]);
        }

        $event->update(['status' => 'active']);

        $this->cacheService->invalidateEventCaches($event->id);
        $this->cacheService->invalidateUserCaches($profile->id);

        return redirect()->route('events.show', $event->id)->with('success', 'Evento publicado com sucesso!');
    }

    public function unpublish(Request $request, Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para despublicar este evento.');
        }

        $event->update(['status' => 'draft']);

        $this->cacheService->invalidateEventCaches($event->id);
        $this->cacheService->invalidateUserCaches($profile->id);

        return redirect()->route('events.show', $event->id)->with('success', 'Evento despublicado com sucesso!');
    }

    public function analytics(Event $event)
    {
        $profile = $this->getCurrentProfile();

        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para visualizar as métricas deste evento.');
        }

        $analyticsData = $this->cacheService->getEventAnalytics($event->id);

        return Inertia::render('Events/Analytics', $analyticsData);
    }

    private function canCreateEvent($planType, $userId)
    {
        if ($planType === 'freemium') {
            $activeEventsCount = $this->cacheService->getUserActiveEventsCount($userId);
            return $activeEventsCount < 1;
        }

        return true;
    }

    private function getPlanLimitMessage($planType)
    {
        $messages = [
            'freemium' => 'Plano Free permite apenas 1 evento ativo por vez. Faça upgrade para criar mais eventos.',
            'pro' => 'Plano Pro permite até 10 eventos ativos simultaneamente.',
            'enterprise' => 'Plano Enterprise permite eventos ilimitados.',
        ];

        return $messages[$planType] ?? 'Limite de eventos atingido para seu plano.';
    }

    private function getMaxParticipantsForPlan($planType, $requestedMax)
    {
        $planLimits = [
            'freemium' => 70,
            'pro' => 500,
            'enterprise' => null,
        ];

        $planLimit = $planLimits[$planType] ?? 70;

        if ($requestedMax === null) {
            return $planLimit;
        }

        return min($requestedMax, $planLimit);
    }

    private function createPriceTiers(Event $event, array $validated)
    {
        $price = $validated['price'] ?? 30.00;

        PriceTier::create([
            'event_id' => $event->id,
            'name' => 'Entrada Geral',
            'price' => $price,
            'max_quantity' => null,
            'is_active' => true,
        ]);
    }

    public function testImageUpload(Request $request)
    {
        try {
            $profile = $this->getCurrentProfile();

            Log::info('Testando configuração do storage', [
                'disk' => config('filesystems.default'),
                'supabase_url' => config('filesystems.disks.supabase.url'),
                'bucket' => config('filesystems.disks.supabase.bucket')
            ]);

            $testFile = $request->file('test_image');
            if ($testFile) {
                $url = $this->imageService->uploadEventBanner($testFile, $profile->id);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'direct_url' => $url,
                    'profile_id' => $profile->id
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nenhuma imagem fornecida'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no teste de upload: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
