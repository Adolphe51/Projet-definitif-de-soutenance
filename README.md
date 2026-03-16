# 🛡️ CyberGuard — Plateforme de Cybersécurité Laravel 12

> Système interactif de détection, simulation et défense contre les cyberattaques avec géolocalisation, traçage des attaquants, alarmes sonores vocales et environnement honeypot complet.

---

## 🚀 Installation Rapide

### Prérequis
- PHP 8.2+
- Composer
- SQLite (inclus) ou MySQL

### Étapes

```bash
# 1. Cloner / dézipper le projet
cd cyberguard

# 2. Installer les dépendances
composer install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données (SQLite - zéro config)
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# 5. Lancer le serveur
php artisan serve
```

Ouvrir : **http://localhost:8000**

---

## ⚙️ Configuration MySQL (optionnel)

Modifier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cyberguard
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

```bash
mysql -u root -p -e "CREATE DATABASE cyberguard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan db:seed
```

---

## 📡 Tâches Automatiques (Scheduler)

```bash
# Terminal dédié — génère des attaques + simule le honeypot automatiquement
php artisan schedule:work
```

Ou ajouter au cron :
```cron
* * * * * cd /path/to/cyberguard && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Commandes Artisan

```bash
# Générer des attaques de démo
php artisan cyberguard:detect --count=10

# Gérer le honeypot
php artisan cyberguard:honeypot status      # Statut des pièges
php artisan cyberguard:honeypot init        # Déployer les pièges
php artisan cyberguard:honeypot simulate    # Simuler des intrus
php artisan cyberguard:honeypot report      # Rapport complet

# Auto-bloquer les IPs suspectes
php artisan cyberguard:autoblock --threshold=5 --window=10

# Nettoyer l'ancienne données
php artisan cyberguard:cleanup --days=30
```

---

## 🗺️ Pages Principales

| URL | Description |
|-----|-------------|
| `/dashboard` | Tableau de bord avec stats en temps réel |
| `/attacks/live` | Flux de détection live (polling 3s) |
| `/attacks` | Liste complète des attaques + filtres |
| `/geo/attackers` | Carte mondiale SVG des attaquants |
| `/simulations` | Lancement et suivi des simulations |
| `/alerts` | Centre d'alertes avec replay sonore |
| `/honeypot` | Dashboard de gestion des pièges |

---

## 🍯 URLs Pièges Honeypot

Ces URLs sont **délibérément exposées** pour piéger les attaquants :

| URL | Piège |
|-----|-------|
| `/wp-admin` | Clone WordPress Login |
| `/phpmyadmin` | Clone phpMyAdmin |
| `/admin` | Panneau admin fictif |
| `/api/v1/users` | Fausse API REST |
| `/internal/confidential.pdf` | Document canary |
| `/.env` | Faux fichier .env |
| `/backup.sql` | Faux backup SQL |

---

## 🔊 Alarmes Sonores

Le système utilise **Web Audio API + Speech Synthesis** :
- Bips d'alerte répétitifs selon la sévérité
- Sirène électronique montante/descendante
- Voix française TTS : *"ALERTE SYSTÈME — ATTAQUE DÉTECTÉE"*
- Bannière rouge clignotante
- Overlay rouge sur l'écran
- Déclenchement automatique sur attaque `high` ou `critical`
- Test manuel via le bouton 🔊 dans la topbar

---

## 🛠️ Stack Technique

- **Backend** : Laravel 12, PHP 8.2+
- **Base de données** : SQLite (dev) / MySQL (prod)
- **Frontend** : Blade, CSS custom, JS vanilla
- **Charts** : Chart.js CDN
- **Icons** : Font Awesome 6
- **Fonts** : Exo 2, Rajdhani, Share Tech Mono (Google Fonts)
- **Audio** : Web Audio API + SpeechSynthesis API

---

## 📁 Architecture

```
app/
├── Console/Commands/      # Artisan: detect, honeypot, autoblock, cleanup
├── Http/
│   ├── Controllers/       # 6 contrôleurs principaux
│   └── Middleware/        # HoneypotMiddleware, CheckBlockedIp
├── Models/                # Attack, Alert, Simulation, BlockedIp, HoneypotTrap...
├── Providers/             # AppServiceProvider
└── Services/              # GeoService, AttackDetectionService, HoneypotService

resources/views/
├── layouts/app.blade.php  # Layout principal (sidebar + alarmes)
├── dashboard/             # Tableau de bord
├── attacks/               # Liste, Live, Détail, Carte, Tracé
├── alerts/                # Centre d'alertes
├── simulations/           # Lanceur de simulations
└── honeypot/              # Dashboard + 7 pièges visuels
```

---

## 🔒 Sécurité

⚠️ **Important** : Ce système est destiné à des fins éducatives et défensives uniquement.
- Les pièges honeypot collectent des données d'attaquants réels
- En production, configurez HTTPS et authentification admin
- Respectez les lois locales sur la collecte de données réseau

---

*CyberGuard v2.0 — Développé avec Laravel 12*
