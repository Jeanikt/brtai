<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportEventData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $user;

    public function __construct(Event $event, $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    public function handle()
    {
        $filename = "export-{$this->event->slug}-" . now()->format('d-m-Y') . ".csv";
        $path = "exports/{$filename}";

        $handle = fopen(storage_path('app/' . $path), 'w');
        fputcsv($handle, [
            'Nome',
            'Email',
            'Telefone',
            'Valor Pago',
            'Status',
            'Data de Confirmação',
            'Check-in',
        ]);

        $participants = $this->event->participants()->with('priceTier')->get();

        foreach ($participants as $participant) {
            fputcsv($handle, [
                $participant->full_name,
                $participant->email,
                $participant->phone,
                $participant->payment_amount,
                $participant->payment_status,
                $participant->confirmed_at?->format('d/m/Y H:i'),
                $participant->checked_in_at ? 'Sim' : 'Não',
            ]);
        }

        fclose($handle);

        // TODO: Send email to user with download link
        // Mail::to($this->user->email)->send(new EventExportMail($path));
    }
}
