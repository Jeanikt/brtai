<?php

namespace App\Console\Commands;

use App\Services\WooviService;
use Illuminate\Console\Command;

class TestWooviCharge extends Command
{
    protected $signature = 'test:woovi-charge';
    protected $description = 'Test Woovi charge creation';

    public function handle(WooviService $wooviService)
    {
        $chargeData = [
            'correlation_id' => 'test_' . time(),
            'value' => 100, // R$ 1,00
            'comment' => 'Test charge',
            'additional_info' => [
                [
                    'key' => 'test',
                    'value' => 'true',
                ],
            ],
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '5511999999999',
                'taxID' => '12345678909',
            ]
        ];

        $this->info('Creating test charge...');
        $result = $wooviService->createCharge($chargeData);

        if ($result['success']) {
            $this->info('✅ Charge created successfully!');
            $charge = $result['data']['charge'];

            $this->info("Correlation ID: " . $charge['correlationID']);
            $this->info("BR Code: " . ($charge['brCode'] ?? 'NOT FOUND'));
            $this->info("QR Code Image: " . ($charge['qrCodeImage'] ?? 'NOT FOUND'));
            $this->info("Payment Link: " . ($charge['paymentLinkUrl'] ?? 'NOT FOUND'));
            $this->info("Status: " . $charge['status']);

            if (isset($charge['brCode'])) {
                $this->info("\n📋 PIX Code para copiar:");
                $this->line($charge['brCode']);
            }
        } else {
            $this->error('❌ Failed to create charge: ' . $result['error']);
        }
    }
}
