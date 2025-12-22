<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ResponseTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResponseTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ResponseTemplate::with('categories');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Order by usage count
        $orderDirection = $request->get('usage_order', 'desc');
        $query->orderBy('usage_count', $orderDirection);

        // Paginate with 52 items per page
        $responseTemplates = $query->paginate(52)->withQueryString();
        $categories = Category::all();
        $types = ResponseTemplate::distinct()->pluck('type')->filter()->values();

        return Inertia::render('Dashboard', [
            'responseTemplates' => $responseTemplates,
            'categories' => $categories,
            'types' => $types,
            'filters' => [
                'type' => $request->type,
                'category_id' => $request->category_id ? (int) $request->category_id : null,
                'usage_order' => $request->get('usage_order', 'desc'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string|max:255',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $responseTemplate = ResponseTemplate::create([
            'content' => $validated['content'],
            'type' => $validated['type'] ?? null,
        ]);

        if (isset($validated['category_ids'])) {
            $responseTemplate->categories()->sync($validated['category_ids']);
        }

        return redirect()->back()->with('success', 'Réponse type créée avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResponseTemplate $responseTemplate)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string|max:255',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $responseTemplate->update([
            'content' => $validated['content'],
            'type' => $validated['type'] ?? null,
        ]);

        if (isset($validated['category_ids'])) {
            $responseTemplate->categories()->sync($validated['category_ids']);
        } else {
            $responseTemplate->categories()->detach();
        }

        return redirect()->back()->with('success', 'Réponse type mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResponseTemplate $responseTemplate)
    {
        $responseTemplate->delete();

        return redirect()->back()->with('success', 'Réponse type supprimée avec succès.');
    }

    /**
     * Increment usage count and return content for copying.
     */
    public function copy(ResponseTemplate $responseTemplate)
    {
        $responseTemplate->increment('usage_count');
        $responseTemplate->refresh();

        return response()->json([
            'content' => $responseTemplate->content,
            'usage_count' => $responseTemplate->usage_count,
        ]);
    }
}

