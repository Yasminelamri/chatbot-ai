<?php

return [
    [
        'intent' => 'inscription',
        'examples' => [
            "comment m'inscrire", "comment m inscrire", "je veux créer un compte",
            "comment rejoindre jadara", "inscription gratuite", "c'est gratuit ou pas",
            "gratuit ou pas", "inscription", "ouvrir un compte"
        ],
        'answer' => "👍 Pour vous inscrire, cliquez sur « S'inscrire » en haut à droite et remplissez le formulaire. L'inscription est gratuite. Si besoin, utilisez « Mot de passe oublié » pour récupérer l'accès. 😊",
    ],
    [
        'intent' => 'projets',
        'examples' => [
            'quels sont les projets', 'projets disponibles', 'projets actifs',
            'je veux voir les projets', 'participer projet', 'postuler projet', 'comment participer à un projet'
        ],
        'answer' => "🚀 Allez dans l'onglet « Projets » pour voir les opportunités en cours. Ouvrez la fiche d'un projet pour les détails (dates, lieu, objectifs) puis cliquez sur « Participer ». Bonne chance !",
    ],
    [
        'intent' => 'formations',
        'examples' => ['formations', 'formations en ligne', 'certificat', 'ateliers', 'séminaires'],
        'answer' => "🎓 Vous pouvez trouver les prochaines formations dans la section « Formations ». Certaines sont gratuites et donnent même droit à un certificat !",
    ],
    [
        'intent' => 'aide_financiere',
        'examples' => ['aide financière', 'bourse', 'soutien financier', 'demander une aide'],
        'answer' => "💡 Pour demander une aide financière, ouvrez la section « Aide financière », vérifiez les critères puis déposez votre dossier. Vous pourrez suivre l'avancement depuis votre espace.",
    ],
    [
        'intent' => 'support',
        'examples' => ['contact', 'support', 'assistance', 'aide', 'je veux parler à un responsable'],
        'answer' => "📩 Besoin d'aide ? Utilisez le formulaire « Assistance » dans la plateforme. Selon votre compte, un email ou un numéro direct peut être proposé.",
    ],
    [
        'intent' => 'bonjour',
        'examples' => ['bonjour', 'salut', 'bjr', 'cc', 'slt', 'hello', 'hi'],
        'answer' => "👋 Bonjour ! Bienvenue sur Jadara Impact Social Cloud. Je suis votre assistant personnel et je suis là pour vous aider. Que souhaitez-vous savoir aujourd'hui ?",
    ],
    [
        'intent' => 'merci',
        'examples' => ['merci', 'thanks', 'thank you', 'merci beaucoup'],
        'answer' => "😊 De rien ! Je suis ravi d'avoir pu vous aider. N'hésitez pas si vous avez d'autres questions !",
    ],
    [
        'intent' => 'au_revoir',
        'examples' => ['au revoir', 'bye', 'goodbye', 'à bientôt', 'ciao'],
        'answer' => "👋 À bientôt ! N'hésitez pas à revenir si vous avez besoin d'aide. Bonne continuation !",
    ],
];


