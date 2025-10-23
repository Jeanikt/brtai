<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function participants(Event $event)
    {
        $this->authorize('view', $event);

        $participants = $event->participants()
            ->with('priceTier')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "participantes-{$event->slug}-" . now()->format('d-m-Y') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($participants) {
            $handle = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nome Completo',
                'E-mail',
                'Telefone',
                'Valor Pago (R$)',
                'Status Pagamento',
                'Lote',
                'Data Inscrição',
                'Data Confirmação',
                'Check-in Realizado'
            ]);

            foreach ($participants as $participant) {
                fputcsv($handle, [
                    $participant->full_name,
                    $participant->email,
                    $participant->phone,
                    number_format($participant->payment_amount, 2, ',', ''),
                    $this->getPaymentStatusText($participant->payment_status),
                    $participant->priceTier->name,
                    $participant->created_at->format('d/m/Y H:i'),
                    $participant->confirmed_at?->format('d/m/Y H:i') ?? 'N/A',
                    $participant->checked_in_at ? 'Sim' : 'Não'
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function eventFinancials(Event $event)
    {
        $this->authorize('view', $event);

        $participants = $event->participants()
            ->with('priceTier')
            ->where('payment_status', 'paid')
            ->orderBy('confirmed_at', 'desc')
            ->get();

        $filename = "financeiro-{$event->slug}-" . now()->format('d-m-Y') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($event, $participants) {
            $handle = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // Event summary
            fputcsv($handle, ['Resumo do Evento']);
            fputcsv($handle, ['Nome do Evento:', $event->name]);
            fputcsv($handle, ['Data do Evento:', $event->event_date->format('d/m/Y H:i')]);
            fputcsv($handle, ['Total Arrecadado:', 'R$ ' . number_format($event->total_revenue, 2, ',', '.')]);
            fputcsv($handle, ['Participantes Confirmados:', $event->confirmed_count]);
            fputcsv($handle, []);
            fputcsv($handle, []);

            // Participants details
            fputcsv($handle, ['Detalhamento dos Participantes']);
            fputcsv($handle, [
                'Nome',
                'E-mail',
                'Telefone',
                'Valor Bruto (R$)',
                'Taxa BrotaAI (R$)',
                'Valor Líquido (R$)',
                'Data Pagamento',
                'Lote'
            ]);

            $totalFees = 0;
            $totalNet = 0;

            foreach ($participants as $participant) {
                $fee = $this->calculateFee($participant->payment_amount, $event->organizer);
                $netAmount = $participant->payment_amount - $fee;

                $totalFees += $fee;
                $totalNet += $netAmount;

                fputcsv($handle, [
                    $participant->full_name,
                    $participant->email,
                    $participant->phone,
                    number_format($participant->payment_amount, 2, ',', ''),
                    number_format($fee, 2, ',', ''),
                    number_format($netAmount, 2, ',', ''),
                    $participant->confirmed_at->format('d/m/Y H:i'),
                    $participant->priceTier->name
                ]);
            }

            // Summary
            fputcsv($handle, []);
            fputcsv($handle, ['Resumo Financeiro']);
            fputcsv($handle, ['Total Bruto Arrecadado:', 'R$ ' . number_format($event->total_revenue, 2, ',', '.')]);
            fputcsv($handle, ['Total Taxas BrotaAI:', 'R$ ' . number_format($totalFees, 2, ',', '.')]);
            fputcsv($handle, ['Total Líquido:', 'R$ ' . number_format($totalNet, 2, ',', '.')]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getPaymentStatusText($status)
    {
        return match ($status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'refunded' => 'Reembolsado',
            'failed' => 'Falhou',
            default => 'Desconhecido'
        };
    }

    private function calculateFee($amount, $organizer)
    {
        $feePercentage = $organizer->isPro() ? 0.055 : 0.065;
        $fixedFee = 0.80;

        return ($amount * $feePercentage) + $fixedFee;
    }
}
