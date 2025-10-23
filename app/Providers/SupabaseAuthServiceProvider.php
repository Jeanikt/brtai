<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Services\SupabaseAuthService;

class SupabaseAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
    
        Auth::viaRequest('supabase', function ($request) {
            return app(SupabaseAuthService::class)->getUserFromRequest($request);
        });
    }
}
