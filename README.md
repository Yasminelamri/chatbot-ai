# 🤖 Chatbot Intelligent - Fondation Jadara

Un chatbot intelligent et moderne développé pour la Fondation Jadara, spécialisé dans l'assistance aux bénéficiaires de bourses et la gestion des procédures administratives.

## ✨ **Fonctionnalités**

### 💬 **Chat Intelligent**
- **Détection de mots-clés** : Comprend les questions même avec des fautes d'orthographe
- **Réponses contextuelles** : Réponses précises et utiles selon le contexte
- **Support multilingue** : Interface en français avec support des accents
- **Gestion des conversations** : Historique, archivage, recherche

### 🎯 **Domaines d'Expertise**
- **Bourses et versements** : Prolongation, dates de paiement, documents requis
- **Procédures administratives** : Inscription, reconduction, transferts
- **Support et assistance** : Contact, rendez-vous, résolution de problèmes
- **Formations** : Ateliers, séminaires, certifications

### 🛠️ **Fonctionnalités Techniques**
- **Upload de médias** : Images et enregistrements audio
- **Transcription audio** : Conversion voix vers texte
- **Interface responsive** : Compatible PC, tablette et mobile
- **Mode sombre/clair** : Interface personnalisable

## 🚀 **Technologies Utilisées**

- **Backend** : Laravel 12 (PHP 8.3+)
- **Frontend** : React 18 + Inertia.js
- **CSS** : Tailwind CSS
- **Base de données** : SQLite (développement) / MySQL (production)
- **Build** : Laravel Mix (Webpack)

## 📋 **Prérequis**

- PHP 8.3 ou supérieur
- Composer 2.0+
- Node.js 18+ et npm
- Git

## 🛠️ **Installation**

### 1. **Cloner le repository**
```bash
git clone https://github.com/Yasminelamri/chatbot-ai.git
cd chatbot-ai
```

### 2. **Installer les dépendances PHP**
```bash
composer install
```

### 3. **Installer les dépendances Node.js**
```bash
npm install
```

### 4. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

### 5. **Base de données**
```bash
php artisan migrate
php artisan db:seed
```

### 6. **Compilation des assets**
```bash
npm run build
```

### 7. **Lancer le serveur**
```bash
php artisan serve
```

## 🎮 **Utilisation**

### **Démarrage rapide**
```bash
# Terminal 1 : Serveur Laravel
php artisan serve --port=8000

# Terminal 2 : Compilation des assets (développement)
npm run dev
```

### **Accès**
- **URL** : `http://localhost:8000`
- **Route principale** : `/chat`

## 🔧 **Configuration**

### **Variables d'environnement importantes**
```env
APP_NAME="Jadara Chatbot"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### **Personnalisation des réponses**
Les réponses du chatbot sont configurables dans le fichier `storage/faq.csv` :
```csv
question,answer
"Comment demander une prolongation ?","Réponse détaillée..."
"À quelle date la bourse est-elle versée ?","Réponse détaillée..."
```

## 📱 **Interface Utilisateur**

### **Fonctionnalités principales**
- **Chat en temps réel** avec historique des conversations
- **Gestion des conversations** : création, archivage, suppression
- **Upload de médias** : images et enregistrements audio
- **Mode sombre/clair** pour le confort visuel
- **Interface responsive** pour tous les appareils

### **Navigation**
- **Nouvelle conversation** : Bouton ➕ pour démarrer un nouveau chat
- **Historique** : Liste des conversations précédentes
- **Archives** : Conversations archivées
- **Recherche** : Filtrage des conversations

## 🧪 **Tests**

```bash
# Tests unitaires
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📊 **Statistiques**

Le chatbot fournit des statistiques détaillées :
- Nombre total de conversations
- Messages échangés
- Conversations archivées
- Export des données (CSV)

## 🤝 **Contribution**

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 **License**

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 **Support**

- **Email** : programmes@jadara.foundation
- **Issues** : [GitHub Issues](https://github.com/Yasminelamri/chatbot-ai/issues)
- **Documentation** : Consultez la section Wiki

## 🙏 **Remerciements**

- **Fondation Jadara** pour la confiance accordée
- **Laravel** pour le framework backend robuste
- **React** pour l'interface utilisateur moderne
- **Tailwind CSS** pour le design system

---

**Développé avec ❤️ pour la Fondation Jadara**
