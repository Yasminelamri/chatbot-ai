Write-Host "🚀 Démarrage du développement Chatbot Jadara" -ForegroundColor Green
Write-Host ""

Write-Host "📦 Installation des dépendances..." -ForegroundColor Yellow
npm install

Write-Host ""
Write-Host "🔨 Compilation des assets..." -ForegroundColor Yellow
npm run build

Write-Host ""
Write-Host "🌐 Démarrage du serveur Laravel..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan serve --host 127.0.0.1 --port 9001"

Write-Host ""
Write-Host "📱 Démarrage de la surveillance des assets..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "npm run dev"

Write-Host ""
Write-Host "✅ Développement démarré !" -ForegroundColor Green
Write-Host "📍 Chatbot: http://127.0.0.1:9001/chat" -ForegroundColor Cyan
Write-Host "🧪 Test Tailwind: http://127.0.0.1:9001/test-tailwind" -ForegroundColor Cyan
Write-Host ""
Write-Host "Appuyez sur une touche pour fermer cette fenêtre..." -ForegroundColor Yellow
Read-Host
