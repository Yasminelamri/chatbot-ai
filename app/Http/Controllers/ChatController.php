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
            } else {
                // SYSTÈME HYBRIDE INTELLIGENT : FAQ → Claude → Mots-clés → Défaut
                
                // 1. D'ABORD : Chercher dans la FAQ (rapide et gratuit)
                $faqResult = $this->searchInFAQ($text);
                if ($faqResult) {
                    $botResponse = $faqResult;
                } elseif ($this->shouldUseClaudeAI($text)) {
                    // 2. CLAUDE AI : Pour questions complexes ou non trouvées
                    $botResponse = $this->getClaudeResponse($text);
                } elseif ($this->isGreeting($text)) {
                    // 3. Salutations classiques
                    if ($currentUser && $currentUser->role === 'jadara') {
                        $botResponse = "Bonjour {$currentUser->name} 👋 Que voulez-vous gérer aujourd'hui ? (FAQ, demandes, export)";
                    } elseif ($currentUser) {
                        $botResponse = "Bonjour {$currentUser->name} 👋 Comment puis-je vous aider pour votre bourse ?";
                    } else {
                        $botResponse = "Bonjour 👋 Que souhaitez-vous savoir aujourd'hui ? (ex: prolongation bourse, documents annuels, versement, changement RIB)";
                    }
                } else {
                    // 4. ENFIN : Système de mots-clés intelligent
                    $botResponse = $this->generateSmartResponse($text);
                }
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
        
        // 🔍 DÉTECTION INTELLIGENTE AVANCÉE
        
        // 1. PROLONGATION ET RENOUVELLEMENT
        if ($this->containsAny($normalizedText, ['prolongation', 'renouvellement', 'reconduction', 'prolongassion', 'renouveler', 'prolonger', 'continuer', 'maintenir'])) {
            if ($this->containsAny($normalizedText, ['bourse', 'bours', 'aide', 'financement']) || strlen($normalizedText) < 20) {
                return "📋 **Prolongation de bourse :**\n\n" .
                       "Chaque année, un mail explicatif vous est envoyé fin décembre. Il détaille les modalités et la procédure de prolongation de votre bourse.\n\n" .
                       "**Documents requis :**\n" .
                       "• Bulletin de notes de l'année précédente\n" .
                       "• Attestation de scolarité de l'année suivante\n" .
                       "• Résultats semestriels (en mars)\n\n" .
                       "Pour toute question, contactez : programmes@jadara.foundation";
            }
        }
        
        // 2. VERSEMENTS ET PAIEMENTS
        if ($this->containsAny($normalizedText, ['versement', 'versiment', 'paiement', 'quand', 'date', 'argent', 'reçu', 'reception', 'transfert', 'virement', 'compte bancaire', 'banque'])) {
            if ($this->containsAny($normalizedText, ['bourse', 'bours', 'aide', 'financement']) || strlen($normalizedText) < 20) {
                return "💰 **Versement des bourses :**\n\n" .
                       "Les bourses sont généralement versées soit à la fin du mois en cours, soit au début du mois suivant.\n\n" .
                       "**En cas de retard :**\n" .
                       "1. Vérifiez votre compte bancaire\n" .
                       "2. Assurez-vous d'avoir transmis tous les documents\n" .
                       "3. Contactez : programmes@jadara.foundation";
            }
        }
        
        // 3. DOCUMENTS ET JUSTIFICATIFS
        if ($this->containsAny($normalizedText, ['document', 'papier', 'justificatif', 'papiers', 'bulletin', 'attestation', 'scolarité', 'notes', 'resultats', 'semestre', 'année', 'certificat', 'diplome'])) {
            return "📄 **Documents requis annuellement :**\n\n" .
                   "• Bulletin de notes de l'année précédente\n" .
                   "• Attestation de scolarité de l'année suivante\n" .
                   "• Résultats semestriels (en mars)\n\n" .
                   "La reconduction de votre bourse dépend de la validation de votre année universitaire.";
        }
        
        // 4. CONTACT ET ASSISTANCE
        if ($this->containsAny($normalizedText, ['contact', 'contacter', 'aide', 'support', 'assistance', 'probleme', 'question', 'urgent', 'urgence', 'telephone', 'mail', 'email', 'adresse', 'bureau', 'siege', 'tunis'])) {
            return "📞 **Contact et support :**\n\n" .
                   "• **Email principal :** programmes@jadara.foundation\n" .
                   "• **Pour les urgences :** Contactez directement cette adresse\n" .
                   "• **Rendez-vous physique :** Possible sur demande par mail\n\n" .
                   "Notre équipe répond généralement sous 24-48h.";
        }
        
        // 5. CHANGEMENT DE RIB ET COMPTE BANCAIRE
        if ($this->containsAny($normalizedText, ['changer', 'modifier', 'mise a jour', 'rib', 'compte bancaire', 'banque', 'coordonnees bancaires', 'numero compte', 'iban', 'swift', 'code banque', 'guichet', 'compte', 'bancaire'])) {
            if ($this->containsAny($normalizedText, ['rib', 'compte bancaire', 'banque', 'coordonnees']) || strlen($normalizedText) < 15) {
                return "🏦 **Changement de RIB :**\n\n" .
                       "1. Mettez à jour votre RIB sur la plateforme Impact Social\n" .
                       "2. Déposez l'attestation correspondante\n" .
                       "3. Renvoyez le document par mail à programmes@jadara.foundation";
            }
        }
        
        // 6. BOURSES ET AIDE FINANCIÈRE (GÉNÉRAL)
        if ($this->containsAny($normalizedText, ['bourse', 'bours', 'aide financiere', 'financement', 'subvention', 'allocation', 'bénéfice', 'avantage', 'soutien', 'aide etudiant', 'etudiant', 'etudiante'])) {
            return "💰 **Informations sur les bourses :**\n\n" .
                   "Je peux vous aider avec :\n\n" .
                   "• **Prolongation** : Procédure et documents requis\n" .
                   "• **Versements** : Dates et modalités de paiement\n" .
                   "• **Documents** : Liste des justificatifs annuels\n" .
                   "• **Changements** : RIB, établissement, etc.\n\n" .
                   "Que souhaitez-vous savoir précisément ?";
        }
        
        // 7. FORMATIONS ET DÉVELOPPEMENT
        if ($this->containsAny($normalizedText, ['formation', 'apprendre', 'etudier', 'cours', 'atelier', 'seminaire', 'stage', 'concours', 'certification', 'diplome', 'competence', 'developpement', 'apprentissage', 'education', 'pedagogie'])) {
            return "📚 **Formations et développement :**\n\n" .
                   "Nous proposons des formations gratuites en ligne et en présentiel.\n\n" .
                   "**Types de formations :**\n" .
                   "• Formations en ligne (gratuites)\n" .
                   "• Ateliers pratiques\n" .
                   "• Séminaires de développement\n" .
                   "• Certifications\n\n" .
                   "Consultez la section 'Formations' pour plus d'infos !";
        }
        
        // 8. PROJETS ET OPPORTUNITÉS
        if ($this->containsAny($normalizedText, ['projet', 'opportunite', 'chance', 'participer', 'collaborer', 'travailler', 'mission', 'tache', 'activite', 'engagement', 'benevolat', 'volontariat'])) {
            return "🚀 **Projets et opportunités :**\n\n" .
                   "Nous proposons de nombreux projets dans différents domaines :\n\n" .
                   "• **Innovation sociale** : Projets d'impact communautaire\n" .
                   "• **Entrepreneuriat** : Accompagnement de start-ups\n" .
                   "• **Culture digitale** : Projets technologiques\n" .
                   "• **Développement durable** : Initiatives écologiques\n\n" .
                   "Consultez l'onglet 'Projets' pour voir les opportunités actuelles !";
        }

        // 9. INSCRIPTION ET ADHÉSION
        if ($this->containsAny($normalizedText, ['inscription', 'adherer', 'rejoindre', 'devenir membre', 'creer compte', 's inscrire', 'inscrire', 'adhesion', 'membre', 'beneficiaire', 'candidature'])) {
            return "📝 **Inscription et adhésion :**\n\n" .
                   "Pour rejoindre la Fondation Jadara :\n\n" .
                   "1. **Visitez** notre site officiel\n" .
                   "2. **Remplissez** le formulaire d'inscription\n" .
                   "3. **Soumettez** les documents requis\n" .
                   "4. **Attendez** la validation par notre équipe\n\n" .
                   "L'inscription est **gratuite** et ouverte à tous !";
        }

        // 10. REDOUBLEMENT ET ÉCHEC
        if ($this->containsAny($normalizedText, ['redoubler', 'redoublement', 'echec', 'echouer', 'rater', 'perdre', 'annuler', 'supprimer', 'terminer', 'finir', 'arreter'])) {
            return "⚠️ **Règles importantes :**\n\n" .
                   "**Redoublement :** Strictement interdit et n'ouvre pas droit à la bourse.\n\n" .
                   "**En cas d'échec :**\n" .
                   "• Contactez immédiatement programmes@jadara.foundation\n" .
                   "• Expliquez votre situation\n" .
                   "• Demandez un accompagnement personnalisé\n\n" .
                   "Nous sommes là pour vous aider à réussir !";
        }

        // 11. CHANGEMENT D'ÉTABLISSEMENT
        if ($this->containsAny($normalizedText, ['changer ecole', 'changer universite', 'transfert', 'transferer', 'autre ecole', 'autre universite', 'nouvel etablissement', 'etablissement', 'institution', 'ecole', 'universite', 'faculte'])) {
            return "🏫 **Changement d'établissement :**\n\n" .
                   "**En principe :** Les changements ne sont pas autorisés.\n\n" .
                   "**Exceptions possibles :**\n" .
                   "• Validation de l'année en cours\n" .
                   "• Établissement d'accueil de qualité\n" .
                   "• Motivations justifiées\n\n" .
                   "**Procédure :** Demande exceptionnelle à étudier en fin d'année académique.";
        }

        // 12. VACANCES ET PÉRIODES SPÉCIALES
        if ($this->containsAny($normalizedText, ['vacances', 'ete', 'aout', 'juillet', 'noel', 'nouvel an', 'weekend', 'ferie', 'conges', 'pause', 'repos'])) {
            return "📅 **Vacances et périodes spéciales :**\n\n" .
                   "**Vacances d'été (juillet-août) :**\n" .
                   "• Bourse versée uniquement si stage justifié\n" .
                   "• Conformément au règlement intérieur\n\n" .
                   "**Autres périodes :** Bourse normale\n\n" .
                   "**Planifiez** vos stages à l'avance !";
        }

        // 13. QUESTIONS GÉNÉRALES SUR JADARA
        if ($this->containsAny($normalizedText, ['jadara', 'fondation', 'organisation', 'ong', 'association', 'qui etes vous', 'que faites vous', 'votre mission', 'objectif', 'but', 'vision'])) {
            return "🌍 **Fondation Jadara - Impact Social Cloud :**\n\n" .
                   "**Notre mission :** Accompagner les jeunes vers l'excellence académique et professionnelle.\n\n" .
                   "**Nos domaines d'action :**\n" .
                   "• Bourses d'études et accompagnement\n" .
                   "• Formations et développement de compétences\n" .
                   "• Projets d'innovation sociale\n" .
                   "• Entrepreneuriat et innovation\n\n" .
                   "**Notre vision :** Créer un impact social durable !";
        }

        // Réponse par défaut professionnelle et intelligente
        return "💡 **Je comprends que vous avez besoin d'aide.**\n\n" .
               "**Voici les sujets sur lesquels je peux vous accompagner :**\n\n" .
               "• 💰 **Bourses et versements** : Dates de paiement, prolongation, documents requis\n" .
               "• 🏦 **Gestion de compte** : Changement de RIB, mise à jour des informations\n" .
               "• 📋 **Procédures administratives** : Inscription, reconduction, transfert d'établissement\n" .
               "• 📞 **Support et assistance** : Contact urgent, rendez-vous, résolution de problèmes\n" .
               "• 📚 **Formations et développement** : Concours, stages, ateliers\n" .
               "• 🚀 **Projets et opportunités** : Innovation sociale, entrepreneuriat\n" .
               "• 📝 **Inscription et adhésion** : Rejoindre la fondation\n\n" .
               "**Pour une réponse plus précise, vous pouvez :**\n" .
               "• Utiliser des mots-clés comme 'bourse', 'versement', 'RIB', 'documents', 'formation', 'projet'\n" .
               "• Reformuler votre question de manière plus détaillée\n" .
               "• Consulter notre FAQ complète dans la section 'Aide'";
    }

    /**
     * Détermine si on doit utiliser Claude AI
     */
    private function shouldUseClaudeAI(string $text): bool
    {
        // Utilise Claude pour :
        // - Questions longues et complexes
        // - Questions sur l'avenir, l'innovation, l'IA
        // - Questions non couvertes par la FAQ
        
        $complexityIndicators = [
            'comment', 'pourquoi', 'expliquez', 'analysez', 'comparez',
            'avenir', 'futur', 'développement', 'innovation', 'intelligence',
            'ia', 'claude', 'technologie', 'amélioration', 'optimisation',
            'stratégie', 'vision', 'objectifs', 'recommandation'
        ];
        
        $normalizedText = $this->normalizeText($text);
        
        // Questions longues (plus de 50 caractères)
        if (strlen($text) > 50) {
            return true;
        }
        
        // Contient des indicateurs de complexité
        foreach ($complexityIndicators as $indicator) {
            if (str_contains($normalizedText, $indicator)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtient une réponse de Claude AI
     */
    private function getClaudeResponse(string $text): string
    {
        try {
            $claudeService = app(\App\Services\ClaudeService::class);
            
            if (!$claudeService->isAvailable()) {
                return $this->generateSmartResponse($text);
            }
            
            // Récupère le contexte FAQ pour Claude
            $faqContext = $this->getFAQContext();
            
            $response = $claudeService->generateResponse($text, [
                'faq_data' => $faqContext
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            // Fallback vers le système classique en cas d'erreur
            return $this->generateSmartResponse($text);
        }
    }

    /**
     * Récupère le contexte FAQ pour Claude
     */
    private function getFAQContext(): array
    {
        try {
            $faq = app(\App\Models\FAQ::class);
            return $faq::limit(10)->get(['question', 'answer'])->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Recherche dans la FAQ de la base de données
     */
    private function searchInFAQ(string $text): ?string
    {
        // Import du modèle FAQ
        $faq = app(\App\Models\FAQ::class);
        
        $normalizedText = $this->normalizeText($text);
        
        // Recherche exacte dans les questions
        $result = $faq::where('question', 'like', "%{$text}%")
                     ->orWhere('question', 'like', "%{$normalizedText}%")
                     ->first();
        
        if ($result) {
            return $result->answer;
        }
        
        // Recherche par mots-clés dans les questions et réponses
        $keywords = explode(' ', $normalizedText);
        $keywords = array_filter($keywords, fn($k) => strlen($k) > 2); // Mots de plus de 2 caractères
        
        if (count($keywords) > 0) {
            $query = $faq::query();
            
            foreach ($keywords as $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('question', 'like', "%{$keyword}%")
                      ->orWhere('answer', 'like', "%{$keyword}%");
                });
            }
            
            $result = $query->first();
            
            if ($result) {
                return $result->answer;
            }
        }
        
        return null;
    }

    private function normalizeText(string $s): string
    {
        // 1. Conversion en minuscules
        $s = mb_strtolower($s);
        
        // 2. Suppression des accents et caractères spéciaux
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        
        // 3. Suppression de la ponctuation mais garder les espaces
        $s = preg_replace('/[^\p{L}\p{Nd}\s]/u', ' ', $s) ?? $s;
        
        // 4. Normalisation des espaces (suppression des espaces multiples)
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        
        // 5. Suppression des espaces en début et fin
        $s = trim($s);
        
        // 6. Gestion des variations courantes
        $s = str_replace(['bonsoir', 'bon soir'], 'bonsoir', $s);
        $s = str_replace(['bonjour', 'bon jour'], 'bonjour', $s);
        $s = str_replace(['bonne journee', 'bonne journée'], 'bonne journee', $s);
        $s = str_replace(['bonne soiree', 'bonne soirée'], 'bonne soiree', $s);
        
        // 7. Gestion des fautes d'orthographe courantes
        $s = str_replace(['bours', 'bourse'], 'bourse', $s);
        $s = str_replace(['versiment', 'versement'], 'versement', $s);
        $s = str_replace(['prolongassion', 'prolongation'], 'prolongation', $s);
        $s = str_replace(['documant', 'document'], 'document', $s);
        
        return $s;
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

    /**
     * Détecte TOUTES les formes de salutations possibles
     */
    private function isGreeting(string $text): bool
    {
        $greetings = [
            // Salutations formelles
            'bonjour', 'bonsoir', 'bonne journée', 'bonne soirée', 'bonne nuit',
            'salut', 'coucou', 'hey', 'hi', 'hello',
            
            // Abréviations courantes
            'bjr', 'bsr', 'bj', 'bs', 'cc', 'slt', 'hey',
            
            // Salutations avec variations
            'bonjour à tous', 'bonjour tout le monde', 'salut tout le monde',
            'bonjour madame', 'bonjour monsieur', 'bonsoir madame', 'bonsoir monsieur',
            
            // Salutations informelles
            'yo', 'wesh', 'salam', 'marhaba', 'ahlan',
            
            // Salutations temporelles
            'bon matin', 'bon après-midi', 'bonne fin de journée',
            
            // Salutations avec émojis (normalisées)
            '👋', '🙋', '💫', '✨', '🌟'
        ];
        
        $normalizedText = $this->normalizeText($text);
        
        // Vérification exacte
        foreach ($greetings as $greeting) {
            if ($normalizedText === $greeting) {
                return true;
            }
        }
        
        // Vérification partielle (pour "bonjour comment allez-vous" par exemple)
        foreach ($greetings as $greeting) {
            if (str_contains($normalizedText, $greeting)) {
                return true;
            }
        }
        
        // Vérification des variations avec espaces
        if (preg_match('/^(bon\s*(jour|soir|matin|apres.midi)|salut|hey|hi|hello|cc|slt|bjr|bsr)$/i', $normalizedText)) {
            return true;
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

