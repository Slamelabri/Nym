# Setup Environnement de Développement Symfony 7.3

## 1. Vérification de l'installation

D'abord, vérifie que tout est bien installé :

```bash
# Vérifier Symfony
symfony check:requirements

# Vérifier Composer
composer --version

# Vérifier les dépendances du projet
composer install
```

## 2. Configuration de la base de données

### Créer le fichier .env.local
Crée un fichier `.env.local` à la racine de ton projet (copie de `.env`) :

```bash
cp .env .env.local
```

### Configurer la base de données dans .env.local
```env
# Database URL pour MySQL
DATABASE_URL="mysql://root:password@127.0.0.1:3306/nym_db?serverVersion=8.0.32&charset=utf8mb4"

# Ou pour PostgreSQL
# DATABASE_URL="postgresql://username:password@127.0.0.1:5432/nym_db?serverVersion=15&charset=utf8"
```

### Créer la base de données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Vérifier la connexion
php bin/console doctrine:schema:validate
```

## 3. Installation de Xdebug

### Télécharger Xdebug
1. Va sur https://xdebug.org/download
2. Ou utilise le wizard : copie le résultat de `php -i` sur https://xdebug.org/wizard

### Configuration automatique (recommandée)
```bash
# Avec PECL (si disponible)
pecl install xdebug

# Ou télécharge le .dll correspondant à ta version PHP 8.4 x64 TS
```

### Configuration manuelle
Ajoute ces lignes dans ton `php.ini` (trouve le chemin avec `php --ini`) :

```ini
[xdebug]
zend_extension=xdebug
xdebug.mode=debug,develop
xdebug.start_with_request=yes
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.log=C:\tmp\xdebug.log
xdebug.idekey=VSCODE
```

### Vérifier Xdebug
```bash
php -v
# Tu devrais voir "with Xdebug v3.x.x"

# Ou
php -m | grep xdebug
```

## 4. Configuration VS Code (si tu l'utilises)

### Extensions recommandées
- PHP Intelephense
- PHP Debug
- Symfony for VSCode
- Twig Language 2

### Configuration du debugger (.vscode/launch.json)
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

## 5. Démarrer le serveur de développement

```bash
# Démarrer le serveur Symfony
symfony serve

# Ou avec PHP built-in server
php -S localhost:8000 -t public/

# Avec support HTTPS (recommandé)
symfony serve --daemon
```

Ton application sera accessible sur `https://127.0.0.1:8000`

## 6. Configuration des assets (optionnel)

Si tu veux utiliser Webpack Encore :

```bash
# Installer les dépendances Node.js
npm install

# Compiler les assets en mode dev
npm run dev

# Ou en mode watch
npm run watch
```

## 7. Commandes utiles pour le développement

```bash
# Vider le cache
php bin/console cache:clear

# Créer un contrôleur
php bin/console make:controller

# Créer une entité
php bin/console make:entity

# Générer la migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir les routes
php bin/console debug:router

# Voir les services
php bin/console debug:container
```

## 8. Test de l'environnement

### Créer un contrôleur de test
```bash
php bin/console make:controller TestController
```

### Ajouter un breakpoint et tester Xdebug
Dans le contrôleur généré, ajoute :
```php
public function index(): Response
{
    $message = "Hello Symfony 7.3!";
    // Ajoute un breakpoint ici
    return $this->render('test/index.html.twig', [
        'message' => $message,
    ]);
}
```

### Tester
1. Lance le debugger dans VS Code (F5)
2. Va sur la route du contrôleur
3. Le debugger devrait s'arrêter au breakpoint

## 9. Structure recommandée pour le développement

```
Nym/
├── config/         # Configuration
├── src/
│   ├── Controller/ # Contrôleurs
│   ├── Entity/     # Entités Doctrine
│   ├── Repository/ # Repositories
│   └── Service/    # Services métier
├── templates/      # Templates Twig
├── public/         # Assets publics
├── var/           # Cache, logs
└── .env.local     # Config locale
```

## 10. Prochaines étapes

Une fois l'environnement configuré :
1. Crée tes premières entités avec `make:entity`
2. Configure tes routes
3. Crée tes templates Twig
4. Utilise `make:crud` pour du CRUD rapide

**Note importante :** Assure-toi que le port 9003 n'est pas bloqué par ton firewall pour Xdebug.