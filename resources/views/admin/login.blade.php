<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration FAQ - Jadara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">🛠️</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Administration FAQ</h1>
            <p class="text-gray-600 mt-2">Accès réservé aux membres Jadara</p>
        </div>

        <!-- Erreurs -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="text-red-700 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Formulaire de connexion -->
        <form method="POST" action="{{ request()->url() }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">
                    🔐 Mot de passe administrateur
                </label>
                <input 
                    type="password" 
                    id="admin_password" 
                    name="admin_password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    placeholder="Entrez le mot de passe admin"
                    autofocus
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                🚀 Accéder à l'administration
            </button>
        </form>

        <!-- Info -->
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-500">
                Interface sécurisée pour la gestion des FAQ du chatbot Jadara
            </p>
            <div class="mt-4">
                <a href="/chat" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ← Retour au chatbot
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus sur le champ mot de passe
        document.getElementById('admin_password').focus();
        
        // Effet de particules en arrière-plan (optionnel)
        document.body.addEventListener('mousemove', function(e) {
            const particle = document.createElement('div');
            particle.style.position = 'fixed';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.backgroundColor = '#3b82f6';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.left = e.clientX + 'px';
            particle.style.top = e.clientY + 'px';
            particle.style.opacity = '0.7';
            particle.style.animation = 'fade 1s ease-out forwards';
            
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 1000);
        });
        
        // CSS pour l'animation des particules
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fade {
                0% { opacity: 0.7; transform: scale(1); }
                100% { opacity: 0; transform: scale(0.5); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
