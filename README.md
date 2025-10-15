# 🧱 Portail Cyber — Dashboard Symfony

## 📖 Description

**Portail Cyber** est une application web basée sur **Symfony 7.3** et **Bootstrap 5**, déployée dans un environnement **Docker** simplifié.  
Elle fournit un tableau de bord centralisé permettant d’accéder à différentes fonctionnalités d’administration et d’analyse de sécurité.

Le portail prend en charge une **authentification via RADIUS** pour les environnements d’entreprise,  
avec un **mode local de secours** (compte `admin/admin`) pour le développement ou les tests hors réseau.

---

## 🚀 Fonctionnalités principales

- 🔐 **Authentification RADIUS** (via `dapphp/radius`)
- 🧩 **Compte local intégré** (`admin/admin` et `test/test`) pour mode hors ligne
- 🧭 **Interface responsive** avec Bootstrap 5
- 🧱 **Architecture Symfony** (contrôleurs, templates Twig, sécurité, routing)
- 📊 **Dashboard personnalisable** (catégories, flux RSS)
- 🐳 **Déploiement Docker complet** avec PHP-FPM et Nginx

---

## 🧩 Architecture des conteneurs

| Service | Rôle | Image |
|----------|------|-------|
| **php** | Application Symfony (PHP 8.3 + Composer) | `php:8.3-fpm` |
| **nginx** | Reverse proxy et serveur web | `nginx:latest` |

L’arborescence du projet est montée dans `/var/www/html` sur le conteneur PHP et Nginx.

---

## 📁 Structure du projet

```
Portail_Cyber/
├── docker-compose.yml
├── Dockerfile
├── nginx/
│   └── default.conf
├── symfony/
│   ├── config/
│   ├── public/
│   ├── src/
│   │   ├── Controller/
│   │   └── Security/
│   │       ├── RadiusUserProvider.php
│   │       └── RadiusAuthenticator.php
│   ├── templates/
│   │   ├── base.html.twig
│   │   └── security/login.html.twig
│   └── .env
```

---

## ⚙️ Installation

### 1️⃣ Cloner le dépôt

```bash
git clone https://github.com/DyDum/portail-cyber.git
cd portail-cyber
```

### 2️⃣ Démarrer l’environnement Docker

```bash
docker-compose up -d --build
```

Cela démarre les conteneurs PHP et Nginx.

---

## 🔑 Authentification

Deux modes sont disponibles :

### 🔹 Mode local (par défaut)
- Identifiant : `admin`  
- Mot de passe : `admin`

### 🔹 Mode RADIUS
Pour activer l’authentification via un serveur RADIUS,  
éditer le fichier `.env` à la racine du projet :

```dotenv
USE_RADIUS=true
RADIUS_SERVER=radius-server
RADIUS_SECRET=shared_secret
RADIUS_PORT=1812
```

Par défaut :
```dotenv
USE_RADIUS=false
```

⚠️ Si le serveur RADIUS ne répond pas ou que `USE_RADIUS=false`,  
le système bascule automatiquement sur le mode **local**.

---

## 🧱 Configuration Symfony

### Fichier `config/packages/security.yaml`

- Définit le firewall `main`
- Active `RadiusAuthenticator`
- Utilise un provider mémoire `admin/admin`
- Gère les rôles et les redirections (`/dashboard`, `/admin`, etc.)

---

## 💻 Commandes utiles

### Vider le cache Symfony
```bash
docker exec -it portail_php php bin/console cache:clear
```

### Accéder au conteneur PHP
```bash
docker exec -it portail_php bash
```

### Vérifier les logs
```bash
docker logs portail_php
```

---

## 🧪 Accès à l’application

Une fois les conteneurs lancés :  
➡️ [http://localhost:8080](http://localhost:8080)

Routes principales :
| URL | Description |
|------|--------------|
| `/login` | Page de connexion |
| `/dashboard` | Tableau de bord utilisateur |
| `/admin` | Interface d’administration (ROLE_ADMIN) |

---

## 🧰 Dépendances principales

| Composant | Version | Rôle |
|------------|----------|------|
| Symfony | 7.3 | Framework principal |
| PHP | 8.3 | Environnement d’exécution |
| Nginx | latest | Serveur web |
| Composer | 2.x | Gestion des dépendances |
| dapphp/radius | ^2.0 | Client RADIUS PHP |
| Bootstrap | 5.3 | Interface responsive |

---

## 🧑‍💻 Mode développement

Pour installer les dépendances PHP dans le conteneur :

```bash
docker exec -it portail_php composer install
```

Les fichiers Symfony sont montés dans le conteneur,  
les modifications locales sont donc automatiquement prises en compte.

---

## 🧾 Licence

Projet sous licence **MIT**.  
Modification, redistribution ou intégration à un projet interne autorisé.

---

## 🧭 Roadmap

- [x] Authentification locale
- [x] Authentification RADIUS
- [x] Gestion des flux RSS
- [x] Interface d’administration pour les catégories
- [ ] Mode multi-utilisateurs
- [ ] Monitoring simple du serveur RADIUS
