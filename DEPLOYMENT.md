# 🚀 Guide de Déploiement - Salon Gobel

## Architecture de Déploiement

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   GitHub Repo   │────▶│  Webhook Push   │────▶│   VPS Debian    │
│   (main branch) │     │   (automatique) │     │   nginx + PHP   │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                                                        │
                                                        ▼
┌─────────────────┐                             ┌─────────────────┐
│   Cloudflare    │◀────────────────────────────│  etsgobel.com   │
│   (DNS + SSL)   │                             │   (Laravel App) │
└─────────────────┘                             └─────────────────┘
```

---

## 🔄 DÉPLOIEMENT AUTOMATIQUE (Webhook GitHub)

### Configuration rapide (5 minutes)

#### Étape 1 : Sur le VPS (en tant que root)

```bash
# 1. Copier le script de déploiement
cat > /home/deploy/salon-gobel/deploy-from-webhook.sh << 'EOF'
#!/bin/bash
set -e

VPS_PATH="/home/deploy/salon-gobel"
REPO_URL="https://github.com/oliversoftsarl/salon.git"
LOG_FILE="${VPS_PATH}/shared/storage/logs/deploy.log"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "🚀 Démarrage du déploiement automatique"

RELEASE_NAME=$(date +'%Y%m%d_%H%M%S')
RELEASE_PATH="${VPS_PATH}/releases/${RELEASE_NAME}"

log "📦 Release: ${RELEASE_NAME}"

git clone --depth 1 --branch main "$REPO_URL" "$RELEASE_PATH" >> "$LOG_FILE" 2>&1

cd "$RELEASE_PATH"

ln -sfn "${VPS_PATH}/shared/.env" "${RELEASE_PATH}/.env"
rm -rf "${RELEASE_PATH}/storage"
ln -sfn "${VPS_PATH}/shared/storage" "${RELEASE_PATH}/storage"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader >> "$LOG_FILE" 2>&1

npm ci >> "$LOG_FILE" 2>&1
npm run build >> "$LOG_FILE" 2>&1

php artisan migrate --force >> "$LOG_FILE" 2>&1
php artisan config:cache >> "$LOG_FILE" 2>&1
php artisan route:cache >> "$LOG_FILE" 2>&1
php artisan view:cache >> "$LOG_FILE" 2>&1
php artisan event:cache >> "$LOG_FILE" 2>&1
php artisan storage:link --force >> "$LOG_FILE" 2>&1 || true
php artisan livewire:publish --assets >> "$LOG_FILE" 2>&1 || true

ln -sfn "$RELEASE_PATH" "${VPS_PATH}/current"

sudo /bin/systemctl reload php8.2-fpm

cd "${VPS_PATH}/releases"
ls -t | tail -n +6 | xargs -r rm -rf

log "✅ Déploiement terminé avec succès!"
EOF

chmod +x /home/deploy/salon-gobel/deploy-from-webhook.sh
chown deploy:deploy /home/deploy/salon-gobel/deploy-from-webhook.sh

# 2. Générer un secret pour le webhook
WEBHOOK_SECRET=$(openssl rand -hex 32)
echo "DEPLOY_WEBHOOK_SECRET=${WEBHOOK_SECRET}" >> /home/deploy/salon-gobel/shared/.env
echo ""
echo "🔑 Votre secret webhook (à copier pour GitHub):"
echo "$WEBHOOK_SECRET"
echo ""
```

#### Étape 2 : Configurer le Webhook sur GitHub

1. Allez sur **https://github.com/oliversoftsarl/salon/settings/hooks**
2. Cliquez sur **Add webhook**
3. Configurez :
   - **Payload URL**: `https://etsgobel.com/deploy-webhook.php`
   - **Content type**: `application/json`
   - **Secret**: Le secret généré à l'étape 1
   - **Events**: Sélectionnez "Just the push event"
4. Cliquez sur **Add webhook**

#### Étape 3 : Tester

Faites un commit et push sur `main`. Le déploiement se fera automatiquement !

Voir les logs :
```bash
tail -f /home/deploy/salon-gobel/shared/storage/logs/deploy.log
```

---

## 📋 Prérequis

- VPS Debian avec accès root
- PHP 8.2 installé
- Nginx installé
- Domaine `etcgobel.com` géré par Cloudflare
- Compte GitHub avec le repository du projet

---

## 🖥️ PARTIE 1 : Configuration du VPS

### 1.1 Connexion au VPS

```bash
ssh root@VOTRE_IP_VPS
```

### 1.2 Mise à jour du système

```bash
apt update && apt upgrade -y
```

