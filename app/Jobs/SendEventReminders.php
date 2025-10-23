<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SendEventReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Find events happening in the next 24 hours
        $upcomingEvents = Event::where('event_date', '>=', now())
            ->where('event_date', '<=', now()->addHours(24))
            ->where('status', 'active')
            ->get();

        foreach ($upcomingEvents as $event) {
            $participants = $event->confirmedParticipants()->get();

            foreach ($participants as $participant) {
                Notification::create([
                    'participant_id' => $participant->id,
                    'event_id' => $event->id,
                    'type' => 'event_reminder',
                    'title' => 'Lembrete do Evento! 🎉',
                    'message' => "Não esqueça: {$event->name} é hoje às {$event->event_date->format('H:i')}!",
                    'channel' => 'whatsapp',
                    'status' => 'pending',
                ]);
            }

            // Notify organizer about event stats
            Notification::create([
                'user_id' => $event->organizer_id,
                'event_id' => $event->id,
                'type' => 'event_reminder',
                'title' => 'Seu Evento é Hoje!',
                'message' => "{$event->name} acontece hoje! Você tem {$event->confirmed_count} participantes confirmados.",
                'channel' => 'push',
                'status' => 'pending',
            ]);
        }
    }
}
