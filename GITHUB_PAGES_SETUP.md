# 🚀 Configuration GitHub Pages - Guide Complet

## ❌ **Problème actuel**
Le déploiement automatique a échoué car **GitHub Pages n'est pas encore activé** sur votre repository.

## ✅ **Solution : Activer GitHub Pages**

### **Étape 1 : Aller dans les paramètres**
1. Allez sur votre repository : https://github.com/Yasminelamri/chatbot-ai
2. Cliquez sur l'onglet **"Settings"** (Paramètres)
3. Descendez jusqu'à la section **"Pages"** dans le menu de gauche

### **Étape 2 : Configurer GitHub Pages**
1. **Source** : Sélectionnez **"GitHub Actions"**
2. **Branch** : Laissez sur **"master"** (ou "main" si c'est votre branche par défaut)
3. Cliquez sur **"Save"** (Enregistrer)

### **Étape 3 : Vérifier les permissions**
1. Toujours dans **Settings**
2. Allez dans **"Actions"** → **"General"**
3. Sous **"Workflow permissions"**, sélectionnez :
   - ✅ **"Read and write permissions"**
   - ✅ **"Allow GitHub Actions to create and approve pull requests"**
4. Cliquez sur **"Save"**

## 🔄 **Après activation**

Une fois GitHub Pages activé, le workflow se relancera automatiquement et votre démo sera disponible à :

**🔗 https://yasminelamri.github.io/chatbot-ai/**

## 📱 **URLs de démonstration**

Une fois déployé, vous aurez accès à :

- **Chatbot principal** : https://yasminelamri.github.io/chatbot-ai/simple-chat.html
- **Interface FAQ** : https://yasminelamri.github.io/chatbot-ai/faq-management.html

## 🎯 **Alternative : Démonstration locale**

En attendant le déploiement, votre démonstration fonctionne parfaitement en local :

```bash
cd C:\Users\MSI GAMMING I5\Documents\chatbot-app
cd public
php -S localhost:3000
```

Puis accédez à :
- http://localhost:3000/simple-chat.html
- http://localhost:3000/faq-management.html

## 🚨 **Si le problème persiste**

1. **Vérifiez que GitHub Pages est activé** (étapes ci-dessus)
2. **Attendez 5-10 minutes** pour la propagation
3. **Relancez le workflow** manuellement :
   - Allez dans l'onglet **"Actions"**
   - Cliquez sur **"Deploy Demo to GitHub Pages"**
   - Cliquez sur **"Run workflow"**

## 📞 **Support**

Si vous avez des difficultés :
1. Vérifiez l'onglet **"Actions"** pour voir les logs d'erreur détaillés
2. Assurez-vous que votre repository est **public** (GitHub Pages gratuit nécessite un repo public)

---

**🎊 Une fois configuré, votre chatbot sera accessible publiquement et vous pourrez le partager avec votre superviseur !**
