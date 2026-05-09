# Deploiement VPS CyberGuard

## Objectif

Ce guide prepare un deploiement propre de CyberGuard sur un VPS Linux avec `Nginx`, `PHP-FPM`, `MySQL` et HTTPS.

## 1. Hypothese cible

Pile recommandee :

- Ubuntu 24.04 LTS ou Debian 12
- Nginx
- PHP 8.2+
- PHP-FPM
- MySQL 8+
- Composer
- Node.js 20+

## 2. Preparation du serveur

Mesures minimales conseillees :

- creer un utilisateur non root pour l'exploitation
- n'autoriser l'acces SSH que par cle
- activer un pare-feu avec `22`, `80` et `443`
- installer les mises a jour de securite
- proteger Nginx et SSH avec `fail2ban` si le VPS est expose

## 3. Installation applicative

Depuis le repertoire de deploiement :

```bash
git clone <repo> cyberguard
cd cyberguard
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Puis :

```bash
cp .env.example .env
php artisan key:generate
```

## 4. Variables d'environnement de production

Valeurs minimales a verifier dans `.env` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.tld

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cyberguard
DB_USERNAME=cyberguard
DB_PASSWORD=mot_de_passe_fort

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.tld
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@votre-domaine.tld
MAIL_FROM_NAME=CyberGuard

DEMO_AUTO_ATTACKS=false
HONEYPOT_DEMO_MODE=false
CYBERGUARD_AUTO_BLOCK_ENABLED=true
CYBERGUARD_AUTO_BLOCK_ALLOWLIST=127.0.0.1,::1,<votre_ip_admin>
CYBERGUARD_TRUSTED_ADMIN_IPS=<votre_ip_admin>
CYBERGUARD_MINI_SITE_REFRESH_ON_SEED=false
```

Points d'attention :

- ne jamais laisser `APP_DEBUG=true` sur un VPS public
- definir au moins une IP admin fiable dans `CYBERGUARD_TRUSTED_ADMIN_IPS`
- conserver `DEMO_AUTO_ATTACKS=false`
- ne pas reexecuter les seeders de demonstration en routine

## 5. Base de donnees

Creation initiale :

```bash
php artisan migrate --force
php artisan db:seed --class=SystemSeeder --force
php artisan db:seed --class=IntranetSeeder --force
php artisan db:seed --class=DetectionRuleSeeder --force
```

Si le VPS sert de plateforme de demonstration complete, un `php artisan migrate --seed --force` est acceptable.

Si le VPS est expose comme environnement reel, il vaut mieux semer uniquement ce qui est necessaire et changer immediatement les comptes par defaut.

## 6. Optimisation Laravel

Apres configuration :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Optionnel :

```bash
php artisan event:cache
```

## 7. Permissions

Verifier que l'utilisateur web peut ecrire dans :

- `storage/`
- `bootstrap/cache/`

Exemple classique :

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 8. Nginx

Exemple minimal :

```nginx
server {
    listen 80;
    server_name votre-domaine.tld;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votre-domaine.tld;

    root /var/www/cyberguard/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/votre-domaine.tld/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.tld/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 9. Scheduler

CyberGuard depend du scheduler Laravel pour la maintenance et l'auto-blocage.

Cron recommande :

```cron
* * * * * cd /var/www/cyberguard && php artisan schedule:run >> /dev/null 2>&1
```

## 10. Verifications apres deploiement

Checklist rapide :

- `php artisan about`
- `php artisan route:list`
- `php artisan schedule:list`
- page `/login` accessible en HTTPS
- OTP envoye correctement
- connexion admin fonctionnelle
- acces mini-site uniquement apres authentification
- logs applicatifs sans erreur critique

## 11. Durcissement recommande

- remplacer les comptes par defaut apres initialisation
- limiter SSH a tes IPs si possible
- sauvegarder la base et le fichier `.env`
- centraliser les logs si le VPS est durable
- surveiller `storage/logs/`
- renouveler automatiquement les certificats TLS
- ne pas exposer SQLite sur un environnement internet si MySQL est disponible

## 12. Limite importante

Ce projet reste une plateforme pedagogique et de demonstration. Un hebergement public est possible, mais il doit etre traite comme un environnement maitrise, avec jeu de donnees controle et exposition limitee.
