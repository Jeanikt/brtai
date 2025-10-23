<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/Index', [
            'profile' => $profile,
            'plan' => [
                'type' => $profile->plan_type,
                'event_limit' => $profile->getEventLimit(),
                'participant_limit' => $profile->getParticipantLimit(),
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['full_name', 'phone']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($profile->avatar_url) {
                Storage::disk('supabase')->delete($profile->avatar_url);
            }

            $path = $request->file('avatar')->store('avatars', 'supabase');
            $data['avatar_url'] = $path;
        }

        $profile->update($data);

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePlan(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'plan_type' => 'required|in:free,pro',
        ]);

        // TODO: Implement payment processing for plan upgrades
        // For now, we'll just update the plan type
        $profile->update(['plan_type' => $request->plan_type]);

        return redirect()->back()->with('success', 'Plano atualizado com sucesso!');
    }

    public function billing(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Get recent transactions
        $transactions = \App\Models\PaymentTransaction::whereHas('participant.event', function ($query) use ($profile) {
            $query->where('organizer_id', $profile->id);
        })
            ->with(['participant', 'event'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m');
            });

        return Inertia::render('Settings/Billing', [
            'transactions' => $transactions,
            'plan' => $profile->plan_type,
        ]);
    }

    public function upgradeToPro(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // TODO: Implement payment processing with AbacatePay
        // For MVP, we'll just update the plan
        $profile->update(['plan_type' => 'pro']);

        return redirect()->route('settings.index')
            ->with('success', 'Upgrade para Pro realizado com sucesso!');
    }
}
