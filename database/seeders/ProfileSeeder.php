<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use Illuminate\Support\Str;

class ProfileSeeder extends Seeder
{
    public function run()
    {
       
        $profiles = [
            [
                'id' => Str::uuid()->toString(),
                'full_name' => 'Organizador Teste',
                'phone' => '(11) 99999-9999',
                'plan_type' => 'freemium',
                'email_verified_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'full_name' => 'Organizador Pro',
                'phone' => '(11) 98888-8888',
                'plan_type' => 'pro',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($profiles as $profile) {
            Profile::create($profile);
        }

        $this->command->info('Perfis de teste criados com sucesso!');
    }
}
