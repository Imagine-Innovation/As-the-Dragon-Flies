# Guidelines & AI Agent Directives for Google Jules

This document sets guidelines, structural rules, coding standards, testing requirements, and agentic engineering principles to enable Google Jules to generate highly relevant, maintainable, and robust code for this project.

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
6. **`web/` subdirectories:** Contain stylesheets, JavaScript libraries, and multimedia assets. **Do NOT analyze or parse asset files inside `web/` directories.**

### Shared Library Conventions (`common/`)
- **Helpers:** Basic function libraries implemented as static helper classes.
- **Widgets:** Graphical/UI components implemented as Yii2 `Widget` classes.
- **Components:** Modular service libraries placed in `common/components/`. Initialize components via contextual instantiation (e.g., `$module = new MyModule(['playerId' => $playerId]);`).

---

## II. General Development & PHP Coding Rules

### 1. Strict Typing & Rigor
- **Strict Types Directive:** Place `declare(strict_types=1);` at the top of every PHP file to prevent implicit type coercion.
- **Exhaustive Typing:** Expressly type function arguments, class properties, and return types. Use union (`string|int`) or intersection types where appropriate.
- **Immutability:** Use `readonly` for classes and properties to prevent state mutation after instantiation.

```php
<?php

declare(strict_types=1);

namespace App\Service;

final readonly class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
```

### 2. PSR Standard Compliance
- **PSR-12 / PER-CS:** Follow standard code style (4-space indentation, correct brace placement, grouped imports).
- **PSR-4:** Standard Composer namespace autoloading.
- **PSR-7 & PSR-15:** Standard HTTP request, response, and middleware interfaces.

### 3. Security by Design
- **Prepared SQL Statements:** Never concatenate variables into SQL queries. Always use PDO prepared statements or Yii Query Builder.
- **XSS Protection:** Sanitize user input before rendering using `htmlspecialchars($input, ENT_QUOTES, 'UTF-8')` or framework HTML helpers.
- **Secrets Management:** Keep secrets in environment configuration files ignored by Git.

```php
// ❌ SQL Injection Vulnerability
$pdo->query("SELECT * FROM users WHERE email = '$email'");

// ✅ Safe Prepared Statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

### 4. Architecture & SOLID Principles
- **Single Responsibility Principle (SRP):** Keep controllers thin; isolate business logic into services and models.
- **Dependency Inversion (DI):** Inject interface dependencies via constructors rather than directly instantiating dependencies inside classes.
- **PHP 8 Enums & Attributes:** Use native PHP `enum` types instead of scattered constants for fixed sets of values (roles, statuses).

### 5. Code Quality & Static Analysis
- **Static Analysis:** Target **PHPStan Level 9** compliance. Catch potential bugs before runtime.
- **Error Handling:** Catch and handle anomalies using specific, descriptive exceptions instead of suppressing errors.

### 6. Modern PHP 8 Features
- **Constructor Property Promotion:** Reduce boilerplate by declaring and initializing properties in constructor signatures.
- **Match Expressions:** Use `match` instead of verbose or loose `switch` statements.
- **Named Arguments:** Clarify function calls when passing optional or boolean parameters.
- **Attributes:** Use native attributes for routing and metadata configuration.

---

## III. Testing Requirements

### 1. Happy Path & Boundary Value Testing
- Verify standard behavior with boundary values:
  - `null` values
  - `0` or empty strings (`''`)
  - Maximum allowable values (e.g. 100%)
  - Pagination boundary thresholds

### 2. Resilience & Edge-Case Testing
Incorporate tests for negative paths and invalid conditions:
- Missing required parameters
- Out-of-bound values (e.g., 150% where 100% is max)
- Incorrect data types (e.g., passing string `'hello'`, array `[12]`, or `null` where an integer or array is expected)
- Potential crash conditions (e.g., division by zero)
- Non-existent array key lookups (e.g., accessing `$array['non_existent_key']`)

---

## IV. Agentic Engineering Principles

### 1. Spectrum of AI-Assisted Development
- **Vibe Coding:** Useful for fast prototypes or internal utilities where speed matters most.
- **Agentic Engineering:** Required for production software. Operates in a structured ecosystem with specifications, architecture docs, tests, and guardrails.

### 2. Context Engineering Over Prompt Engineering
Providing rich, structured codebase context yields far better results than prompt engineering alone:
- **Static Context:** Architecture conventions, project guidelines (`AGENT.md`, `DESIGN.md`), and tech stack rules.
- **Dynamic Context:** API specs, task descriptions, test outputs, and tool results loaded as needed.
- **Workflow:** `Specify Intent -> Discover Repo Context -> Generate Solution -> Verify & Validate`

### 3. Agent = Model + Harness
An AI coding agent relies on its surrounding harness: tools, sandboxes, rule files, test suites, and observability. Always use workspace tools to inspect and verify before making assumptions.

### 4. Test-First & Verification Focus
Implementation is cheap; verification and judgment are not. Focus on:
- Writing specifications and test contracts prior to or alongside code generation.
- Verifying edge cases, boundary conditions, and architectural alignment.
- Evaluating both output diffs and execution trajectory.

### 5. Conductor vs. Orchestrator
- **Conductor Mode:** For complex architectural tasks, inspect and review changes step-by-step.
- **Orchestrator Mode:** For well-defined tasks (refactoring, test generation), define goals and constraints, delegate execution, and review final outputs.
