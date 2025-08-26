<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        // Vérifie si une conversation existe déjà en session
        $conversationId = session('conversation_id');

        // Si pas d'ID ou si l'utilisateur a demandé une nouvelle conversation
        if (!$conversationId || $request->input('new_conversation')) {
            // Crée une nouvelle conversation
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                // autres champs...
            ]);
            session(['conversation_id' => $conversation->id]);
        } else {
            // Récupère la conversation existante
            $conversation = Conversation::find($conversationId);
        }

        // Ajoute le message à la conversation
        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'content' => $request->input('message'),
            // autres champs...
        ]);

        // ...traitement de la réponse...

        return response()->json([
            'reply' => $message->content,
            // autres données...
        ]);
    }
}