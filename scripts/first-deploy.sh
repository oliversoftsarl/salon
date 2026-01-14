#!/bin/bash

# ============================================
# Script de premier déploiement
# À exécuter en tant qu'utilisateur deploy
# ============================================

set -e

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

print_header() {
    echo -e "\n${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Vérifier que l'utilisateur est deploy
if [ "$USER" != "deploy" ]; then
    print_error "Ce script doit être exécuté en tant qu'utilisateur deploy"
    echo "Utilisez: su - deploy"
    exit 1
fi

print_header "🚀 Premier déploiement de Salon Gobel"

# Variables
BASE_PATH="/home/deploy/salon-gobel"
RELEASE_NAME="initial_$(date +'%Y%m%d_%H%M%S')"
RELEASE_PATH="${BASE_PATH}/releases/${RELEASE_NAME}"
SHARED_PATH="${BASE_PATH}/shared"
CURRENT_PATH="${BASE_PATH}/current"

read -p "URL du repository GitHub (ex: https://github.com/user/repo.git): " REPO_URL

print_header "📥 Clonage du repository"
git clone "${REPO_URL}" "${RELEASE_PATH}"
print_success "Repository cloné"

print_header "🔗 Liaison des fichiers partagés"
# Lier .env
ln -sfn "${SHARED_PATH}/.env" "${RELEASE_PATH}/.env"
print_success ".env lié"

# Lier storage
rm -rf "${RELEASE_PATH}/storage"
ln -sfn "${SHARED_PATH}/storage" "${RELEASE_PATH}/storage"
print_success "storage lié"

print_header "📦 Installation des dépendances Composer"
cd "${RELEASE_PATH}"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
print_success "Dépendances Composer installées"

print_header "📦 Installation des dépendances NPM"
npm ci
print_success "Dépendances NPM installées"

print_header "🔨 Build des assets"
npm run build
print_success "Assets compilés"

print_header "🗄️ Migrations de base de données"
php artisan migrate --force
print_success "Migrations exécutées"

print_header "⚡ Optimisations Laravel"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
print_success "Cache généré"

# Storage link
php artisan storage:link --force 2>/dev/null || true
print_success "Storage link créé"

print_header "🔄 Activation de la release"
ln -sfn "${RELEASE_PATH}" "${CURRENT_PATH}"
print_success "Release activée"

print_header "🔐 Correction des permissions"
sudo chown -R deploy:www-data "${BASE_PATH}"
sudo chmod -R 775 "${SHARED_PATH}/storage"
print_success "Permissions corrigées"

print_header "🔄 Rechargement des services"
sudo /bin/systemctl reload php8.2-fpm
print_success "PHP-FPM rechargé"

print_header "✅ Déploiement terminé!"
echo ""
echo -e "Release: ${GREEN}${RELEASE_NAME}${NC}"
echo -e "Path: ${GREEN}${RELEASE_PATH}${NC}"
echo ""
echo "Vérifiez que le site fonctionne sur https://etcgobel.com"
echo ""
echo "Pour voir les logs en cas de problème:"
echo "  tail -f ${SHARED_PATH}/storage/logs/laravel.log"

