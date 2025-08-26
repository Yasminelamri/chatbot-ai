@echo off
echo 🚀 Démarrage du développement Chatbot Jadara
echo.

echo 📦 Installation des dépendances...
call npm install

echo.
echo 🔨 Compilation des assets...
call npm run build

echo.
echo 🌐 Démarrage du serveur Laravel...
start "Laravel Server" cmd /k "php artisan serve --host 127.0.0.1 --port 9001"

echo.
echo 📱 Démarrage de la surveillance des assets...
start "Asset Watcher" cmd /k "npm run dev"

echo.
echo ✅ Développement démarré !
echo 📍 Chatbot: http://127.0.0.1:9001/chat
echo 🧪 Test Tailwind: http://127.0.0.1:9001/test-tailwind
echo.
echo Appuyez sur une touche pour fermer cette fenêtre...
pause > nul
