<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout(Participant $participant)
    {
        $event = $participant->event;

        // Check if participant already paid
        if ($participant->isPaid()) {
            return redirect()->route('payment.success', $participant->id);
        }

        // Check if Pix is expired
        if ($participant->pix_code && $participant->isPixExpired()) {
            $participant->update([
                'pix_code' => null,
                'pix_expires_at' => null,
            ]);
        }

        // Generate new Pix if needed
        if (!$participant->pix_code) {
            $pixData = $this->generatePix($participant);
            $participant->update([
                'pix_code' => $pixData['pix_code'],
                'pix_expires_at' => $pixData['expires_at'],
            ]);
        }

        return Inertia::render('Payment/Checkout', [
            'participant' => $participant->load('event', 'priceTier'),
            'pix_code' => $participant->pix_code,
            'pix_expires_at' => $participant->pix_expires_at,
        ]);
    }

    public function success(Participant $participant)
    {
        if (!$participant->isPaid()) {
            return redirect()->route('payment.checkout', $participant->id);
        }

        $event = $participant->event;

        return Inertia::render('Payment/Success', [
            'participant' => $participant,
            'event' => $event,
            'location' => $event->location_reveal_after_payment ? $event->location : null,
            'whatsapp_group' => $event->metadata['whatsapp_group'] ?? null,
        ]);
    }

    public function status(Participant $participant)
    {
        return response()->json([
            'paid' => $participant->isPaid(),
            'status' => $participant->payment_status,
        ]);
    }

    private function generatePix(Participant $participant)
    {
        // TODO: Integrate with AbacatePay API
        // This is a mock implementation
        $pixCode = "00020126580014br.gov.bcb.pix0136" . uniqid() . "5204000053039865406" . number_format($participant->payment_amount, 2, '.', '') . "5802BR5913BROTAAI PLAT6008BRASILIA62070503***6304" . strtoupper(uniqid());

        return [
            'pix_code' => $pixCode,
            'expires_at' => now()->addHours(24),
            'qr_code' => $this->generateQrCode($pixCode),
        ];
    }

    private function generateQrCode($pixCode)
    {
        // TODO: Generate QR code image using a library
        return "data:image/png;base64," . base64_encode("mock-qr-code-for-{$pixCode}");
    }
}
