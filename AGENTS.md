# Guidelines & AI Agent Directives for Google Jules

This document sets guidelines, structural rules, coding standards, testing requirements, and agentic engineering principles to enable Google Jules to generate highly relevant, maintainable, and robust code for this project.

When defining any policy or directive, relevant exceptions are explicitly documented to guide appropriate trade-offs.

---

## I. Codebase Organization & Architecture

### Repository Directory Structure
```
project/
├── backend/
│   ├── controllers/
│   ├── models/
│   ├── tests/
│   ├── views/
│   ├── web/
│   ├── widgets/
│   └── ...
├── common/
│   ├── components/
│   ├── config/
│   ├── models/
│   ├── services/
│   ├── tests/
│   ├── widgets/
│   └── ...
├── console/
│   ├── controllers/
│   └── migrations/
├── frontend/
│   ├── controllers/
│   ├── models/
│   ├── tests/
│   ├── views/
│   ├── web/
│   ├── widgets/
│   └── ...
├── composer.json
├── docker/
└── ...
```

### Module Responsibilities
1. **Framework:** Built on the latest version of the **Yii2 Advanced Application Template**.
2. **`backend/`:** Contains the configuration and management application for site administration.
3. **`common/`:** Shared components between backend and frontend. In particular, `common/models/` contains Active Record definitions mirroring the underlying database schema.
4. **`console/`:** Contains console commands and the `EventHandler` application enabling real-time communication between quest clients.
5. **`frontend/`:** Turn-based online RPG web application (Dungeons & Dragons style).
6. **`web/` Asset Subdirectories Parsing Policy:**
   - **Policy:** CSS (`.css`) and JavaScript (`.js`) files in `web/` subdirectories **shall be analyzed and parsed**. Non-code multimedia assets (images, audio files, compiled binary fonts) shall not be analyzed.
   - **Exceptions:** Minified vendor libraries (e.g., `jquery.min.js`, `bootstrap.min.css`) or third-party assets in `web/offline/` unless explicitly requested for debugging.

### Shared Library Conventions (`common/`)
- **Helpers:** Basic function libraries implemented as static helper classes.
- **Widgets:** Graphical/UI components implemented as Yii2 `Widget` classes.
- **Components:** Modular service libraries placed in `common/components/`. Initialize components via contextual instantiation (e.g., `$module = new MyModule(['playerId' => $playerId]);`).

---

## II. General Development & PHP Coding Rules

### 1. Strict Typing & Rigor
- **Policy:** Place `declare(strict_types=1);` at the top of every PHP file to prevent implicit type coercion. Type function arguments, class properties, and return values exhaustively.
- **Exceptions:**
  - View templates (`backend/views/*`, `frontend/views/*`) and simple HTML layout snippets where strict scalar types can interfere with framework rendering helpers or HTML generation.
  - Legacy code refactorings where adding `declare(strict_types=1)` would break third-party/framework type coercion without clear benefit, or where explicit review instructions dictate otherwise.

### 2. Class Immutability
- **Policy:** Use `readonly` for classes and properties to prevent state mutation after instantiation.
- **Exceptions:**
  - Active Record models (`yii\db\ActiveRecord` subclasses) whose properties represent mutable database rows.
  - Stateful services, form models, or entities whose properties must be updated during lifecycle processing.

### 3. Security by Design
- **Policy:** Never concatenate user input or dynamic variables into SQL queries. Always use PDO prepared statements or Yii Query Builder.
- **Exceptions:**
  - Structural SQL components (such as table names, column names, or fixed SQL keywords) that cannot be parameterized by PDO. In such cases, identifiers must be strictly validated against an explicit whitelist or escaped via Yii's schema identifier quoting methods (e.g., `quoteColumnName`).

### 4. Architecture & SOLID Principles
- **Policy:** Apply Single Responsibility Principle (SRP) and Dependency Inversion (DI). Inject interface dependencies via constructors rather than directly instantiating concrete classes inside services.
- **Exceptions:**
  - Lightweight value objects, data transfer objects (DTOs), framework widgets, or Active Record models instantiated contextually via Yii configuration arrays or static factory methods (e.g., `$module = new MyModule(['playerId' => $playerId])`).

### 5. Code Quality & Static Analysis
- **Policy:** Target **PHPStan Level 9** compliance across all custom classes.
- **Exceptions:**
  - Pre-existing legacy files that are outside the scope of current changes, unless directly touched or requested by the user.

### 6. Modern PHP 8 Features
- **Policy:** Adopt Constructor Property Promotion, `match` expressions, Named Arguments, and Attributes.
- **Exceptions:**
  - Simple classes without constructor boilerplate or framework annotations where traditional signatures remain clearer or required for framework compatibility.

---

## III. Testing Requirements

### 1. Happy Path & Boundary Value Testing
- **Policy:** Every newly created component, helper, or service must include unit/integration tests covering:
  - Standard execution paths (Happy path)
  - Boundary values (`null`, `0`, empty string `''`, maximum limits like 100%, page index bounds)
- **Exceptions:** Pure view templates, static layout views, or simple configuration files containing no executable business logic.

### 2. Resilience & Edge-Case Testing
- **Policy:** Write tests verifying graceful handling of negative conditions:
  - Missing parameters
  - Out-of-bound inputs (e.g., 150%)
  - Incorrect data types (e.g., passing string `'hello'`, array `[12]`, or `null` where an integer or array is expected)
  - Potential runtime crashes (e.g., division by zero)
  - Non-existent array key lookups (e.g., `$array['non_existent_key']`)
- **Exceptions:** Private internal helper methods where strict type declarations at the caller level make bad inputs impossible at compile/runtime.

---

## IV. Agentic Engineering Principles

### 1. Spectrum of AI-Assisted Development
- **Policy:** Follow **Agentic Engineering** for production code (specifications, tests, CI/CD gates, guardrails).
- **Exceptions:** Use **Vibe Coding** (rapid prototyping without strict harnesses) only when explicitly requested for throwaway POCs, internal quick scripts, or weekend prototypes.

### 2. Context Engineering Over Prompt Engineering
- **Policy:** Thoroughly explore repository context (`AGENT.md`, `DESIGN.md`, existing code) before generating implementations.
- **Exceptions:** Self-contained utility functions with no repository dependencies.

### 3. Agent = Model + Harness
- **Policy:** Rely on the surrounding harness (tools, sandboxes, test runners, static analysis) to verify code execution and diagnose errors.
- **Exceptions:** Non-code documentation tasks or pure text updates.

### 4. Conductor vs. Orchestrator Modes
- **Policy:** Operate in **Orchestrator Mode** for well-defined, autonomous tasks (delegating goals and reviewing final diffs).
- **Exceptions:** Switch to **Conductor Mode** (step-by-step guidance and continuous diff inspection) for complex, unfamiliar, or high-risk architectural changes.
