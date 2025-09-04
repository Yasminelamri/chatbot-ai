<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Obtenir toutes les FAQ avec pagination
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);
        
        $faqs = FAQ::query()
            ->when($search, function ($query) use ($search) {
                $query->where('question', 'like', "%{$search}%")
                      ->orWhere('answer', 'like', "%{$search}%")
                      ->orWhere('tags', 'like', "%{$search}%")
                      ->orWhere('question_ar', 'like', "%{$search}%")
                      ->orWhere('answer_ar', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $stats = [
            'total' => FAQ::count(),
            'recent' => FAQ::where('created_at', '>=', now()->subDays(7))->count(),
            'updated' => FAQ::where('updated_at', '>=', now()->subDays(7))
                          ->where('updated_at', '>', FAQ::raw('created_at'))
                          ->count(),
        ];

        return response()->json([
            'faqs' => $faqs,
            'stats' => $stats,
            'success' => true
        ]);
    }

    /**
     * Créer une nouvelle FAQ
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|max:1000',
                'answer' => 'required|string|max:5000',
                'tags' => 'nullable|string|max:500',
                'question_ar' => 'nullable|string|max:1000',
                'answer_ar' => 'nullable|string|max:5000',
            ]);

            $faq = FAQ::create($validated);

            return response()->json([
                'faq' => $faq,
                'message' => 'Question ajoutée avec succès !',
                'success' => true
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
                'success' => false
            ], 422);
        }
    }

    /**
     * Obtenir une FAQ spécifique
     */
    public function show($id)
    {
        $faq = FAQ::find($id);
        
        if (!$faq) {
            return response()->json([
                'message' => 'FAQ non trouvée',
                'success' => false
            ], 404);
        }

        return response()->json([
            'faq' => $faq,
            'success' => true
        ]);
    }

    /**
     * Mettre à jour une FAQ
     */
    public function update(Request $request, $id)
    {
        try {
            $faq = FAQ::find($id);
            
            if (!$faq) {
                return response()->json([
                    'message' => 'FAQ non trouvée',
                    'success' => false
                ], 404);
            }

            $validated = $request->validate([
                'question' => 'required|string|max:1000',
                'answer' => 'required|string|max:5000',
                'tags' => 'nullable|string|max:500',
                'question_ar' => 'nullable|string|max:1000',
                'answer_ar' => 'nullable|string|max:5000',
            ]);

            $faq->update($validated);

            return response()->json([
                'faq' => $faq,
                'message' => 'Question modifiée avec succès !',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification : ' . $e->getMessage(),
                'success' => false
            ], 422);
        }
    }

    /**
     * Supprimer une FAQ
     */
    public function destroy($id)
    {
        try {
            $faq = FAQ::find($id);
            
            if (!$faq) {
                return response()->json([
                    'message' => 'FAQ non trouvée',
                    'success' => false
                ], 404);
            }

            $faq->delete();

            return response()->json([
                'message' => 'Question supprimée avec succès !',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Rechercher dans les FAQ
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'faqs' => [],
                'message' => 'Aucun terme de recherche fourni',
                'success' => false
            ], 400);
        }

        $faqs = FAQ::where('question', 'like', "%{$query}%")
                   ->orWhere('answer', 'like', "%{$query}%")
                   ->orderBy('created_at', 'desc')
                   ->limit(20)
                   ->get();

        return response()->json([
            'faqs' => $faqs,
            'count' => $faqs->count(),
            'success' => true
        ]);
    }
}