# Miraubolant Dashboard

Dashboard unifié pour la gestion des liens blanchis, rotators et statistiques.

## Description

Ce dashboard permet de :
- Gérer les liens blanchis (URLs raccourcies via Bitly, Twitter, etc.)
- Configurer et gérer les rotators distants
- Synchroniser tous les liens vers tous les rotators en un clic
- Visualiser les statistiques agrégées (clics, pays, appareils)
- Suivre les infos PopCash (solde, campagnes)

## Installation

### Avec Docker

```bash
docker build -t miraubolant-dashboard .
docker run -d -p 80:80 \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_PASSWORD=votre_mot_de_passe \
  -e APP_SECRET=cle_secrete_pour_sessions \
  -e POPCASH_API_KEY=votre_cle_popcash \
  -v miraubolant-data:/var/www/html/data \
  miraubolant-dashboard
```

### Sur Coolify

1. Créer une nouvelle application depuis ce repo
2. Configurer les variables d'environnement
3. Ajouter un volume persistant pour `/var/www/html/data`

## Variables d'environnement

| Variable | Description | Défaut |
|----------|-------------|--------|
| `ADMIN_USERNAME` | Nom d'utilisateur admin | `admin` |
| `ADMIN_PASSWORD` | Mot de passe admin | `changeme123` |
| `APP_SECRET` | Clé secrète pour les sessions | `change_this_secret` |
| `POPCASH_API_KEY` | Clé API PopCash (optionnel) | - |
| `DEBUG_MODE` | Mode debug (true/false) | `false` |

## Structure

```
vintdress-link-manager/
├── public/
│   ├── index.php       # Page login
│   ├── dashboard.php   # Dashboard unifié
│   └── logout.php
├── includes/
│   ├── auth.php        # Authentification
│   ├── db.php          # Base de données
│   ├── functions.php   # Helpers + API PopCash
│   └── layout.php      # Layout HTML
├── config.php
├── data/
│   └── database.sqlite
├── Dockerfile
└── README.md
```

## Fonctionnalités du Dashboard

- **Stats en temps réel** : Clics et IPs uniques agrégés de tous les rotators
- **Widget PopCash** : Solde et nombre de campagnes
- **Carte du monde** : Distribution géographique des clics
- **Gestion des liens** : CRUD via modales
- **Gestion des rotators** : CRUD via modales
- **Synchronisation** : Un bouton pour sync tous les liens vers tous les rotators
- **Logs** : Historique des synchronisations récentes

## Workflow Simplifié

1. Connectez-vous au dashboard
2. Ajoutez vos liens blanchis (nom + URL destination + URL blanchie + source)
3. Ajoutez vos rotators (nom + URL + token API)
4. Cliquez sur "Synchroniser" pour pousser tous les liens vers tous les rotators
5. Consultez les statistiques en temps réel

## Sécurité

- Authentification par session PHP sécurisée
- Protection CSRF sur tous les formulaires
- Mots de passe hashés avec bcrypt
- Headers de sécurité HTTP configurés
- Validation des URLs

## Licence

Projet privé Miraubolant.
