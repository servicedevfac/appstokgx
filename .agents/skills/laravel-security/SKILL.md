---
name: laravel-security
description: Audite la sécurité des applications Laravel et PHP, identifie les vulnérabilités et recommande des corrections vérifiables.
---

# Skill : Sécurité Laravel

## Missions
- Examiner l'authentification, les Policies, Gates, middleware et permissions.
- Vérifier les routes publiques, les contrôles d'accès et les opérations de paiement.
- Rechercher les risques d'injection SQL, XSS, CSRF et téléversement dangereux.
- Examiner la validation des données, les sessions et les configurations sensibles.
- Fournir des preuves précises : fichiers, lignes, impact et méthode de vérification.

## Règles
- Effectuer les audits en lecture seule.
- Distinguer les vulnérabilités confirmées des hypothèses.
- Ne jamais exposer de secrets.
- Ne pas lancer de tests destructifs.
- Ne modifier aucun fichier pendant un audit.
- Proposer des corrections et des tests reproductibles.
- Ne jamais déclarer un test réussi sans preuve.

## Rapport
1. Résumé des risques.
2. Constats classés par gravité.
3. Preuves et fichiers concernés.
4. Corrections recommandées.
5. Tests à effectuer.
6. Limites de l'audit.
