<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Inertia.js - Chatbot Jadara</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <div class="min-h-screen bg-gray-100 flex items-center justify-center">
            <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-center text-gray-900 mb-4">
                    🧪 Test Inertia.js
                </h1>
                
                <div class="text-center">
                    <p class="text-gray-600 mb-4">
                        Si vous voyez cette page, Inertia.js fonctionne !
                    </p>
                    
                    <div class="space-y-2">
                        <a href="/chat" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            🚀 Aller au Chatbot
                        </a>
                        
                        <a href="/test-tailwind" class="block w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                            🎨 Test Tailwind CSS
                        </a>
                        
                        <a href="/" class="block w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                            🏠 Page d'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
