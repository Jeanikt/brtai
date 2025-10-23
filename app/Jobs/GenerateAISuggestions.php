<?php

namespace App\Jobs;

use App\Models\AISuggestion;
use App\Models\Event;
use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateAISuggestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $suggestionType;
    public $eventId;
    public $preferences;

    public function __construct(Profile $user, $suggestionType, $eventId = null, $preferences = [])
    {
        $this->user = $user;
        $this->suggestionType = $suggestionType;
        $this->eventId = $eventId;
        $this->preferences = $preferences;
    }

    public function handle()
    {
        try {
            $suggestion = $this->callAIService();

            AISuggestion::create([
                'user_id' => $this->user->id,
                'event_id' => $this->eventId,
                'suggestion_type' => $this->suggestionType,
                'content' => $suggestion,
                'confidence_score' => $suggestion['confidence'] ?? 0.7,
            ]);

            Log::info('AI suggestion generated successfully', [
                'user_id' => $this->user->id,
                'suggestion_type' => $this->suggestionType
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating AI suggestion', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function callAIService()
    {
        // TODO: Integrate with actual AI service (OpenAI, etc.)
        // For now, using mock data

        $prompt = $this->buildPrompt();

        // Mock API call - replace with actual AI service
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert event planner helping users create amazing events.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            $content = $response->json()['choices'][0]['message']['content'];
            return $this->parseAIResponse($content);
        }

        // Fallback to mock data if API fails
        return $this->getMockSuggestion();
    }

    private function buildPrompt()
    {
        $event = $this->eventId ? Event::find($this->eventId) : null;

        $basePrompt = "Generate {$this->suggestionType} suggestions for an event";

        if ($event) {
            $basePrompt .= " called '{$event->name}'";
            $basePrompt .= " with theme: {$event->theme}";
            $basePrompt .= " scheduled for {$event->event_date->format('Y-m-d')}";
        }

        $basePrompt .= ". User preferences: " . json_encode($this->preferences);

        return $basePrompt;
    }

    private function parseAIResponse($content)
    {
        // Parse the AI response and structure it
        // This would depend on the AI service response format
        return [
            'content' => $content,
            'confidence' => 0.8,
            'source' => 'openai'
        ];
    }

    private function getMockSuggestion()
    {
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
            ]
        ];

        return $suggestions[$this->suggestionType] ?? ['error' => 'Tipo de sugestão não suportado'];
    }
}
