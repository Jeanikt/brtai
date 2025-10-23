<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');

        $query = Supplier::query();

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $suppliers = $query->where('is_verified', true)
            ->orderBy('rating', 'desc')
            ->get();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'categories' => [
                'all' => 'Todos',
                'dj' => 'DJs',
                'beverage' => 'Bebidas',
                'food' => 'Comida',
                'decoration' => 'Decoração',
                'venue' => 'Locais'
            ],
            'selectedCategory' => $category
        ]);
    }

    public function show(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier
        ]);
    }

    public function getByCategory($category)
    {
        $suppliers = Supplier::where('category', $category)
            ->where('is_verified', true)
            ->orderBy('rating', 'desc')
            ->get();

        return response()->json($suppliers);
    }

    public function suggest(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'budget' => 'required|string|in:low,medium,high,premium',
            'location' => 'required|string'
        ]);

        // TODO: Implement AI-powered supplier suggestions
        $suggestions = Supplier::where('price_range', $request->budget)
            ->where('is_verified', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return response()->json([
            'suggestions' => $suggestions,
            'message' => 'Fornecedores sugeridos com base no seu evento!'
        ]);
    }
}