### 1.3 Installation des dépendances PHP requises

```bash
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-readline php8.2-dom
```

### 1.4 Installation de Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 1.5 Installation de Node.js (pour les assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

### 1.6 Installation de Git

```bash
apt install -y git
```

### 1.7 Installation de MySQL/MariaDB

```bash
apt install -y mariadb-server mariadb-client
mysql_secure_installation
```

### 1.8 Création de la base de données

```bash
mysql -u root -p
```

```sql
CREATE DATABASE salon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'salon_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_SECURISE';
GRANT ALL PRIVILEGES ON salon_db.* TO 'salon_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 👤 PARTIE 2 : Création de l'utilisateur de déploiement

### 2.1 Créer un utilisateur dédié

```bash
adduser deploy
usermod -aG www-data deploy
```

### 2.2 Configurer les permissions sudo (optionnel mais recommandé)

```bash
visudo
```

Ajouter à la fin :
```
deploy ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx, /bin/systemctl reload php8.2-fpm, /usr/bin/php
```

### 2.3 Configurer l'authentification SSH par clé

```bash
su - deploy
mkdir -p ~/.ssh
chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

---

## 📁 PARTIE 3 : Structure des dossiers

### 3.1 Créer la structure de déploiement

```bash
# En tant que deploy
su - deploy

mkdir -p /home/deploy/salon-gobel
mkdir -p /home/deploy/salon-gobel/releases
mkdir -p /home/deploy/salon-gobel/shared/storage/app/public
mkdir -p /home/deploy/salon-gobel/shared/storage/framework/cache
mkdir -p /home/deploy/salon-gobel/shared/storage/framework/sessions
mkdir -p /home/deploy/salon-gobel/shared/storage/framework/views
mkdir -p /home/deploy/salon-gobel/shared/storage/logs
```

### 3.2 Créer le fichier .env partagé

```bash
nano /home/deploy/salon-gobel/shared/.env
```

Contenu du fichier `.env` :
```env
APP_NAME="Salon Gobel"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://etcgobel.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salon_db
DB_USERNAME=salon_user
DB_PASSWORD=VOTRE_MOT_DE_PASSE_SECURISE

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@etcgobel.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3.3 Générer la clé d'application

```bash
cd /home/deploy/salon-gobel/shared
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copiez la clé générée et mettez-la dans le fichier `.env` pour `APP_KEY`.

### 3.4 Définir les permissions

```bash
sudo chown -R deploy:www-data /home/deploy/salon-gobel
sudo chmod -R 775 /home/deploy/salon-gobel/shared/storage
```

---

## 🌐 PARTIE 4 : Configuration Nginx

### 4.1 Créer la configuration du site

```bash
sudo nano /etc/nginx/sites-available/etcgobel.com
```

Contenu :
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name etcgobel.com www.etcgobel.com;

    root /home/deploy/salon-gobel/current/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/etcgobel.access.log;
    error_log /var/log/nginx/etcgobel.error.log;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Taille max upload
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

    # Cache des assets statiques
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Bloquer l'accès aux fichiers sensibles
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(\.env|composer\.json|composer\.lock|package\.json|webpack\.mix\.js) {
        deny all;
    }
}
```

### 4.2 Activer le site

```bash
sudo ln -s /etc/nginx/sites-available/etcgobel.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## ☁️ PARTIE 5 : Configuration Cloudflare

### 5.1 Configuration DNS

Dans le dashboard Cloudflare pour `etcgobel.com` :

1. Allez dans **DNS** > **Records**
2. Ajoutez les enregistrements suivants :

| Type | Name | Content | Proxy status |
|------|------|---------|--------------|
| A | @ | VOTRE_IP_VPS | Proxied (orange) |
| A | www | VOTRE_IP_VPS | Proxied (orange) |

### 5.2 Configuration SSL/TLS

1. Allez dans **SSL/TLS** > **Overview**
2. Sélectionnez **Full (strict)**

3. Allez dans **SSL/TLS** > **Edge Certificates**
4. Activez :
   - **Always Use HTTPS** : ON
   - **Automatic HTTPS Rewrites** : ON
   - **Minimum TLS Version** : TLS 1.2

### 5.3 Configuration de sécurité

1. Allez dans **Security** > **Settings**
2. Configurez :
   - **Security Level** : Medium
   - **Challenge Passage** : 30 minutes
   - **Browser Integrity Check** : ON

### 5.4 Certificat Origin (pour Full Strict)

1. Allez dans **SSL/TLS** > **Origin Server**
2. Cliquez sur **Create Certificate**
3. Laissez les valeurs par défaut et créez
4. Copiez le certificat et la clé privée

