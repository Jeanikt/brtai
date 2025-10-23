<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PriceTier;
use App\Models\Profile;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    protected $supabaseAuth;

    public function __construct(SupabaseAuthService $supabaseAuth)
    {
        $this->supabaseAuth = $supabaseAuth;
    }

    /**
     * Obtém o perfil do usuário atual
     */
    private function getCurrentProfile()
    {
        // Se estiver autenticado via Laravel Auth (para desenvolvimento)
        if (Auth::check() && Auth::user()->profile) {
            return Auth::user()->profile;
        }

        // Tenta obter via Supabase Auth
        $profile = $this->supabaseAuth->getUserFromRequest(request());

        if ($profile) {
            return $profile;
        }

        // Para desenvolvimento: cria um perfil de teste
        if (app()->environment('local')) {
            return $this->supabaseAuth->createTestProfile();
        }

        abort(403, 'Usuário não autenticado.');
    }

    /**
     * Lista eventos do organizador logado.
     */
    public function index(Request $request)
    {
        $profile = $this->getCurrentProfile();

        $events = Event::with(['confirmedParticipants', 'priceTiers'])
            ->where('organizer_id', '=', $profile->id)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Exibe formulário de criação.
     */
    public function create()
    {
        return Inertia::render('Events/Create');
    }

    /**
     * Cria um novo evento.
     */
    public function store(StoreEventRequest $request)
    {
        $profile = $this->getCurrentProfile();

        if ($profile->plan_type === 'free') {
            $activeEventsCount = Event::where('organizer_id', '=', $profile->id)
                ->whereIn('status', ['draft', 'active'])
                ->count();

            if ($activeEventsCount >= 1) {
                return redirect()->back()->withErrors([
                    'limit' => 'Plano Free permite apenas 1 evento ativo por vez. Faça upgrade para criar mais eventos.'
                ]);
            }
        }

        // Usa validated() em vez de input() para acessar dados validados
        $validated = $request->validated();

        $data = [
            'organizer_id' => $profile->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'event_date' => $validated['event_date'],
            'location' => $validated['location'],
            'location_reveal_after_payment' => (bool) ($validated['location_reveal_after_payment'] ?? true),
            'theme' => $validated['theme'] ?? null,
            'rules' => $validated['rules'] ?? null,
            'max_participants' => $validated['max_participants'] ?? null,
            'status' => 'draft',
            'is_public' => true,
        ];

        $event = Event::create($data);

        // Cria níveis de preço
        $priceTiers = $validated['price_tiers'] ?? [];
        if (!empty($priceTiers)) {
            foreach ($priceTiers as $tierData) {
                PriceTier::create([
                    'event_id' => $event->id,
                    'name' => $tierData['name'] ?? 'Entrada',
                    'price' => $tierData['price'] ?? 0,
                    'max_quantity' => $tierData['max_quantity'] ?? null,
                    'is_active' => true,
                ]);
            }
        } else {
            // Cria um price tier padrão se nenhum for fornecido
            PriceTier::create([
                'event_id' => $event->id,
                'name' => 'Entrada Geral',
                'price' => 30.00,
                'max_quantity' => null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Evento criado com sucesso!');
    }

    /**
     * Exibe um evento.
     */
    public function show(Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para ver este evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para visualizar este evento.');
        }

        $event->load(['priceTiers', 'participants', 'confirmedParticipants']);

        return Inertia::render('Events/Show', [
            'event' => $event,
            'participants' => $event->participants()->orderBy('created_at', 'desc')->get(),
            'stats' => [
                'total_revenue' => $event->total_revenue,
                'confirmed_count' => $event->confirmed_count,
                'conversion_rate' => $event->conversion_rate,
            ]
        ]);
    }

    /**
     * Edita um evento.
     */
    public function edit(Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para editar este evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para editar este evento.');
        }

        $event->load('priceTiers');

        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Atualiza um evento existente.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para atualizar este evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para atualizar este evento.');
        }

        $validated = $request->validated();
        $event->update($validated);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Evento atualizado com sucesso!');
    }

    /**
     * Exclui um evento.
     */
    public function destroy(Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para excluir este evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para excluir este evento.');
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Evento excluído com sucesso!');
    }

    /**
     * Publica um evento (torna ativo).
     */
    public function publish(Request $request, Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para publicar este evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para publicar este evento.');
        }

        $event->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Evento publicado com sucesso!');
    }

    /**
     * Exibe métricas do evento.
     */
    public function analytics(Event $event)
    {
        $profile = $this->getCurrentProfile();

        // Verifica se o usuário tem permissão para ver as métricas deste evento
        if ($event->organizer_id !== $profile->id) {
            abort(403, 'Você não tem permissão para visualizar as métricas deste evento.');
        }

        $event->load(['analytics', 'participants']);

        return Inertia::render('Events/Analytics', [
            'event' => $event,
            'analytics' => $event->analytics,
            'participants' => $event->participants,
        ]);
    }
}
