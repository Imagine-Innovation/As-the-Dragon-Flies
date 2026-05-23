# Agent Strategy & Project Instructions: Strict Verification & Lean Execution

## 1. System Environment & Tech Stack
- **Language/Runtime:** PHP 8.2 (Running locally via XAMPP).
- **Framework:** Yii2 PHP Framework (Active Record, Component Architecture).
- **Database:** MariaDB (Local instance via XAMPP, strict mode enabled).
- **Static Analysis:** PHPStan (Strict Level 9 compliance mandatory).
- **Testing Suite:** Codeception (Unit, Functional, and Acceptance suites).
- **Frontend Layer:** Bootstrap 5.3 (Vanilla JavaScript components, utility-first UI).

## 2. Core Architectural Principles
- **Strict Typing:** All newly created or modified files must use `declare(strict_types=1);`.
- **PHPStan Level 9 Compliance:** 
  - Zero `mixed` types allowed. Every parameter, return type, and property must be explicitly and narrowly typed.
  - Utilize explicit PHPDoc array shapes for complex data arrays (e.g., `/** @param array{id: int, status: string} $data */`).
  - Account for Yii2 magic properties (`$model->attribute`) by ensuring `proget-hq/phpstan-yii2` extension parameters are integrated, or explicitly add `@property` annotations in ActiveRecord model classes.
- **Fat Models, Skinny Controllers:** Business logic must reside in service layers or domain-specific models. Controllers should only handle routing, request parsing, and response formatting.

## 3. Code & UI Styling Conventions
- **PHP Styling:** Follow PSR-12 strict formatting rules.
- **UI Architecture (Bootstrap 5.3):**
  - Do not use jQuery for custom UI components; write native modern Vanilla JS (ES6+).
  - Use Bootstrap 5.3's native semantic dark/light mode triggers (`data-bs-theme="dark"`).
  - Prioritize Bootstrap utility classes over writing custom CSS rules in custom stylesheets.

## 4. Coding & Execution Strategy
- **Think Before Coding:** Don't assume or hide confusion. State your assumptions explicitly before implementing. If multiple interpretations exist, present the tradeoffs instead of picking silently. If something remains unclear, stop and ask.
- **Simplicity First:** Write the minimum code that solves the problem with nothing speculative. Do not build abstractions for single-use code, features beyond what was asked, or unrequested flexibility. If a complex 200-line implementation can be rewritten in 50 cleanly, rewrite it.
- **Surgical Changes:** Touch only what you must, and clean up only your own mess. Match the existing codebase style, and do not "improve" or refactor adjacent code that isn't broken. If your changes create unused imports, variables, or functions, remove them immediately; do not remove pre-existing dead code unless asked.

## 5. Goal-Driven Execution & Verification
- **Define Success Criteria:** Transform abstract tasks into verifiable goals. Write tests for invalid inputs to validate new fields, or reproduce bugs with a test before writing the fix. For multi-step tasks, state a brief step-by-step execution plan along with its verification check before writing code.
- **Codeception Framework Integration:**
  - Every pull request adding a feature must include a matching functional or unit test inside `tests/`.
  - Use Codeception DataFactory or Yii2 Fixtures to seed data; never hardcode IDs inside tests.
- **Verification Pipeline:** Loop and independently verify your changes until all criteria pass. Before declaring a task finished, execute the strict pipeline:
  1. Fix linting errors.
  2. Run PHPStan: `vendor/bin/phpstan analyze -l 9`.
  3. Run Test Suite: `vendor/bin/codecept run`.

## 6. XAMPP & MariaDB Constraints
- **Database Queries:** MariaDB SQL queries must use explicit columns; avoid `SELECT *`.
- **OS Interoperability:** Account for cross-platform file paths (Windows vs Linux local environments) by utilizing `Yii::getAlias()` for all file operations.