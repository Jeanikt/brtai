<?php

use Illuminate\Support\Facades\Route;
use App\Services\DiscordMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::post('/session/location', function (Request $request) {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Usuário não autenticado'], 401);
    }

    DiscordMonitorService::sessionEvent(
        'login',
        $user,
        $request->ip(),
        $request->header('User-Agent'),
        php_uname('s'),
        'manual',
        [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]
    );

    return response()->json(['message' => 'Localização registrada com sucesso']);
});
