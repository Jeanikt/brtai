<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function handle()
    {
        try {
            switch ($this->notification->channel) {
                case 'whatsapp':
                    $this->sendWhatsAppNotification();
                    break;
                case 'email':
                    $this->sendEmailNotification();
                    break;
                case 'push':
                    $this->sendPushNotification();
                    break;
                case 'sms':
                    $this->sendSmsNotification();
                    break;
            }

            $this->notification->markAsSent();

            Log::info('Notification sent successfully', [
                'notification_id' => $this->notification->id,
                'channel' => $this->notification->channel
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage()
            ]);

            $this->notification->update(['status' => 'failed']);
        }
    }

    private function sendWhatsAppNotification()
    {
        // TODO: Integrate with WhatsApp Business API
        $phone = $this->getRecipientPhone();

        if (!$phone) {
            throw new \Exception('No phone number available for WhatsApp notification');
        }

        // Mock implementation - replace with actual WhatsApp API
        $response = Http::post('https://api.whatsapp.com/v1/messages', [
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $this->notification->message
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('WhatsApp API error: ' . $response->body());
        }
    }

    private function sendEmailNotification()
    {
        // TODO: Implement email sending logic
        // Use Laravel Mail with a nice template
    }

    private function sendPushNotification()
    {
        // TODO: Implement push notification logic
        // Use Laravel Notifications or OneSignal
    }

    private function sendSmsNotification()
    {
        // TODO: Implement SMS sending logic
        // Use Twilio or similar service
    }

    private function getRecipientPhone()
    {
        if ($this->notification->participant_id) {
            return $this->notification->participant->phone;
        }

        if ($this->notification->user_id) {
            return $this->notification->user->phone;
        }

        return null;
    }
}
