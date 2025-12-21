# VintDress Link Manager - Dashboard Central

## Description
Dashboard de gestion centralisée des liens blanchis et des rotators.

## Stack
- **Backend**: PHP 8.0+ avec SQLite
- **Frontend**: TailwindCSS, Vanilla JS, jsvectormap
- **Auth**: Sessions sécurisées, bcrypt, CSRF

## Structure
```
vintdress-link-manager/
├── config.php              # Configuration (DB, credentials)
├── includes/
│   ├── auth.php            # Authentification
│   ├── db.php              # SQLite + helpers
│   └── functions.php       # httpRequest, sync, formatRelativeDate
├── public/
│   ├── index.php           # Page login
│   ├── dashboard.php       # Dashboard principal
│   ├── api-logs.php        # API logs temps réel
│   ├── api-sync.php        # Sync vers rotators
│   ├── links.php           # CRUD liens
│   ├── sites.php           # CRUD sites
│   └── assets/
└── data/
    └── database.sqlite
```

## Base de données SQLite

### Tables
- **users**: id, username, password (bcrypt), created_at
- **links**: id, name, original_url, whitened_url, source, created_at, updated_at
- **sites**: id, name, base_url, api_token, is_active, created_at, updated_at
- **link_site**: id, link_id, site_id, is_synced, synced_at
- **sync_logs**: id, site_id, status, message, created_at

## API Endpoints

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api-logs.php` | GET | Agrège logs de tous les rotators (param: limit) |
| `/api-sync.php` | POST | Sync liens vers rotators sélectionnés |
| `/links.php` | POST | CRUD liens (action: add/edit/delete) |
| `/sites.php` | POST | CRUD sites (action: add/edit/delete/toggle) |

## Fonctionnalités
- Authentification sécurisée (bcrypt, sessions, CSRF)
- CRUD complet pour liens et sites/rotators
- Synchronisation vers rotators via API Bearer token
- Stats agrégées de tous les rotators
- Carte mondiale des clics (jsvectormap)
- Logs temps réel des requêtes (refresh 5s)
- Stats auto-refresh (15s)
- Pagination des liens
- Modales pour formulaires CRUD

## Communication avec Rotators
```
Dashboard → GET /api-logs.php?format=logs&limit=20 → Rotator
         ← JSON { success, logs: [{timestamp, url, country, city, device, browser, os}] }

Dashboard → POST /api-sync.php → Rotator
         ← JSON { success, message }
```

## Déploiement
1. Copier `config.example.php` → `config.php`
2. Définir `ADMIN_USERNAME` et `ADMIN_PASSWORD`
3. S'assurer que `data/` est en écriture (755)
4. Ajouter les rotators avec leur URL et token API
