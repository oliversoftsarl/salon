#!/bin/bash

# ============================================
# Script de déploiement manuel
# À exécuter depuis votre machine locale
# Usage: ./scripts/deploy.sh
# ============================================

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "\n${BLUE}============================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Configuration - MODIFIEZ CES VALEURS
VPS_HOST="102.223.210.91"
VPS_USER="deploy"
VPS_PATH="/home/deploy/salon-gobel"
SSH_KEY="~/.ssh/deploy_etsgobel"  # Chemin vers votre clé SSH

# Vérifier que la clé SSH existe
if [ ! -f "${SSH_KEY/#\~/$HOME}" ]; then
    print_error "Clé SSH non trouvée: $SSH_KEY"
    echo "Créez une clé avec: ssh-keygen -t ed25519 -f $SSH_KEY"
    exit 1
fi

print_header "🚀 Déploiement Salon Gobel"

# Générer le nom de release
RELEASE_NAME=$(date +'%Y%m%d_%H%M%S')_$(git rev-parse --short HEAD)
echo "Release: $RELEASE_NAME"

print_header "📦 Installation des dépendances"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
print_success "Composer installé"

npm ci
print_success "NPM installé"

print_header "🔨 Build des assets"
npm run build
print_success "Assets compilés"

print_header "📤 Envoi des fichiers vers le serveur"

# Créer le dossier de release sur le serveur
ssh -i "$SSH_KEY" ${VPS_USER}@${VPS_HOST} "mkdir -p ${VPS_PATH}/releases/${RELEASE_NAME}"

# Synchroniser les fichiers
rsync -avz --delete \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='storage' \
    --exclude='DEPLOYMENT.md' \
    --exclude='scripts/' \
    -e "ssh -i $SSH_KEY" \
    ./ ${VPS_USER}@${VPS_HOST}:${VPS_PATH}/releases/${RELEASE_NAME}/

print_success "Fichiers synchronisés"

print_header "🔗 Activation de la release"

ssh -i "$SSH_KEY" ${VPS_USER}@${VPS_HOST} << ENDSSH
    set -e

    RELEASE_PATH="${VPS_PATH}/releases/${RELEASE_NAME}"
    SHARED_PATH="${VPS_PATH}/shared"
    CURRENT_PATH="${VPS_PATH}/current"

    echo "🔗 Liaison des fichiers partagés..."
    ln -sfn \${SHARED_PATH}/.env \${RELEASE_PATH}/.env
    rm -rf \${RELEASE_PATH}/storage
    ln -sfn \${SHARED_PATH}/storage \${RELEASE_PATH}/storage

    echo "📦 Optimisations Laravel..."
    cd \${RELEASE_PATH}
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan storage:link --force 2>/dev/null || true

    # Publier les assets Livewire
    php artisan livewire:publish --assets 2>/dev/null || true

    echo "🔄 Activation de la nouvelle release..."
    ln -sfn \${RELEASE_PATH} \${CURRENT_PATH}

    echo "🧹 Nettoyage des anciennes releases (garde les 5 dernières)..."
    cd ${VPS_PATH}/releases
    ls -t | tail -n +6 | xargs -r rm -rf

    echo "✅ Déploiement terminé!"
ENDSSH

print_success "Release activée"

print_header "🔄 Redémarrage des services"

ssh -i "$SSH_KEY" ${VPS_USER}@${VPS_HOST} "sudo /bin/systemctl reload php8.2-fpm"

print_success "PHP-FPM rechargé"

print_header "✅ Déploiement terminé avec succès!"
echo ""
echo -e "Release: ${GREEN}${RELEASE_NAME}${NC}"
echo -e "URL: ${GREEN}https://etsgobel.com${NC}"
echo ""

