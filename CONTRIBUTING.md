# Contribuer au projet – Ina Zaoui

Merci de votre intérêt pour ce projet
Ce document décrit les règles et bonnes pratiques à respecter afin de garantir
une **qualité de code élevée**, une **bonne maintenabilité** et une **intégration fluide**
des contributions.

---

## Objectifs

Les contributions doivent respecter les principes suivants :

- Code lisible et maintenable
- Respect des conventions Symfony / PHP
- Tests automatisés systématiques
- Aucun code non vérifié sur la branche principale
- Qualité de code contrôlée par des outils d’analyse

---

## Convention de branches

Le projet utilise une stratégie simple et efficace :

- `main`  
  → Branche stable, toujours fonctionnelle  
  → Aucune contribution directe autorisée

- `feature/<nom>`  
  → Nouvelle fonctionnalité  
  Exemple : `feature/media-upload`

- `fix/<nom>`  
  → Correction de bug  
  Exemple : `fix/media-validation`

- `test/<nom>`  
  → Ajout ou amélioration de tests  
  Exemple : `test/user-repository`

---

## Convention de commits

Les messages de commit doivent être **clairs et explicites**.

### Format recommandé

```text
type: description courte
Types autorisés
feat : nouvelle fonctionnalité

fix : correction de bug

test : ajout ou modification de tests

refactor : refactorisation sans changement fonctionnel

docs : documentation

ci : intégration continue

chore : maintenance (dépendances, configuration, etc.)

Exemples
feat: ajout de la gestion des albums
fix: correction validation formulaire media
test: ajout tests unitaires UserRepository
ci: ajout pipeline GitHub Actions
Procédure de contribution
Forker le dépôt (si nécessaire)

Créer une branche dédiée à partir de main

Développer la fonctionnalité ou le correctif

Ajouter ou mettre à jour les tests

Vérifier la qualité du code

Créer une Pull Request vers main

Outils de qualité obligatoires
Avant toute Pull Request, les commandes suivantes doivent passer sans erreur :

Tests
vendor/bin/phpunit
Analyse statique
vendor/bin/phpstan analyse
Style de code
vendor/bin/php-cs-fixer fix --dry-run
⚠️ Toute Pull Request ne respectant pas ces règles sera refusée.

Intégration Continue
Une pipeline GitHub Actions est configurée pour automatiser :

Installation du projet

Exécution des tests PHPUnit

Analyse statique PHPStan

Vérification du style de code

 Configuration : .github/workflows/ci.yml

Toute Pull Request doit passer la CI avec succès avant validation.

Tests attendus
Tests unitaires pour la logique métier

Tests fonctionnels pour les contrôleurs

Aucun ajout de fonctionnalité sans test associé

Tests lisibles, explicites et maintenables

Bonnes pratiques générales
Respecter les standards Symfony

Favoriser l’injection de dépendances

Éviter la logique métier dans les contrôleurs

Utiliser les repositories pour les accès base de données

Documenter les parties complexes du code

Ne jamais exposer de secrets ou mots de passe

Politique de validation
Une contribution est acceptée si :

La CI est verte

Les tests sont complets et pertinents

Le code est lisible et cohérent

Les conventions sont respectées

La fonctionnalité est clairement justifiée

Merci pour votre contribution