<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema as DBSchema;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $conversationId = (int) $request->query('c', 0);
        if ($conversationId === 0) {
            $conversationId = (int) $request->session()->get('active_conversation_id', 0);
        }
        
        $messages = collect();
        if ($conversationId > 0) {
            $messages = Message::where('conversation_id', $conversationId)
                ->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();
        }
        
        $activeUserId = (int) $request->session()->get('active_user_id', 0);
        $query = Conversation::query()->orderBy('updated_at', 'desc');
        
        if ($activeUserId > 0 && DBSchema::hasColumn('conversations', 'user_id')) {
            $query->where('user_id', $activeUserId);
        }
        
        $conversations = $query->get(['id','title','updated_at','archived']);

        return Inertia::render('Chat/Index', [
            'messages' => $messages,
            'conversationId' => $conversationId,
            'conversations' => $conversations,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000|required_without_all:image,audio',
            'image' => 'nullable|image|max:2048',
            'audio' => 'nullable|mimetypes:audio/mpeg,audio/wav,audio/ogg,audio/webm|max:10240',
        ]);

        $imagePath = null;
        $audioPath = null;
        
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages/images', 'public');
        }
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('messages/audios', 'public');
        }

        $activeUserId = (int) $request->session()->get('active_user_id', 0);
        $currentUser = Auth::user();
        if (!$currentUser && $activeUserId > 0) {
            $currentUser = User::find($activeUserId);
        }

        $conversationId = (int) $request->query('c', 0);
        if ($conversationId === 0) {
            $conversationId = (int) $request->session()->get('active_conversation_id', 0);
        }
        if ($conversationId === 0) {
            $conv = Conversation::create([
                'title' => str($validated['message'] ?? 'Nouvelle conversation')->limit(60),
                'user_id' => $currentUser?->id,
            ]);
            $conversationId = $conv->id;
        }
        $request->session()->put('active_conversation_id', $conversationId);

        Message::create([
            'sender' => 'user',
            'content' => $validated['message'] ?? '',
            'image_path' => $imagePath,
            'audio_path' => $audioPath,
            'conversation_id' => $conversationId ?: null,
        ]);

        $botResponse = '';
        $text = trim((string) ($validated['message'] ?? ''));
        
        if ($audioPath) {
            $audioFullPath = storage_path('app/public/' . $audioPath);
            $text = trim($this->transcribeAudio($audioFullPath));
        }

        if ($text === '' && $imagePath) {
            $botResponse = "📷 Image reçue. Merci ! Dites-moi comment je peux vous aider en lien avec cette image.";
        } else {
            if ($text === '') {
                if ($currentUser && $currentUser->role === 'jadara') {
                    $botResponse = "👋 Bonjour {$currentUser->name}. Vous êtes connecté en tant que membre Jadara. Souhaitez-vous consulter les demandes en attente ou gérer la FAQ ?";
                } elseif ($currentUser) {
                    $botResponse = "👋 Bonjour {$currentUser->name}. Je peux vous aider pour votre bourse (prolongation, versement, documents).";
                } else {
                    $botResponse = "👋 Bienvenue sur Jadara ! Posez vos questions sur la bourse (prolongation, versement, documents, changement RIB).";
                }
            } elseif (preg_match('/^(bonjour|salut|bjr|cc|slt)$/i', $text)) {
                if ($currentUser && $currentUser->role === 'jadara') {
                    $botResponse = "Bonjour {$currentUser->name} 👋 Que voulez-vous gérer aujourd'hui ? (FAQ, demandes, export)";
                } elseif ($currentUser) {
                    $botResponse = "Bonjour {$currentUser->name} 👋 Comment puis-je vous aider pour votre bourse ?";
                } else {
                    $botResponse = "Bonjour 👋 Que souhaitez-vous savoir aujourd'hui ? (ex: prolongation bourse, documents annuels, versement, changement RIB)";
                }
            } else {
                // Utiliser le système de mots-clés intelligent
                $botResponse = $this->generateSmartResponse($text);
            }
        }

        Message::create([
            'sender' => 'bot',
            'content' => $botResponse,
            'conversation_id' => $conversationId ?: null,
        ]);

        Conversation::where('id', $conversationId)->update(['updated_at' => now()]);
        return redirect()->route('chat.index', ['c' => $conversationId]);
    }

    private function generateSmartResponse(string $text): string
    {
        $normalizedText = $this->normalizeText($text);
        
        // Détection intelligente par mots-clés et contexte
        if ($this->containsAny($normalizedText, ['prolongation', 'renouvellement', 'reconduction', 'prolongassion'])) {
            if ($this->containsAny($normalizedText, ['bourse', 'bours']) || strlen($normalizedText) < 15) {
                return "📋 **Prolongation de bourse :**\n\n" .
                       "Chaque année, un mail explicatif vous est envoyé fin décembre. Il détaille les modalités et la procédure de prolongation de votre bourse.\n\n" .
                       "**Documents requis :**\n" .
                       "• Bulletin de notes de l'année précédente\n" .
                       "• Attestation de scolarité de l'année suivante\n" .
                       "• Résultats semestriels (en mars)\n\n" .
                       "Pour toute question, contactez : programmes@jadara.foundation";
            }
        }
        
        if ($this->containsAny($normalizedText, ['versement', 'versiment', 'paiement', 'quand', 'date', 'argent'])) {
            if ($this->containsAny($normalizedText, ['bourse', 'bours']) || strlen($normalizedText) < 15) {
                return "💰 **Versement des bourses :**\n\n" .
                       "Les bourses sont généralement versées soit à la fin du mois en cours, soit au début du mois suivant.\n\n" .
                       "**En cas de retard :**\n" .
                       "1. Vérifiez votre compte bancaire\n" .
                       "2. Assurez-vous d'avoir transmis tous les documents\n" .
                       "3. Contactez : programmes@jadara.foundation";
            }
        }
        
        if ($this->containsAny($normalizedText, ['document', 'papier', 'justificatif', 'papiers'])) {
            return "📄 **Documents requis annuellement :**\n\n" .
                   "• Bulletin de notes de l'année précédente\n" .
                   "• Attestation de scolarité de l'année suivante\n" .
                   "• Résultats semestriels (en mars)\n\n" .
                   "La reconduction de votre bourse dépend de la validation de votre année universitaire.";
        }
        
        if ($this->containsAny($normalizedText, ['contact', 'contacter', 'aide', 'support', 'assistance', 'probleme', 'question'])) {
            return "📞 **Contact et support :**\n\n" .
                   "• **Email principal :** programmes@jadara.foundation\n" .
                   "• **Pour les urgences :** Contactez directement cette adresse\n" .
                   "• **Rendez-vous physique :** Possible sur demande par mail\n\n" .
                   "Notre équipe répond généralement sous 24-48h.";
        }
        
        if ($this->containsAny($normalizedText, ['changer', 'modifier', 'mise a jour', 'rib', 'compte bancaire'])) {
            if ($this->containsAny($normalizedText, ['rib', 'compte bancaire']) || strlen($normalizedText) < 10) {
                return "🏦 **Changement de RIB :**\n\n" .
                       "1. Mettez à jour votre RIB sur la plateforme Impact Social\n" .
                       "2. Déposez l'attestation correspondante\n" .
                       "3. Renvoyez le document par mail à programmes@jadara.foundation";
            }
        }
        
        if ($this->containsAny($normalizedText, ['bourse', 'bours'])) {
            return "💰 **Informations sur les bourses :**\n\n" .
                   "Je peux vous aider avec :\n\n" .
                   "• **Prolongation** : Procédure et documents requis\n" .
                   "• **Versements** : Dates et modalités de paiement\n" .
                   "• **Documents** : Liste des justificatifs annuels\n" .
                   "• **Changements** : RIB, établissement, etc.\n\n" .
                   "Que souhaitez-vous savoir précisément ?";
        }
        
        if ($this->containsAny($normalizedText, ['formation', 'apprendre', 'etudier', 'cours', 'atelier', 'seminaire'])) {
            return "📚 **Formations et développement :**\n\n" .
                   "Nous proposons des formations gratuites en ligne et en présentiel.\n\n" .
                   "**Types de formations :**\n" .
                   "• Formations en ligne (gratuites)\n" .
                   "• Ateliers pratiques\n" .
                   "• Séminaires de développement\n" .
                   "• Certifications\n\n" .
                   "Consultez la section 'Formations' pour plus d'infos !";
        }
        
        // Réponse par défaut professionnelle
        return "💡 **Je comprends que vous avez besoin d'aide.**\n\n" .
               "**Voici les sujets sur lesquels je peux vous accompagner :**\n\n" .
               "• 💰 **Bourses et versements** : Dates de paiement, prolongation, documents requis\n" .
               "• 🏦 **Gestion de compte** : Changement de RIB, mise à jour des informations\n" .
               "• 📋 **Procédures administratives** : Inscription, reconduction, transfert d'établissement\n" .
               "• 📞 **Support et assistance** : Contact urgent, rendez-vous, résolution de problèmes\n" .
               "• 📚 **Formations et développement** : Concours, stages, ateliers\n\n" .
               "**Pour une réponse plus précise, vous pouvez :**\n" .
               "• Utiliser des mots-clés comme 'bourse', 'versement', 'RIB', 'documents'\n" .
               "• Reformuler votre question de manière plus détaillée\n" .
               "• Consulter notre FAQ complète dans la section 'Aide'";
    }

    private function normalizeText(string $s): string
    {
        $s = mb_strtolower($s);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/[^\p{L}\p{Nd}\s]/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        return trim($s);
    }

    private function containsAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function new(Request $request)
    {
        $request->session()->forget('active_conversation_id');
        return redirect()->route('chat.index');
    }

    private function transcribeAudio(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }
        return 'Transcription audio (démo): question posée par voix.';
    }

    public function update(Request $request, Message $message)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        $message->update(['content' => $validated['content']]);
        return redirect()->route('chat.index');
    }

    public function destroy(Message $message)
    {
        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }
        if ($message->audio_path) {
            Storage::disk('public')->delete($message->audio_path);
        }
        if ($message->sender === 'user') {
            $next = Message::where('id', '>', $message->id)->orderBy('id')->first();
            if ($next && $next->sender === 'bot') {
                if ($next->image_path) {
                    Storage::disk('public')->delete($next->image_path);
                }
                if ($next->audio_path) {
                    Storage::disk('public')->delete($next->audio_path);
                }
                $next->delete();
            }
        }
        $message->delete();
        return redirect()->route('chat.index');
    }

    public function clear(Request $request)
    {
        Message::query()->orderBy('id')->chunk(200, function ($chunk) {
            foreach ($chunk as $m) {
                if ($m->image_path) {
                    Storage::disk('public')->delete($m->image_path);
                }
                if ($m->audio_path) {
                    Storage::disk('public')->delete($m->audio_path);
                }
            }
        });
        Message::query()->delete();
        return redirect()->route('chat.index');
    }

    public function updateConversation(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
        ]);
        $conversation->update(['title' => $validated['title']]);
        return redirect()->route('chat.index', ['c' => $conversation->id]);
    }

    public function stats(Request $request)
    {
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $archivedConversations = Conversation::where('archived', true)->count();

        return response()->json([
            'totalConversations' => $totalConversations,
            'archivedConversations' => $archivedConversations,
            'totalMessages' => $totalMessages,
        ]);
    }

    public function exportConversationsCsv(): StreamedResponse
    {
        $fileName = 'conversations.csv';
        $columns = ['id','title','archived','user_id','updated_at','created_at'];
        $callback = function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            Conversation::orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->title,
                        $row->archived ? 1 : 0,
                        $row->user_id,
                        $row->updated_at,
                        $row->created_at,
                    ]);
                }
            });
            fclose($handle);
        };
        return response()->streamDownload($callback, $fileName, ['Content-Type' => 'text/csv']);
    }
}