Sur le VPS :
```bash
sudo mkdir -p /etc/ssl/cloudflare
sudo nano /etc/ssl/cloudflare/etcgobel.com.pem
# Collez le certificat

sudo nano /etc/ssl/cloudflare/etcgobel.com.key
# Collez la clé privée

sudo chmod 600 /etc/ssl/cloudflare/etcgobel.com.key
```

### 5.5 Mettre à jour Nginx pour SSL

```bash
sudo nano /etc/nginx/sites-available/etcgobel.com
```

Remplacez le contenu par :
```nginx
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

    # Certificats Cloudflare Origin
    ssl_certificate /etc/ssl/cloudflare/etcgobel.com.pem;
    ssl_certificate_key /etc/ssl/cloudflare/etcgobel.com.key;

    # Configuration SSL
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;

    root /home/deploy/salon-gobel/current/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/etcgobel.access.log;
    error_log /var/log/nginx/etcgobel.error.log;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Taille max upload
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

    # Cache des assets statiques
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Bloquer l'accès aux fichiers sensibles
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ ^/(\.env|composer\.json|composer\.lock|package\.json|webpack\.mix\.js) {
        deny all;
    }
}
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔑 PARTIE 6 : Configuration GitHub Actions

### 6.1 Générer une clé SSH pour le déploiement

Sur votre machine locale :
```bash
ssh-keygen -t ed25519 -C "deploy@etcgobel.com" -f ~/.ssh/deploy_etcgobel
```

### 6.2 Ajouter la clé publique sur le VPS

```bash
# Sur le VPS, en tant que deploy
cat >> /home/deploy/.ssh/authorized_keys << 'EOF'
COLLEZ_ICI_LE_CONTENU_DE_deploy_etcgobel.pub
EOF
```

### 6.3 Configurer les secrets GitHub

Dans votre repository GitHub :
1. Allez dans **Settings** > **Secrets and variables** > **Actions**
2. Ajoutez les secrets suivants :

| Secret Name | Value |
|-------------|-------|
| `VPS_HOST` | Votre IP VPS |
| `VPS_USER` | deploy |
| `VPS_SSH_KEY` | Contenu de `~/.ssh/deploy_etcgobel` (clé privée) |
| `VPS_PATH` | /home/deploy/salon-gobel |

---

## 🔄 PARTIE 7 : Script de déploiement

Le workflow GitHub Actions est déjà créé dans `.github/workflows/deploy.yml`.

### Fonctionnement :
1. **Déclenché** : À chaque push/merge sur `main`
2. **Build** : Installe les dépendances, compile les assets
3. **Deploy** : Synchronise via rsync, exécute les migrations
4. **Rollback** : Garde les 5 dernières releases pour rollback si besoin

---

## 🚀 PARTIE 8 : Premier déploiement manuel

Avant d'activer le CI/CD, faites un premier déploiement manuel :

```bash
# Sur le VPS, en tant que deploy
cd /home/deploy/salon-gobel

# Cloner le repository
git clone https://github.com/VOTRE_USERNAME/salon.git releases/initial

# Créer le lien symbolique current
ln -sfn /home/deploy/salon-gobel/releases/initial /home/deploy/salon-gobel/current

# Lier le .env
ln -sfn /home/deploy/salon-gobel/shared/.env /home/deploy/salon-gobel/current/.env

# Lier le storage
rm -rf /home/deploy/salon-gobel/current/storage
ln -sfn /home/deploy/salon-gobel/shared/storage /home/deploy/salon-gobel/current/storage

# Installer les dépendances
cd /home/deploy/salon-gobel/current
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Migrations
php artisan migrate --force

# Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Permissions
sudo chown -R deploy:www-data /home/deploy/salon-gobel
sudo chmod -R 775 /home/deploy/salon-gobel/shared/storage
```

---

## 🛡️ PARTIE 9 : Sécurité additionnelle

### 9.1 Configuration du firewall (UFW)

```bash
sudo apt install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 9.2 Fail2Ban pour protéger SSH

