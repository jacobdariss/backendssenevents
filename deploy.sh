#!/bin/bash
# ============================================
# SEN-EVENTS - Script de déploiement sécurisé
# Usage: bash deploy.sh
# ============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}🚀 SEN-EVENTS - Déploiement en cours...${NC}"

# 1. Vérifier qu'on est sur la bonne branche
BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$BRANCH" != "main" ]; then
  echo -e "${RED}❌ ERREUR: Tu n'es pas sur main (branche actuelle: $BRANCH)${NC}"
  echo -e "${RED}   Lance: git checkout main${NC}"
  exit 1
fi

# 2. Vérifier qu'il n'y a pas de modifications non commitées
if [ -n "$(git status --porcelain)" ]; then
  echo -e "${RED}❌ ERREUR: Des fichiers ont été modifiés localement sur le serveur !${NC}"
  git status --short
  echo -e "${YELLOW}   Annule les modifs: git checkout -- .${NC}"
  exit 1
fi

# 3. Pull sécurisé
echo -e "${YELLOW}📥 Pull depuis origin/main...${NC}"
git pull origin main

# 4. Installer les dépendances si composer.json a changé
if git diff HEAD@{1} --name-only | grep -q "composer.json\|composer.lock"; then
  echo -e "${YELLOW}📦 Mise à jour des dépendances Composer...${NC}"
  composer install --no-dev --optimize-autoloader
fi

# 5. Migrations Laravel
echo -e "${YELLOW}🗄️  Migrations base de données...${NC}"
php artisan migrate --force

# 6. Vider les caches
echo -e "${YELLOW}🔄 Vidage des caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Permissions storage
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}✅ Déploiement réussi ! Branche: $BRANCH${NC}"
echo -e "${GREEN}   Commit: $(git log --oneline -1)${NC}"
