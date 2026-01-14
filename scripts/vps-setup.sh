#!/bin/bash

# ============================================
# Script de configuration initiale du VPS
# Pour Salon Gobel - etcgobel.com
# ============================================

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Vérifier si le script est exécuté en tant que root
if [ "$EUID" -ne 0 ]; then
    print_error "Ce script doit être exécuté en tant que root"
    exit 1
fi

print_header "🚀 Configuration initiale du VPS pour Salon Gobel"

# Variables - À modifier selon vos besoins
read -p "Entrez l'IP du serveur (pour les logs): " SERVER_IP
read -p "Entrez le nom d'utilisateur GitHub (pour cloner): " GITHUB_USER
read -p "Entrez le nom du repository GitHub: " GITHUB_REPO
read -sp "Entrez le mot de passe MySQL pour salon_user: " DB_PASSWORD
echo ""

print_header "📦 Mise à jour du système"
apt update && apt upgrade -y
print_success "Système mis à jour"

print_header "📦 Installation des paquets nécessaires"
apt install -y \
    nginx \
    git \
    curl \
    unzip \
    ufw \
    fail2ban \
    mariadb-server \
    mariadb-client \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline \
    php8.2-dom
print_success "Paquets installés"

print_header "📦 Installation de Composer"
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_success "Composer installé"
else
    print_warning "Composer déjà installé"
fi

print_header "📦 Installation de Node.js 20"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
    print_success "Node.js installé: $(node --version)"
else
    print_warning "Node.js déjà installé: $(node --version)"
fi

print_header "👤 Création de l'utilisateur deploy"
if ! id "deploy" &>/dev/null; then
    adduser --disabled-password --gecos "" deploy
    usermod -aG www-data deploy
    print_success "Utilisateur deploy créé"
else
    print_warning "Utilisateur deploy existe déjà"
fi

# Configuration sudo pour deploy
echo "deploy ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx, /bin/systemctl reload php8.2-fpm, /usr/bin/php" > /etc/sudoers.d/deploy
chmod 440 /etc/sudoers.d/deploy
print_success "Permissions sudo configurées"

print_header "🗄️ Configuration de la base de données"
mysql -e "CREATE DATABASE IF NOT EXISTS salon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'salon_user'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON salon_db.* TO 'salon_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
print_success "Base de données configurée"

print_header "📁 Création de la structure des dossiers"
mkdir -p /home/deploy/salon-gobel/releases
mkdir -p /home/deploy/salon-gobel/shared/storage/app/public
mkdir -p /home/deploy/salon-gobel/shared/storage/framework/{cache,sessions,views}
mkdir -p /home/deploy/salon-gobel/shared/storage/logs

# Générer APP_KEY
APP_KEY="base64:$(openssl rand -base64 32)"

# Créer le fichier .env
cat > /home/deploy/salon-gobel/shared/.env << EOF
APP_NAME="Salon Gobel"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://etcgobel.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salon_db
DB_USERNAME=salon_user
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_FROM_ADDRESS="contact@etcgobel.com"
MAIL_FROM_NAME="Salon Gobel"
EOF

chown -R deploy:www-data /home/deploy/salon-gobel
chmod -R 775 /home/deploy/salon-gobel/shared/storage
print_success "Structure des dossiers créée"

print_header "🔑 Configuration SSH pour deploy"
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
touch /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh
print_success "SSH configuré pour deploy"
print_warning "N'oubliez pas d'ajouter votre clé publique dans /home/deploy/.ssh/authorized_keys"

print_header "🌐 Configuration Nginx"
cat > /etc/nginx/sites-available/etcgobel.com << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name etcgobel.com www.etcgobel.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name etcgobel.com www.etcgobel.com;

    # Certificats Cloudflare Origin - À configurer
    ssl_certificate /etc/ssl/cloudflare/etcgobel.com.pem;
    ssl_certificate_key /etc/ssl/cloudflare/etcgobel.com.key;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    root /home/deploy/salon-gobel/current/public;
    index index.php index.html;

    access_log /var/log/nginx/etcgobel.access.log;
    error_log /var/log/nginx/etcgobel.error.log;

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(\.env|composer\.json|composer\.lock|package\.json|webpack\.mix\.js) {
        deny all;
    }
}
EOF

# Créer le dossier pour les certificats
mkdir -p /etc/ssl/cloudflare
print_warning "N'oubliez pas d'ajouter les certificats Cloudflare dans /etc/ssl/cloudflare/"

# Activer le site (mais ne pas redémarrer nginx tant que les certificats ne sont pas là)
ln -sf /etc/nginx/sites-available/etcgobel.com /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
print_success "Configuration Nginx créée"

print_header "🔒 Configuration du Firewall (UFW)"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw --force enable
print_success "Firewall configuré"

print_header "🛡️ Configuration de Fail2Ban"
systemctl enable fail2ban
systemctl start fail2ban
print_success "Fail2Ban activé"

print_header "🔧 Configuration PHP"
cat > /etc/php/8.2/fpm/conf.d/99-custom.ini << 'EOF'
expose_php = Off
display_errors = Off
log_errors = On
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 60
memory_limit = 256M
EOF

mkdir -p /var/log/php
chown www-data:www-data /var/log/php
systemctl restart php8.2-fpm
print_success "PHP configuré"

print_header "📋 Résumé de la configuration"
echo ""
echo -e "${GREEN}✓ Système mis à jour${NC}"
echo -e "${GREEN}✓ PHP 8.2, Nginx, MariaDB installés${NC}"
echo -e "${GREEN}✓ Composer et Node.js installés${NC}"
echo -e "${GREEN}✓ Utilisateur deploy créé${NC}"
echo -e "${GREEN}✓ Base de données salon_db créée${NC}"
echo -e "${GREEN}✓ Structure des dossiers créée${NC}"
echo -e "${GREEN}✓ Firewall et Fail2Ban configurés${NC}"
echo ""

print_header "📝 Prochaines étapes"
echo "1. Ajoutez votre clé SSH publique dans:"
echo "   /home/deploy/.ssh/authorized_keys"
echo ""
echo "2. Ajoutez les certificats Cloudflare Origin dans:"
echo "   /etc/ssl/cloudflare/etcgobel.com.pem"
echo "   /etc/ssl/cloudflare/etcgobel.com.key"
echo ""
echo "3. Puis testez et activez Nginx:"
echo "   nginx -t && systemctl reload nginx"
echo ""
echo "4. Configurez les secrets GitHub Actions:"
echo "   - VPS_HOST: ${SERVER_IP}"
echo "   - VPS_USER: deploy"
echo "   - VPS_SSH_KEY: (votre clé privée SSH)"
echo "   - VPS_PATH: /home/deploy/salon-gobel"
echo ""
echo "5. Le fichier .env a été créé avec APP_KEY:"
echo "   ${APP_KEY}"
echo ""

print_success "Configuration initiale terminée!"