```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 9.3 Désactiver l'accès root SSH

```bash
sudo nano /etc/ssh/sshd_config
```

Modifier :
```
PermitRootLogin no
PasswordAuthentication no
```

```bash
sudo systemctl restart sshd
```

### 9.4 Configuration PHP sécurisée

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Modifier :
```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 60
memory_limit = 256M
```

```bash
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php
sudo systemctl restart php8.2-fpm
```

---

## 📊 PARTIE 10 : Monitoring et Logs

### 10.1 Voir les logs Laravel

```bash
tail -f /home/deploy/salon-gobel/shared/storage/logs/laravel.log
```

### 10.2 Voir les logs Nginx

```bash
sudo tail -f /var/log/nginx/etcgobel.error.log
```

### 10.3 Voir les logs PHP

```bash
sudo tail -f /var/log/php/error.log
```

---

## 🔙 PARTIE 11 : Rollback en cas de problème

Si un déploiement pose problème :

```bash
# Lister les releases disponibles
ls -la /home/deploy/salon-gobel/releases/

# Rollback vers une release précédente
ln -sfn /home/deploy/salon-gobel/releases/RELEASE_PRECEDENTE /home/deploy/salon-gobel/current

# Recharger
sudo systemctl reload php8.2-fpm
```

---

## ✅ Checklist de vérification

- [ ] VPS accessible via SSH
- [ ] PHP 8.2 et extensions installés
- [ ] Nginx configuré et running
- [ ] Base de données créée
- [ ] Fichier .env configuré avec APP_KEY
- [ ] DNS Cloudflare configuré
- [ ] SSL Full (strict) activé
- [ ] Certificat Origin installé
- [ ] Clé SSH de déploiement configurée
- [ ] Secrets GitHub configurés
- [ ] Premier déploiement manuel réussi
- [ ] Firewall UFW activé
- [ ] Site accessible sur https://etcgobel.com

---

## 📞 Support

En cas de problème :
1. Vérifiez les logs (Laravel, Nginx, PHP)
2. Vérifiez les permissions des fichiers
3. Vérifiez la connexion à la base de données
4. Vérifiez la configuration Cloudflare

---

## 🔧 PARTIE 12 : Résolution des problèmes courants

### 12.1 Erreur "could not find driver" (SQLite)

**Symptôme :**
```
could not find driver (Connection: sqlite, SQL: select exists...)
```

**Cause :** Le fichier `.env` n'est pas correctement lié ou n'existe pas.

**Solution :**

```bash
# 1. Vérifier que le fichier .env partagé existe
cat /home/deploy/salon-gobel/shared/.env

# 2. S'il n'existe pas, le créer
nano /home/deploy/salon-gobel/shared/.env
```

Contenu du `.env` :
```env
APP_NAME="Salon Gobel"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://etcgobel.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salon_db
DB_USERNAME=salon_user
DB_PASSWORD=VOTRE_MOT_DE_PASSE

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

```bash
# 3. Générer la clé si nécessaire
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"

# 4. Vérifier/créer le lien symbolique .env
ls -la /home/deploy/salon-gobel/current/.env

# Si le lien n'existe pas ou est cassé :
ln -sfn /home/deploy/salon-gobel/shared/.env /home/deploy/salon-gobel/current/.env

# 5. Vider le cache de configuration
cd /home/deploy/salon-gobel/current
php artisan config:clear

# 6. Vérifier la connexion à la base de données
php artisan db:show

# 7. Relancer les migrations
php artisan migrate --force
```

### 12.2 Erreur de permissions sur storage

**Symptôme :**
```
The stream or file "/home/deploy/.../storage/logs/laravel.log" could not be opened
```

**Solution :**
```bash
sudo chown -R deploy:www-data /home/deploy/salon-gobel/shared/storage
sudo chmod -R 775 /home/deploy/salon-gobel/shared/storage
```

### 12.3 Erreur 502 Bad Gateway

**Symptôme :** Page blanche avec erreur 502

**Solution :**
```bash
# Vérifier que PHP-FPM tourne
sudo systemctl status php8.2-fpm

# Redémarrer si nécessaire
sudo systemctl restart php8.2-fpm

# Vérifier les logs
sudo tail -f /var/log/nginx/etcgobel.error.log
```

### 12.4 Erreur de connexion MySQL

**Symptôme :**
```
SQLSTATE[HY000] [1045] Access denied for user 'salon_user'@'localhost'
```

**Solution :**
```bash
# Vérifier/recréer l'utilisateur MySQL
sudo mysql -u root -p
```

```sql
DROP USER IF EXISTS 'salon_user'@'localhost';
CREATE USER 'salon_user'@'localhost' IDENTIFIED BY 'NOUVEAU_MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON salon_db.* TO 'salon_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Puis mettre à jour le `.env` avec le nouveau mot de passe.

### 12.5 Assets non chargés (CSS/JS)

**Symptôme :** Page sans style, erreurs 404 sur les assets

**Solution :**
```bash
cd /home/deploy/salon-gobel/current
npm run build
php artisan storage:link --force
```

