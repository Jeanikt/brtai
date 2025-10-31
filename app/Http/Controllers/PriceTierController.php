<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PriceTier;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PriceTierController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'max_quantity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $priceTier = PriceTier::create([
            'event_id' => $event->id,
            'name' => $request->name,
            'price' => $request->price,
            'max_quantity' => $request->max_quantity,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->back()->with('success', 'Lote de preço criado com sucesso!');
    }

    public function update(Request $request, PriceTier $priceTier)
    {
        $this->authorize('update', $priceTier->event);

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'max_quantity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $priceTier->update($request->all());

        return redirect()->back()->with('success', 'Lote de preço atualizado com sucesso!');
    }

    public function destroy(PriceTier $priceTier)
    {
        $this->authorize('update', $priceTier->event);

        if ($priceTier->participants()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'Não é possível excluir um lote que já possui participantes.'
            ]);
        }

        $priceTier->delete();

        return redirect()->back()->with('success', 'Lote de preço excluído com sucesso!');
    }

    public function toggle(PriceTier $priceTier)
    {
        $this->authorize('update', $priceTier->event);

        $priceTier->update([
            'is_active' => !$priceTier->is_active
        ]);

        $status = $priceTier->is_active ? 'ativado' : 'desativado';
        return redirect()->back()->with('success', "Lote {$status} com sucesso!");
    }
}
