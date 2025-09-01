# 🚀 Installation rapide - Chatbot Jadara

## ⚡ **Installation en 2 minutes pour votre maître de stage**

### **Prérequis :**
- PHP 8.3+ installé
- Composer installé  
- Node.js 18+ installé

### **Commandes d'installation :**

```bash
# 1. Cloner le repository
git clone https://github.com/VOTRE-USERNAME/chatbot-jadara.git
cd chatbot-jadara

# 2. Installation des dépendances (automatique)
composer install --no-dev --optimize-autoloader
npm install --production

# 3. Configuration (automatique)
cp .env.example .env
php artisan key:generate

# 4. Base de données (pré-configurée)
php artisan migrate --seed

# 5. Compilation des assets
npm run production

# 6. LANCEMENT - Choisissez une option :

## Option A : Démo simple (recommandée)
cd public && php -S localhost:3000
# Puis ouvrez : http://localhost:3000/simple-chat.html

## Option B : Version complète Laravel
php artisan serve
# Puis ouvrez : http://localhost:8000/chat
```

### **🎯 Accès direct à la démo :**

1. **Chatbot simple** (sans authentification) : 
   - URL : `http://localhost:3000/simple-chat.html`
   - Tapez : "Bonjour", "prolongation bourse", "versement"

2. **Version complète** (avec authentification) :
   - URL : `http://localhost:3000/chat` 
   - Login : `team@example.test`
   - Password : `password`

### **🛠️ En cas de problème :**

```bash
# Si erreur de permissions
chmod -R 775 storage bootstrap/cache

# Si problème de base de données
php artisan migrate:fresh --seed

# Si problème d'assets
npm run dev
```

### **📱 Test rapide :**

Ouvrez le chatbot et testez ces questions :
- "Comment prolonger ma bourse ?"
- "Quand vais-je recevoir mon versement ?"
- "Quels documents dois-je envoyer ?"

**✅ Installation terminée ! Le chatbot Jadara est opérationnel.**
