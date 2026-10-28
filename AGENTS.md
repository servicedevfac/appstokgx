# AGENTS.md

## 1. Présentation du projet

Application de gestion de stock développée avec Laravel. L'interface existante utilise principalement Blade et Alpine.js. Vérifier l'architecture réelle du dépôt avant toute modification.

Ne pas migrer vers React ou Inertia sans demande explicite.

## 2. Principes de développement

- Examiner le code existant avant toute modification.
- Respecter les conventions et les dépendances du projet.
- Privilégier les corrections minimales et maintenables.
- Vérifier les versions de PHP et Laravel avant de modifier le code.
- Ne pas modifier de fonctionnalités sans rapport avec la demande.

## 3. Sécurité

- Vérifier l'authentification et les autorisations des routes sensibles.
- Examiner les Policies, Gates, middleware et permissions.
- Valider les données reçues par les contrôleurs.
- Protéger les données personnelles et financières.
- Ne jamais exposer de secrets.
- Ne jamais effectuer de test destructif en production.

## 4. Ventes, paiements et stocks

- Vérifier les calculs de ventes, remises, paiements et soldes.
- Garantir la cohérence des mouvements de stock.
- Utiliser des transactions lorsque plusieurs opérations doivent réussir ensemble.
- Prévenir les doubles traitements et les incohérences.
- Ne jamais altérer des données réelles pour faciliter un test.

## 5. Frontend

- Respecter Blade et Alpine.js dans l'architecture existante.
- Vérifier les formulaires, les erreurs et le responsive.
- Contrôler les actions destructives.
- Ne pas remplacer l'architecture frontend sans demande explicite.

## 6. Tests et qualité

- Examiner les tests existants avant d'en créer.
- Tester les règles métier, les autorisations et les cas limites.
- Vérifier l'isolation de la base de test avant d'exécuter des tests qui la modifient.
- Ne lancer aucune migration ou commande destructive sans autorisation.
- Ne jamais annoncer un test réussi sans résultat vérifiable.

## 7. Collaboration multi-agents

- project-coordinator : coordination et synthèse.
- laravel-backend : backend Laravel, API et logique métier.
- react-frontend : React, Inertia et Tailwind, uniquement si pertinents.
- laravel-security : audit de sécurité en lecture seule.
- testing-quality : stratégie et vérification des tests.

Déléguer uniquement aux agents disponibles. Éviter les modifications simultanées des mêmes fichiers. Attendre les résultats et signaler tout échec de délégation. Vérifier les preuves avant de retenir un constat.

## 8. Procédure de travail

1. Comprendre la demande et examiner le code concerné.
2. Identifier les risques et proposer un plan.
3. Demander validation avant les changements risqués.
4. Appliquer uniquement les modifications nécessaires.
5. Exécuter les vérifications dans un environnement sûr.
6. Présenter les fichiers modifiés, les résultats réels et les problèmes restants.

## 9. Rapports

Pour chaque bug, préciser la gravité, l'impact, les fichiers concernés, les preuves, la reproduction, la correction proposée et les tests nécessaires.

Ne jamais inventer des résultats, des fichiers, des tests ou des actions effectuées.
