<?php

namespace App\Http\Controllers;

use App\Models\AISuggestion;
use App\Models\Event;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function suggestions(Request $request)
    {
        $user = $request->user();
        $suggestions = AISuggestion::with('event')
            ->where('user_id', $user->profile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('AI/Suggestions', [
            'suggestions' => $suggestions,
        ]);
    }

    public function generate(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'suggestion_type' => 'required|in:event_theme,pricing,supplier,marketing,timing',
            'preferences' => 'nullable|array',
        ]);

        // TODO: Integrate with AI service (OpenAI, etc.)
        $suggestion = $this->generateAISuggestion(
            $user->profile,
            $request->suggestion_type,
            $request->event_id,
            $request->preferences ?? []
        );

        $aiSuggestion = AISuggestion::create([
            'user_id' => $user->profile->id,
            'event_id' => $request->event_id,
            'suggestion_type' => $request->suggestion_type,
            'content' => $suggestion,
            'confidence_score' => $suggestion['confidence'] ?? 0.7,
        ]);

        return response()->json([
            'suggestion' => $aiSuggestion,
            'message' => 'Sugestão gerada com sucesso!'
        ]);
    }

    public function applySuggestion(AISuggestion $suggestion)
    {
        $this->authorize('update', $suggestion);

        // TODO: Implement suggestion application logic based on type
        switch ($suggestion->suggestion_type) {
            case 'event_theme':
                $this->applyEventThemeSuggestion($suggestion);
                break;
            case 'pricing':
                $this->applyPricingSuggestion($suggestion);
                break;
            case 'supplier':
                $this->applySupplierSuggestion($suggestion);
                break;
        }

        $suggestion->markAsApplied();

        return redirect()->back()->with('success', 'Sugestão aplicada com sucesso!');
    }

    public function feedback(Request $request, AISuggestion $suggestion)
    {
        $this->authorize('update', $suggestion);

        $request->validate([
            'score' => 'required|integer|between:1,5',
        ]);

        $suggestion->update(['feedback_score' => $request->score]);

        return response()->json(['message' => 'Feedback registrado com sucesso!']);
    }

    private function generateAISuggestion($profile, $type, $eventId = null, $preferences = [])
    {
        // Mock AI suggestion generation
        // TODO: Replace with actual AI service integration

        $suggestions = [
            'event_theme' => [
                'theme' => 'Festa Neon',
                'description' => 'Festa com cores neon, luzes blacklight e música eletrônica',
                'decorations' => ['Tubos de neon', 'Tintas fluorescentes', 'Luzes blacklight'],
                'music_recommendations' => ['House', 'EDM', 'Pop eletrônico'],
                'confidence' => 0.8
            ],
            'pricing' => [
                'recommended_price' => 35.00,
                'price_range' => [25.00, 45.00],
                'explanation' => 'Baseado em eventos similares na sua região e no público-alvo',
                'confidence' => 0.7
            ],
            'supplier' => [
                'recommended_suppliers' => [
                    ['name' => 'DJ João', 'category' => 'dj', 'rating' => 4.8],
                    ['name' => 'Bar do Zé', 'category' => 'beverage', 'rating' => 4.5]
                ],
                'confidence' => 0.75
            ],
            'marketing' => [
                'channels' => ['Instagram', 'WhatsApp', 'TikTok'],
                'hashtags' => ['#festa', '#resenha', '#diversão'],
                'post_suggestions' => [
                    'Convide seus amigos para uma noite inesquecível! 🎉',
                    'Não fique de fora da melhor festa da cidade! ✨'
                ],
                'confidence' => 0.8
            ],
            'timing' => [
                'best_dates' => [now()->addDays(14)->format('Y-m-d'), now()->addDays(21)->format('Y-m-d')],
                'best_times' => ['22:00', '23:00'],
                'explanation' => 'Sábados à noite têm maior taxa de conversão',
                'confidence' => 0.85
            ]
        ];

        return $suggestions[$type] ?? ['error' => 'Tipo de sugestão não suportado'];
    }

    private function applyEventThemeSuggestion(AISuggestion $suggestion)
    {
        $content = $suggestion->content;
        $event = $suggestion->event;

        $event->update([
            'theme' => $content['theme'],
            'description' => $content['description'],
            'metadata' => array_merge($event->metadata ?? [], [
                'ai_suggested_decorations' => $content['decorations'],
                'ai_suggested_music' => $content['music_recommendations']
            ])
        ]);
    }

    private function applyPricingSuggestion(AISuggestion $suggestion)
    {
        $content = $suggestion->content;
        $event = $suggestion->event;

        // Create or update price tier with AI suggestion
        $event->priceTiers()->updateOrCreate(
            ['name' => 'Lote Recomendado'],
            [
                'price' => $content['recommended_price'],
                'is_active' => true
            ]
        );
    }

    private function applySupplierSuggestion(AISuggestion $suggestion)
    {
        $content = $suggestion->content;
        $event = $suggestion->event;

        $event->update([
            'metadata' => array_merge($event->metadata ?? [], [
                'ai_recommended_suppliers' => $content['recommended_suppliers']
            ])
        ]);
    }
}
