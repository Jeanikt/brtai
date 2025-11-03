<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoadmapController extends Controller
{
    public function index()
    {
        $roadmap = RoadmapItem::with('user')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Roadmap/Index', [
            'title' => 'Roadmap',
            'roadmap' => $roadmap,
            'isAdmin' => Auth::check() && Auth::user()->role === 'admin',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        RoadmapItem::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'suggested',
            'likes_count' => 0,
            'dislikes_count' => 0,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('roadmap.index')->with('success', 'Sugestão adicionada com sucesso!');
    }

    public function updateStatus(Request $request, RoadmapItem $roadmapItem)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            // Se for Inertia, abort faz a página Inertia receber erro via status
            abort(403, 'Você não tem permissão para alterar o status.');
        }

        $validated = $request->validate([
            'status' => 'required|in:suggested,planned,in_progress,completed',
        ]);

        $roadmapItem->update(['status' => $validated['status']]);

        return back()->with('success', 'Status atualizado!');
    }

    public function like(Request $request, $id)
    {
        // Validação do ID
        if (!Str::isUuid($id)) {
            if ($request->header('X-Inertia')) {
                // Para requisições Inertia: redireciona com errors (Inertia transformará)
                return back()->withErrors(['like' => 'ID inválido.']);
            }
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $item = RoadmapItem::find($id);
        if (!$item) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['like' => 'Item não encontrado.']);
            }
            return response()->json(['error' => 'Item não encontrado'], 404);
        }

        // Incrementa contador
        $item->increment('likes_count');

        // Se foi uma requisição Inertia, redirecionamos de volta para a rota index com flash
        if ($request->header('X-Inertia')) {
            return redirect()->route('roadmap.index')->with('success', '❤️ Obrigado por curtir!');
        }

        // Fallback JSON (compat)
        return response()->json([
            'success' => true,
            'likes_count' => $item->likes_count,
            'message' => '❤️ Obrigado por curtir!'
        ]);
    }
}
