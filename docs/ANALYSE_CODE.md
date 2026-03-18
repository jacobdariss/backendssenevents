# Analyse technique de l'application

## Vue d'ensemble
- Application **Laravel 12** orientée plateforme média/streaming avec architecture modulaire (`nwidart/laravel-modules`).
- Le cœur applicatif expose :
  - un **back-office web** (`/app/...`),
  - une **API mobile/TV** versionnée (`/api`, `/api/v2`, `/api/v3`),
  - un **frontend public** via module `Frontend`.

## Architecture observée

### 1) Backend PHP
- Stack moderne côté PHP : `php ^8.2`, `laravel/framework ^12.0`, `sanctum`, `livewire`, spatie (permissions, media), paiement (Stripe, Razorpay, PayPal), stockage cloud (S3, Bunny), etc.
- Autoload PSR-4 inclut `Modules\\` ce qui confirme une stratégie "feature modules".

### 2) Frontend / build assets
- Tooling frontend basé sur **Laravel Mix / Webpack** (et non Vite) avec Vue 3.
- Le build compile des assets globaux + assets par module en lisant `modules_statuses.json`.

### 3) Modularité métier
- Modules riches : `Entertainment`, `Subscriptions`, `LiveTV`, `Episode`, `Season`, `CastCrew`, `Coupon`, `SEO`, `Onboarding`, etc.
- Les modules actifs sont déclarés explicitement dans `modules_statuses.json`.

### 4) Routes et sécurité
- `RouteServiceProvider` applique les routes web + API, avec throttling global/API.
- Middleware custom nombreux (`checkInstallation`, `checkApiDevice`, `RoleBasedRouteAccess`, etc.).

## Points forts
1. **Domaines métiers bien isolés** via modules, utile pour scaling équipe/fonctionnalités.
2. **API versionnée** (`v2`/`v3`) : bon signal de gestion de compatibilité.
3. **Écosystème paiement et média complet** (streaming, OTP, QR TV login, facturation).
4. **Docker compose prêt** avec MySQL, Redis, Meilisearch, Mailhog, Selenium.

## Risques / dette technique identifiés
1. **README non maintenu** : reste un template GitLab générique, peu exploitable pour onboarding.
2. **Incohérences routing web** :
   - `RouteServiceProvider` ajoute déjà `checkInstallation` au groupe web, et `routes/web.php` le redéclare.
   - `routes/web.php` mélange syntaxes contrôleur modernes et legacy (`'Auth\\LoginController@...'`) ; cela peut casser selon namespace/controller resolution.
   - imbrication/fermeture de groupes difficile à lire => fort risque de régression lors d'ajout de routes.
3. **Décalage runtime Docker** : compose pointe vers runtime Sail PHP 8.1 alors que `composer.json` exige PHP 8.2.
4. **Tests peu représentatifs** : présence surtout de tests exemple/auth de base, peu de couverture visible des modules métiers.
5. **Surface de dépendances très large** : valeur fonctionnelle élevée mais coût sécurité/maintenance (mises à jour + vulnérabilités) important.

## Recommandations prioritaires

### Priorité haute (S1)
- Réécrire `README.md` avec:
  - prérequis réels,
  - installation locale + Docker,
  - commandes build/test,
  - architecture modules + API.
- Refactor `routes/web.php`:
  - harmoniser syntaxe de contrôleurs,
  - simplifier la hiérarchie des groupes,
  - clarifier l'ordre des middlewares,
  - ajouter tests de routes critiques.
- Aligner Docker sur **PHP 8.2** (runtime Sail/containers).

### Priorité moyenne (S2)
- Mettre en place une matrice de tests API sur endpoints critiques (`auth`, `profile`, `dashboard`, `payments`).
- Activer des garde-fous qualité en CI : `phpunit/pest`, `pint`, `composer audit`, éventuellement `npm audit`.
- Cartographier les dépendances à risque élevé (paiement, auth sociale, upload, webhooks).

### Priorité continue (S3)
- Standardiser conventions module (naming, routes, provider, tests).
- Ajouter ADR (Architecture Decision Records) pour choix structurants (Mix vs Vite, versioning API, stratégie auth multi-device).

## Conclusion
Le projet est **fonctionnellement ambitieux** et déjà structuré pour un produit média avancé. Le principal levier à court terme est la **stabilisation des fondations** (routing, doc, runtime, tests) afin de réduire le risque opérationnel tout en conservant la vélocité sur les modules métiers.
