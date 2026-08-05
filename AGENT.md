# Agent Directives: Pragmatic & Test-Driven

## Core Execution Strategy
- **Lazy & Efficient:** Implement only what is explicitly requested (YAGNI). Reuse existing helpers, utilities, or standard library features before writing new code.
- **Surgical Diffs:** Touch only what is strictly necessary. Prefer deletion over addition, and boring code over clever code. 
- **Root Cause Fixes:** Fix bugs at the root source function rather than patching individual callers.
- **Cross-Platform Compatibility:** Use `Yii::getAlias()` for all file operations to ensure seamless execution across Windows (XAMPP) and Linux environments.

## Tech Stack & Architecture
- **Environment:** PHP 8.2 / Yii2 / MariaDB (XAMPP) / Bootstrap 5.3
- **Fat Models, Skinny Controllers:** Keep business logic inside service layers or ActiveRecord models. Controllers must only manage routing, request parsing, and response formatting.
- **Database:** Avoid `SELECT *`. Use explicit column selections in MariaDB queries.
- **UI Architecture:** Use modern Vanilla JavaScript (ES6+) for modern UI behaviors—do not write new jQuery code. Rely on Bootstrap 5.3 utility classes and semantic dark/light mode attributes (`data-bs-theme="dark"`).

## Testing & Quality Requirements
- **Mandatory Test Coverage:** Every newly created component, helper, service, or feature must include a corresponding test class under `tests/`.
- **Test Data Integrity:** Use Codeception DataFactory or Yii2 Fixtures to seed data. Never hardcode IDs in tests.
- **Clean Scope:** Clean up unused imports or variables introduced by your changes, but do not refactor or clean up pre-existing dead code in untouched files.

## Definition of Done
Before completing a task, run the verification pipeline in the terminal and ensure all checks pass:
1. `vendor/bin/phpstan analyze`
2. `vendor/bin/codecept run`
