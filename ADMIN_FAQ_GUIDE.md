# 🛠️ Guide d'utilisation - Interface d'Administration FAQ

## 🚀 Accès à l'interface

### 1. Depuis le Chatbot
- Cliquez sur le bouton **"🛠️ Admin FAQ"** dans la barre latérale du chat
- Vous serez redirigé vers la page de connexion

### 2. Accès direct
- URL: `http://localhost:3000/admin/faq`
- Mot de passe par défaut: `jadara2024`

## 🔐 Authentification

### Connexion
1. Entrez le mot de passe administrateur
2. Cliquez sur "🚀 Accéder à l'administration"
3. Votre session restera active jusqu'à fermeture du navigateur

### Sécurité
- Le mot de passe est configuré dans le fichier `.env` (`ADMIN_PASSWORD`)
- La session est sécurisée côté serveur
- Accès restreint aux membres autorisés de Jadara

## 📋 Fonctionnalités

### ➕ Ajouter une FAQ
1. Cliquez sur **"Ajouter une FAQ"**
2. Remplissez la question et la réponse
3. Cliquez sur **"✅ Ajouter"**
4. La FAQ est immédiatement disponible dans le chatbot

### ✏️ Modifier une FAQ
1. Cliquez sur l'icône **"✏️"** à côté de la FAQ
2. Modifiez la question ou la réponse
3. Cliquez sur **"✅ Sauvegarder"**
4. Les modifications sont appliquées instantanément

### 🗑️ Supprimer une FAQ
1. Cliquez sur l'icône **"🗑️"** à côté de la FAQ
2. Confirmez la suppression
3. La FAQ est définitivement supprimée

### 🔍 Rechercher
- Utilisez la barre de recherche en haut
- La recherche fonctionne sur les questions ET les réponses
- Résultats filtrés en temps réel

## 🎨 Interface

### Dashboard
- **Compteur total** des FAQ
- **Statistiques** en temps réel
- **Design responsive** (mobile/desktop)
- **Mode sombre** automatique

### Notifications
- ✅ Confirmations d'actions réussies
- ❌ Messages d'erreur explicites
- ⏱️ Disparition automatique après 3 secondes

## 🔧 Configuration

### Modifier le mot de passe admin
1. Éditez le fichier `.env`
2. Changez la valeur de `ADMIN_PASSWORD`
3. Redémarrez le serveur Laravel

### Personnalisation
- Les styles sont dans `resources/js/Pages/Admin/FaqAdmin.jsx`
- Les couleurs suivent la charte graphique Jadara
- Interface entièrement personnalisable

## 🚀 Utilisation avancée

### Gestion en lot
- Utilisez la recherche pour filtrer
- Modifiez plusieurs FAQ similaires
- Organisez vos FAQ par thématiques

### Bonnes pratiques
1. **Questions claires** : Utilisez des termes que les utilisateurs emploient
2. **Réponses complètes** : Incluez toutes les informations nécessaires
3. **Mise à jour régulière** : Actualisez selon les retours utilisateurs
4. **Test immédiat** : Vérifiez dans le chatbot après chaque modification

## 🆘 Support

### Problèmes courants
- **Mot de passe incorrect** : Vérifiez la configuration dans `.env`
- **Session expirée** : Reconnectez-vous avec le mot de passe
- **FAQ non visible** : Vérifiez la synchronisation avec la base de données

### Contact
- Interface développée pour Jadara Impact Social Cloud
- Support technique disponible pour les membres Jadara

---

**🎯 Interface prête pour une utilisation en production !**
