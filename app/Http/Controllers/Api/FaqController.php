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
            // Vérifier si le fichier FAQ existe
            if (!file_exists(storage_path('app/faq.json'))) {
                return response()->json([], 200);
            }

            // Lire le contenu du fichier JSON
            $faqContent = file_get_contents(storage_path('app/faq.json'));
            
            // Décoder le JSON
            $faqData = json_decode($faqContent, true);
            
            // Vérifier si le JSON est valide
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Format JSON invalide',
                    'message' => 'Le fichier FAQ contient un JSON malformé'
                ], 500);
            }

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
            // Vérifier si le fichier FAQ existe
            if (!file_exists(storage_path('app/faq.json'))) {
                return response()->json([
                    'error' => 'FAQ non trouvée',
                    'message' => 'Aucune FAQ disponible'
                ], 404);
            }

            // Lire le contenu du fichier JSON
            $faqContent = file_get_contents(storage_path('app/faq.json'));
            $faqData = json_decode($faqContent, true);
            
            // Vérifier si le JSON est valide
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Format JSON invalide',
                    'message' => 'Le fichier FAQ contient un JSON malformé'
                ], 500);
            }

            // Vérifier si l'index existe
            if (!isset($faqData[$id])) {
                return response()->json([
                    'error' => 'Question non trouvée',
                    'message' => 'Aucune question trouvée avec cet identifiant'
                ], 404);
            }

            // Retourner la question spécifique
            return response()->json($faqData[$id], 200);

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

            // Vérifier si le fichier FAQ existe
            if (!file_exists(storage_path('app/faq.json'))) {
                return response()->json([], 200);
            }

            // Lire le contenu du fichier JSON
            $faqContent = file_get_contents(storage_path('app/faq.json'));
            $faqData = json_decode($faqContent, true);
            
            // Vérifier si le JSON est valide
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Format JSON invalide',
                    'message' => 'Le fichier FAQ contient un JSON malformé'
                ], 500);
            }

            // Rechercher dans les questions et réponses
            $results = [];
            $queryLower = mb_strtolower($query);
            
            foreach ($faqData as $index => $item) {
                $questionLower = mb_strtolower($item['question']);
                $responseLower = mb_strtolower($item['response']);
                
                if (str_contains($questionLower, $queryLower) || str_contains($responseLower, $queryLower)) {
                    $results[] = array_merge($item, ['id' => $index]);
                }
            }

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
