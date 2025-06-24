# Stack Projets

### Stack Backend
    ```bash
    PHP 8.2+ - Version moderne avec de bonnes performances
    Symfony 6.4 LTS - Framework principal, version LTS pour la stabilité
    Doctrine ORM - Gestion de base de données (inclus avec Symfony)
    PostgreSQL ou MySQL 8.0 - Base de données relationnelle
    ```
### Stack Frontend
    ```bash
    Twig - Moteur de templates de Symfony (simple et intégré)
    Symfony UX - Composants modernes (Stimulus, Turbo)
    Bootstrap 5 ou Tailwind CSS - Framework CSS
    Webpack Encore - Bundler d'assets (inclus avec Symfony)
    ```
### Outils de développement
    ```bash
    Symfony CLI - Outil en ligne de commande
    Docker + Docker Compose - Containerisation
    Xdebug - Debugging PHP
    PHPUnit - Tests unitaires
```

# Roadmap Projet E-commerce Symfony 7.3
## Phase 1 : Architecture et Entités de Base (Semaine 1)

### 1.1 Installation des bundles essentiels
```bash
# Authentification JWT
composer require lexik/jwt-authentication-bundle

# API Platform (optionnel pour API)
composer require api-platform/core

# Serializer
composer require symfony/serializer

# Validation
composer require symfony/validator

# Fixtures pour les données de test
composer require --dev orm-fixtures
```

### 1.2 Création des entités principales
```bash
# Entités utilisateur
php bin/console make:entity User
php bin/console make:entity UserProfile

# Entités produit
php bin/console make:entity Category
php bin/console make:entity Product
php bin/console make:entity ProductImage

# Entités commande
php bin/console make:entity Order
php bin/console make:entity OrderItem
php bin/console make:entity Cart
php bin/console make:entity CartItem

# Entités paiement
php bin/console make:entity Payment
php bin/console make:entity Shipping
```

### 1.3 Relations entre entités
- **User** : OneToMany avec Order, OneToOne avec Cart
- **Category** : ManyToMany avec Product
- **Product** : ManyToMany avec Category, OneToMany avec ProductImage
- **Order** : ManyToOne avec User, OneToMany avec OrderItem
- **Cart** : OneToOne avec User, OneToMany avec CartItem

## Phase 2 : Authentification JWT (Semaine 2)

### 2.1 Configuration JWT
```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600
```

### 2.2 Génération des clés
```bash
# Générer les clés JWT
php bin/console lexik:jwt:generate-keypair
```

### 2.3 Contrôleurs d'authentification
```bash
# Contrôleur d'authentification
php bin/console make:controller AuthController

# Contrôleur d'inscription
php bin/console make:controller RegistrationController
```

### 2.4 Système de rôles
- **ROLE_CLIENT** : Utilisateur standard
- **ROLE_PRO** : Compte professionnel
- **ROLE_ADMIN** : Administrateur

## Phase 3 : Gestion des Produits et Catégories (Semaine 3)

### 3.1 CRUD pour les entités
```bash
# Génération CRUD
php bin/console make:crud Category
php bin/console make:crud Product
```

### 3.2 Upload d'images
```bash
# Bundle pour les uploads
composer require vich/uploader-bundle
```

### 3.3 Contrôleurs publics
- **ProductController** : Affichage des produits
- **CategoryController** : Navigation par catégories
- **SearchController** : Recherche de produits

## Phase 4 : Système de Panier et Commandes (Semaine 4)

### 4.1 Gestion du panier
```bash
php bin/console make:controller CartController
```

### 4.2 Services métier
- **CartService** : Gestion du panier
- **OrderService** : Création et gestion des commandes
- **PricingService** : Calculs de prix, taxes, frais de port

### 4.3 Workflow des commandes
1. Ajout produits au panier
2. Validation du panier
3. Création de la commande
4. Paiement
5. Confirmation

## Phase 5 : Intégration Stripe (Semaine 5)

### 5.1 Installation
```bash
composer require stripe/stripe-php
```

### 5.2 Configuration Stripe
```yaml
# config/services.yaml
parameters:
    stripe.public_key: '%env(STRIPE_PUBLIC_KEY)%'
    stripe.secret_key: '%env(STRIPE_SECRET_KEY)%'
```

### 5.3 Service de paiement
```bash
php bin/console make:service PaymentService
```

### 5.4 Webhooks Stripe
```bash
php bin/console make:controller StripeWebhookController
```

## Phase 6 : Interface Frontend (Semaine 6)

### 6.1 Installation des assets
```bash
# Bootstrap
npm install bootstrap @popperjs/core

# Stimulus (déjà inclus)
npm install @hotwired/stimulus

# Mode sombre
npm install bootstrap-dark-5
```

### 6.2 Structure des templates
```
templates/
├── base.html.twig
├── layouts/
│   ├── header.html.twig
│   ├── footer.html.twig
│   └── sidebar.html.twig
├── pages/
│   ├── home.html.twig
│   ├── products/
│   └── cart/
├── auth/
│   ├── login.html.twig
│   └── register.html.twig
└── admin/
```

### 6.3 Composants Stimulus
- **cart-controller** : Gestion du panier
- **theme-controller** : Mode clair/sombre
- **search-controller** : Recherche en temps réel

## Phase 7 : Intégration Colissimo (Semaine 7)

### 7.1 Service de livraison
```bash
php bin/console make:service ShippingService
```

### 7.2 API Colissimo
- Calcul des frais de port
- Génération d'étiquettes
- Suivi des colis

## Phase 8 : CMS et Gestion de Contenu (Semaine 8)

### 8.1 Entités CMS
```bash
php bin/console make:entity Page
php bin/console make:entity Block
php bin/console make:entity Menu
```

### 8.2 Éditeur WYSIWYG
```bash
# TinyMCE ou CKEditor
npm install tinymce
```

### 8.3 Interface d'administration
```bash
# EasyAdmin (optionnel)
composer require easycorp/easyadmin-bundle
```

## Phase 9 : Sécurité et Optimisation (Semaine 9)

### 9.1 Sécurité
- Validation des données
- Protection CSRF
- Limitation du taux de requêtes
- Chiffrement des données sensibles

### 9.2 Performance
- Cache Redis/Memcached
- Optimisation des requêtes Doctrine
- Pagination
- Lazy loading des images

### 9.3 Tests
```bash
# Tests unitaires
php bin/console make:test

# Tests fonctionnels
composer require --dev symfony/browser-kit
```

## Phase 10 : Déploiement et Monitoring (Semaine 10)

### 10.1 Configuration production
- Variables d'environnement
- Optimisation du cache
- Minification des assets

### 10.2 Monitoring
- Logs structurés
- Métriques de performance
- Alertes erreurs

## Commandes de démarrage immédiat

```bash
# 1. Créer la base de données
php bin/console doctrine:database:create

# 2. Première entité User
php bin/console make:entity User

# 3. Première migration
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# 4. Créer le contrôleur d'accueil
php bin/console make:controller HomeController

# 5. Installer les assets frontend
npm install
npm run dev
```

## Structure recommandée des dossiers

```
src/
├── Controller/
│   ├── Admin/
│   ├── Api/
│   └── Shop/
├── Entity/
├── Repository/
├── Service/
│   ├── Cart/
│   ├── Payment/
│   └── Shipping/
├── Form/
├── Security/
└── EventListener/
```

## Prochaines étapes immédiates

1. **Créer l'entité User** avec les rôles
2. **Configurer JWT** pour l'authentification
3. **Créer les entités Product et Category**
4. **Mettre en place le système de panier**
5. **Intégrer Stripe** pour les paiements

