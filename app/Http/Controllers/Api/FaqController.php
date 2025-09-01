<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FaqController extends Controller
{
    /**
     * Récupère la FAQ depuis le fichier JSON
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Récupérer la FAQ depuis la base de données
            $faqData = \App\Models\FAQ::all(['id', 'question', 'answer']);
            
            // Retourner la FAQ
            return response()->json($faqData, 200);

        } catch (\Exception $e) {
            // Log de l'erreur pour le débogage
            \Log::error('Erreur lors de la lecture de la FAQ: ' . $e->getMessage());
            
            // Retourner une erreur 500 avec un message générique
            return response()->json([
                'error' => 'Erreur interne du serveur',
                'message' => 'Impossible de récupérer la FAQ pour le moment'
            ], 500);
        }
    }

    /**
     * Récupère une question spécifique par son index
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Récupérer la question depuis la base de données
            $faqItem = \App\Models\FAQ::find($id);
            
            if (!$faqItem) {
                return response()->json([
                    'error' => 'Question non trouvée',
                    'message' => 'Aucune question trouvée avec cet identifiant'
                ], 404);
            }

            // Retourner la question spécifique
            return response()->json($faqItem, 200);

        } catch (\Exception $e) {
            // Log de l'erreur pour le débogage
            \Log::error('Erreur lors de la lecture de la question FAQ: ' . $e->getMessage());
            
            // Retourner une erreur 500 avec un message générique
            return response()->json([
                'error' => 'Erreur interne du serveur',
                'message' => 'Impossible de récupérer la question pour le moment'
            ], 500);
        }
    }

    /**
     * Recherche dans la FAQ par mot-clé
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'error' => 'Requête vide',
                    'message' => 'Veuillez fournir un terme de recherche'
                ], 400);
            }

            // Rechercher dans la base de données
            $results = \App\Models\FAQ::where('question', 'like', "%{$query}%")
                ->orWhere('answer', 'like', "%{$query}%")
                ->get(['id', 'question', 'answer']);

            // Retourner les résultats de recherche
            return response()->json($results, 200);

        } catch (\Exception $e) {
            // Log de l'erreur pour le débogage
            \Log::error('Erreur lors de la recherche dans la FAQ: ' . $e->getMessage());
            
            // Retourner une erreur 500 avec un message générique
            return response()->json([
                'error' => 'Erreur interne du serveur',
                'message' => 'Impossible de rechercher dans la FAQ pour le moment'
            ], 500);
        }
    }
}
