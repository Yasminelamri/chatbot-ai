<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Tailwind CSS</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-4xl font-bold text-center text-brand-500 mb-8">
                🎉 Test Tailwind CSS
            </h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Classes de base</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-500 text-white p-4 rounded-lg text-center">
                        bg-blue-500
                    </div>
                    <div class="bg-green-500 text-white p-4 rounded-lg text-center">
                        bg-green-500
                    </div>
                    <div class="bg-red-500 text-white p-4 rounded-lg text-center">
                        bg-red-500
                    </div>
                    <div class="bg-yellow-500 text-white p-4 rounded-lg text-center">
                        bg-yellow-500
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Classes personnalisées</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-brand-50 text-brand-700 p-4 rounded-lg text-center border border-brand-200">
                        bg-brand-50
                    </div>
                    <div class="bg-brand-100 text-brand-700 p-4 rounded-lg text-center border border-brand-200">
                        bg-brand-100
                    </div>
                    <div class="bg-brand-500 text-white p-4 rounded-lg text-center">
                        bg-brand-500
                    </div>
                    <div class="bg-brand-600 text-white p-4 rounded-lg text-center">
                        bg-brand-600
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Boutons</h2>
                <div class="flex flex-wrap gap-4">
                    <button class="bg-brand-600 hover:bg-brand-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Bouton principal
                    </button>
                    <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Bouton secondaire
                    </button>
                    <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Bouton succès
                    </button>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Formulaire</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="Votre nom">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent" placeholder="votre@email.com">
                    </div>
                    <button class="w-full bg-brand-600 hover:bg-brand-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Envoyer
                    </button>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Mode sombre</h2>
                <div class="p-4 bg-gray-800 text-white rounded-lg">
                    <p class="text-gray-200">Ceci simule le mode sombre</p>
                    <p class="text-gray-400">Avec des couleurs de texte différentes</p>
                </div>
            </div>
            
            <div class="text-center text-gray-600">
                <p>Si vous voyez ce design stylé, Tailwind CSS fonctionne parfaitement ! 🚀</p>
                <div class="mt-4 space-x-4">
                    <a href="/chat" class="text-brand-600 hover:underline">Retour au chatbot</a>
                    <a href="/" class="text-gray-500 hover:text-gray-700">Accueil</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
