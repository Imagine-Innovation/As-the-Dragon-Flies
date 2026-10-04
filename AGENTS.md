# Directives de Développement & Guide pour Google Jules (Agentic Engineering)

Ce document définit les directives et principes fondateurs pour optimiser la collaboration avec l'agent d'IA (Google Jules) au sein de la codebase. Basé sur les principes de l'**Agentic Engineering**, il vise à maximiser la précision du code généré, réduire les allers-retours correctifs et garantir la qualité en production.

---

## 1. Spectre du Développement Assisté par IA

Le développement assisté par IA s'inscrit sur un spectre selon l'enjeu du projet :

- **Vibe Coding (Prototypes & Expérimentations) :**
  - Adapté aux prototypes rapides, scripts internes ou POCs où la rapidité prime sur la rigueur.
  - Processus : Description haut niveau -> Génération -> Ajustements rapides.
- **Agentic Engineering (Systèmes de Production) :**
  - Obligatoire pour le code destiné à la production.
  - Exige un environnement structuré : spécifications précises, documentation d'architecture, suites de tests, contraintes de sécurité et garde-fous (guardrails).

---

## 2. Context Engineering (Ingénierie du Contexte)

La qualité du code produit dépend directement du contexte fourni. L'agent doit toujours prendre en compte le contexte du dépôt avant de générer du code.

### Contexte Statique vs Dynamique
- **Contexte Statique (Toujours disponible) :**
  - Conventions de code et règles d'architecture globales.
  - Directives spécifiques au projet (ex: `AGENT.md`, `DESIGN.md`).
  - Choix technologiques de référence (frameworks, bibliothèques autorisées/interdites).
- **Contexte Dynamique (Chargé à la demande) :**
  - Documentation API spécifique et résultats d'outils.
  - Contexte relatif à la tâche ou à la fonctionnalité en cours.
  - Logs de tests ou de débogage.

### Règle d'or du Contexte
Privilégier le flux : **Spécifier l'intention -> Explorer le contexte repo -> Générer la solution -> Vérifier & Valider**, afin d'éviter d'improviser des choix d'architecture arbitraires.

---

## 3. Modèle + Harness (`Agent = Model + Harness`)

L'agent ne se limite pas au modèle de langage. Il s'appuie sur son **harness** (environnement d'exécution et outils) :

- **Outils & Sandbox :** Utiliser les outils disponibles (lecture de fichiers, exécution de tests, sessions Bash) pour analyser l'environnement avant toute modification.
- **Guardrails & Contraintes :** Respecter strictement les politiques de sécurité, les droits d'accès et les standards du projet.
- **Observabilité :** Analyser systématiquement les erreurs d'exécution ou d'analyse statique plutôt que d'émettre des hypothèses blindées.

---

## 4. Approche Test-First & Spécification Prioritaire

L'implémentation devenant rapide et économique, l'effort principal doit se concentrer en amont sur la spécification et la vérification.

### Flux de travail obligatoire
1. **Spécification :** Définir ce qui doit être accompli, les cas limites (*edge cases*) et les contraintes strictes.
2. **Tests / Évaluations :** Établir ou mettre à jour les tests (unitaires, d'intégration) définissant le contrat de succès *avant* ou conjointement à l'implémentation.
3. **Implémentation :** Générer le code nécessaire pour satisfaire spécifications et tests.
4. **Vérification :** Valider la conformité du code et vérifier le comportement global du système.

---

## 5. Priorité à la Vérification et au Jugement

L'IA excelle à produire les premiers 80% d'une solution. La valeur de l'ingénieur et de la vérification réside dans les 20% restants :

- **Résolution des cas limites (Edge Cases) :** Traitement des cas d'erreur, validation des entrées, et cas aux limites.
- **Respect de l'Architecture :** S'assurer que le code ne réintroduit pas de dette technique ou ne viole pas le découpage modulaire.
- **Évaluation de Trajectoire & Résultat :**
  - *Évaluation du résultat :* Le code final est-il correct et conforme aux spécifications ?
  - *Évaluation de trajectoire :* Les étapes suivies et les modifications apportées sont-elles cohérentes et minimales ?

---

## 6. Modes d'Opération : Conducteur vs Orchestrateur

L'interaction avec Jules s'adapte selon la complexité de la tâche :

- **Mode Conducteur (Tâches complexes ou architecturales) :**
  - Analyse étape par étape, guidage rapproché, inspection continue des modifications diff par diff.
- **Mode Orchestrateur (Tâches bien définies, migrations, refactorings) :**
  - Définition claire des objectifs, contraintes et critères d'acceptation.
  - Exécution autonome par l'agent puis revue globale du résultat.

---

## 7. Résumé des Engagements de l'Agent

Lors de chaque intervention, Google Jules s'engage à :
1. Consulter le contexte du dépôt (architecture, conventions, fichiers de règles).
2. Vérifier les spécifications et les tests existants avant toute modification majeure.
3. Produire des modifications chirurgicales et bien ciblées.
4. Lancer les outils de vérification (analyse statique, tests) pour valider l'implémentation.
5. Privilégier la clarté, la maintenabilité et la sécurité du code généré.
