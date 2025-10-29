<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/Index', [
            'profile' => $profile,
            'user_plan' => $profile->plan_type
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20'
        ]);

        $profile->update($request->only(['full_name', 'phone']));

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePlan(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'plan_type' => 'required|in:freemium,pro'
        ]);

        $profile->update(['plan_type' => $request->plan_type]);

        return redirect()->back()->with('success', 'Plano atualizado com sucesso!');
    }

    public function billing(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/Billing', [
            'user_plan' => $profile->plan_type
        ]);
    }

    public function upgradeToPro(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $profile->update(['plan_type' => 'pro']);

        return redirect()->route('settings.billing')
            ->with('success', 'Upgrade para Pro realizado com sucesso!');
    }
}
