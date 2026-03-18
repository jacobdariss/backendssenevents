# 🌍 SEN-EVENTS — Guide de contribution & workflow Git

## Structure des branches

| Branche    | Rôle                              | Déployée sur          |
|------------|-----------------------------------|-----------------------|
| `main`     | Production — **stable uniquement**| plateforme.senevents.africa |
| `staging`  | Pré-production / tests            | staging.senevents.africa   |
| `develop`  | Développement actif               | Local / dev            |
| `feature/xxx` | Nouvelle fonctionnalité        | Local uniquement       |
| `hotfix/xxx`  | Correction urgente en prod     | → merge dans main      |

---

## ✅ Workflow standard (nouvelle fonctionnalité)

```bash
# 1. Partir toujours de develop
git checkout develop
git pull origin develop

# 2. Créer une branche feature
git checkout -b feature/nom-de-la-fonctionnalite

# 3. Coder, commiter régulièrement
git add .
git commit -m "feat: description claire de ce qui a été fait"

# 4. Pusher la branche
git push origin feature/nom-de-la-fonctionnalite

# 5. Ouvrir une Pull Request : feature → develop (sur GitHub)
# 6. Après validation : merge develop → staging (tests)
# 7. Après validation staging : Pull Request staging → main
```

---

## 🚨 Correction urgente en production (hotfix)

```bash
# 1. Partir de main
git checkout main
git pull origin main

# 2. Créer une branche hotfix
git checkout -b hotfix/description-du-bug

# 3. Corriger, commiter
git add .
git commit -m "fix: description du bug corrigé"

# 4. Merger dans main ET dans develop
git checkout main
git merge hotfix/description-du-bug
git push origin main

git checkout develop
git merge hotfix/description-du-bug
git push origin develop

# 5. Supprimer la branche hotfix
git branch -d hotfix/description-du-bug
```

---

## 🖥️ Déploiement sur le serveur Plesk (Production)

```bash
# Se connecter en SSH, puis :
cd /var/www/vhosts/senevents.africa/plateforme.senevents.africa

# TOUJOURS vérifier avant de toucher quoi que ce soit
git branch
git status

# Déployer avec le script sécurisé
bash deploy.sh
```

### ❌ Commandes INTERDITES sur le serveur de production

```bash
git reset --hard   # ❌ INTERDIT
git rebase         # ❌ INTERDIT
git checkout       # ❌ INTERDIT (sauf vérification)
git push           # ❌ INTERDIT (le serveur ne push pas)
```

---

## 📝 Convention de commits

```
feat:     nouvelle fonctionnalité
fix:      correction de bug
hotfix:   correction urgente en prod
chore:    maintenance, dépendances
docs:     documentation
refactor: refactoring sans changement fonctionnel
```

