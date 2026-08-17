# Outiltheque

Application Laravel + Livewire + Filament pour la gestion d’une outilthèque.

## Prérequis

- PHP 8.3+
- Composer
- Node.js 18+ et npm
- Base de données SQLite (par défaut) ou MariaDB/MySQL/PostgreSQL compatible Laravel
- Extension PHP recommandées :
  - bcmath
  - curl
  - dom
  - fileinfo
  - gd
  - intl
  - mbstring
  - openssl
  - pdo
  - pdo_sqlite (si SQLite)
  - zip

Vérifier les modules actifs :

```bash
php -m
```

## Installation

### 1) Cloner le projet

```bash
git clone <url-du-projet>
cd outiltheque
```

### 2) Installer les dépendances PHP

```bash
composer install
```

### 3) Installer les dépendances JavaScript

```bash
npm install
```

### 4) Configurer l’environnement

```bash
cp .env.example .env
php artisan key:generate
```

Par défaut le projet est configuré pour SQLite dans `.env.example` :

```env
DB_CONNECTION=sqlite
```

Si vous utilisez SQLite, vérifiez que le fichier de base existe ou créez-le :

```bash
touch database/database.sqlite
```

### 5) Lancer les migrations

```bash
php artisan migrate
```

## Données de démonstration via seeders et CSV

Le projet charge ses données de base via des seeders qui lisent des fichiers CSV stockés dans `storage/app/csv/`.

Fichiers présents :

- `storage/app/csv/categories.csv`
- `storage/app/csv/contracts.csv`
- `storage/app/csv/tools.csv`
- `storage/app/csv/features.csv`
- `storage/app/csv/users.csv`

### Générer la base de données à partir des CSV

```bash
php artisan db:seed
```

La commande appelées par `DatabaseSeeder` charge les seeders suivants :

- `UserSeeder`
- `CategorySeeder`
- `ContractSeeder`
- `ToolSeeder`
- `FeatureSeeder`

Important : les CSV doivent être présents dans `storage/app/csv/`, sinon les seeders échoueront.

### Vérifier les données

```bash
php artisan tinker
# puis : App\Models\Tool::count();
```

## Fichiers de stockage / images

Les images et icônes des outils sont importées dans le stockage public.

Créer un lien symbolique si nécessaire :

```bash
php artisan storage:link
```

## Frontend

Compiler les assets frontend :

```bash
npm run build
```

Pour le développement local :

```bash
npm run dev
```

## Démarrage du projet

En mode local :

```bash
php artisan serve
```

Ou avec le serveur Laravel + Vite en parallèle :

```bash
composer run dev
```

## Commandes utiles

```bash
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
```

## Remarques

- Les seeders utilisent les fichiers CSV dans `storage/app/csv/` comme source de données.
- Les utilisateurs, catégories, contrats, outils et fonctionnalités sont importés automatiquement.
- Si vous personnalisez les données, mettez à jour les CSV correspondants avant de relancer `php artisan db:seed`.
