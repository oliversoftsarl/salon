#!/bin/bash

# ============================================
# Script de déploiement automatique (webhook)
# Exécuté automatiquement par le webhook GitHub
# ============================================

set -e

# Configuration
VPS_PATH="/home/deploy/salon-gobel"
REPO_URL="https://github.com/oliversoftsarl/salon.git"
LOG_FILE="${VPS_PATH}/shared/storage/logs/deploy.log"

# Fonction de log
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "============================================"
log "🚀 Démarrage du déploiement automatique"

# Générer le nom de release
RELEASE_NAME=$(date +'%Y%m%d_%H%M%S')
RELEASE_PATH="${VPS_PATH}/releases/${RELEASE_NAME}"

log "📦 Release: ${RELEASE_NAME}"

# Cloner le repository
log "📥 Clonage du repository..."
git clone --depth 1 --branch main "$REPO_URL" "$RELEASE_PATH" >> "$LOG_FILE" 2>&1

# Aller dans le dossier
cd "$RELEASE_PATH"

# Lier les fichiers partagés
log "🔗 Liaison des fichiers partagés..."
ln -sfn "${VPS_PATH}/shared/.env" "${RELEASE_PATH}/.env"
rm -rf "${RELEASE_PATH}/storage"
ln -sfn "${VPS_PATH}/shared/storage" "${RELEASE_PATH}/storage"

# Installer les dépendances Composer
log "📦 Installation des dépendances Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader >> "$LOG_FILE" 2>&1

# Installer les dépendances NPM et build
log "📦 Installation NPM et build des assets..."
npm ci >> "$LOG_FILE" 2>&1
npm run build >> "$LOG_FILE" 2>&1

# Migrations
log "🗄️ Exécution des migrations..."
php artisan migrate --force >> "$LOG_FILE" 2>&1

# Optimisations Laravel
log "⚡ Optimisations Laravel..."
php artisan config:cache >> "$LOG_FILE" 2>&1
php artisan route:cache >> "$LOG_FILE" 2>&1
php artisan view:cache >> "$LOG_FILE" 2>&1
php artisan event:cache >> "$LOG_FILE" 2>&1
php artisan storage:link --force >> "$LOG_FILE" 2>&1 || true

# Publier les assets Livewire
php artisan livewire:publish --assets >> "$LOG_FILE" 2>&1 || true

# Activer la nouvelle release
log "🔄 Activation de la nouvelle release..."
ln -sfn "$RELEASE_PATH" "${VPS_PATH}/current"

# Recharger PHP-FPM
log "🔄 Rechargement de PHP-FPM..."
sudo /bin/systemctl reload php8.2-fpm

# Nettoyer les anciennes releases (garder les 5 dernières)
log "🧹 Nettoyage des anciennes releases..."
cd "${VPS_PATH}/releases"
ls -t | tail -n +6 | xargs -r rm -rf

log "✅ Déploiement terminé avec succès!"
log "============================================"

