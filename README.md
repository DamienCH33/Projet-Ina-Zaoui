# Projet Symfony Ina Zaoui – Gestion d’albums et médias

## Présentation

Ce projet est une application backend développée avec **Symfony 7.4**.
Il permet la gestion d’utilisateurs, d’albums et de médias avec un système
d’authentification et une interface d’administration sécurisée.

Le projet a été conçu avec une forte exigence de **qualité logicielle** :
tests automatisés, analyse statique, conventions de code et intégration continue.

---

## Stack technique

- PHP >= 8.2
- Symfony 7.4
- PostgreSQL 17
- Doctrine ORM
- PHPUnit (tests)
- PHPStan (analyse statique)
- PHP-CS-Fixer (qualité de code)
- GitHub Actions (CI)

---

## Structure du projet

src/
 ├── Controller/
 ├── Entity/
 ├── Repository/
 ├── Form/
 ├── DataFixtures/
 └── Factory/

tests/
 ├── Unit/
 └── Functional/

.github/
 └── workflows/
     └── ci.yml

## Installation du projet :
Prérequis

PHP >= 8.2
Composer
PostgreSQL
Git

## Clonage du projet:
git clone https://github.com/<votre-repo>.git
cd projet_ina_zaoui


## Installation des dépendances:
composer install

## Configuration des environnements:

Base de données (développement)

Créer un fichier .env.local :
DATABASE_URL="postgresql://postgres:root@127.0.0.1:5432/ina_zaoui?serverVersion=17&charset=utf8"

Base de données (tests)

Créer un fichier .env.test :
DATABASE_URL="postgresql://postgres:root@127.0.0.1:5432/ina_zaoui_test?serverVersion=17&charset=utf8"

## Base de données:

Environnement de développement
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

Environnement de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
php bin/console doctrine:fixtures:load --env=test

## Lancer l’application:
symfony server:start ou php -S localhost:8000 -t public

## Tests et qualité de code:

Lancer les tests
vendor/bin/phpunit

Analyse statique (PHPStan)
vendor/bin/phpstan analyse

Vérification du style de code
vendor/bin/php-cs-fixer fix --dry-run

Correction automatique
vendor/bin/php-cs-fixer fix

## Intégration Continue (CI):

Une pipeline GitHub Actions est configurée.

Elle exécute automatiquement :

installation du projet

exécution des tests

analyse statique PHPStan

📄 Fichier de configuration :

.github/workflows/ci.yml

Chaque push ou pull request sur main déclenche la CI.


## Accès administrateur:
Pour se connecter avec le compte de Ina, il faut utiliser l'identifiant suivant:
- identifiant : `ina@zaoui.com`
