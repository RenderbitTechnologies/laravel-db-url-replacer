# GitHub Copilot Instructions

## Priority Guidelines

When generating code for this repository:

1. **Version Compatibility**: Always detect and respect the exact versions of PHP (8.0+), Laravel (8–12), and dependencies (doctrine/dbal ^3.0, orchestra/testbench ^6.0–^10.0) used in this project
2. **Context Files**: Prioritize patterns and standards defined in the `.github/copilot` directory
3. **Codebase Patterns**: When context files don't provide specific guidance, scan the codebase for established patterns
4. **Architectural Consistency**: Maintain our **layered service-provider** architectural style with strict separation between Commands, ServiceProviders, and Tests
5. **Code Quality**: Prioritize maintainability, testability, and security in all generated code

## Technology Version Detection

Before generating code, scan the codebase to identify:

1. **Language Versions**: PHP 8.0+ (`"php": "^8.0"` in composer.json)
   - Never use PHP features beyond 8.0 unless the target version allows it
   - Respect the wide Laravel compatibility matrix (8–12)

2. **Framework Versions**: Laravel 8–12 (`"illuminate/support": "^8.0|^9.0|^10.0|^11.0|^12.0"`)
   - Use APIs compatible across all supported Laravel versions
   - The CI matrix tests: PHP 8.0–8.4 × Laravel 8–12 (with exclusions for incompatible combos)

3. **Library Versions**:
   - `doctrine/dbal` ^3.0 — used for schema introspection fallback in older Laravel versions
   - `orchestra/testbench` ^6.0–^10.0 — used for package testing
   - Never use APIs not available in these version ranges

## Context Files

Prioritize the following files in `.github/copilot` directory (if they exist):

- **architecture.md**: System architecture guidelines
- **tech-stack.md**: Technology versions and framework details
- **coding-standards.md**: Code style and formatting standards
- **folder-structure.md**: Project organization guidelines
- **exemplars.md**: Exemplary code patterns to follow

## Codebase Scanning Instructions

When context files don't provide specific guidance:

1. Identify similar files to the one being modified or created
2. Analyze patterns for:
   - **Naming conventions**: PSR-4 namespaces under `Renderbit\DbUrlReplacer\`, PascalCase classes, camelCase methods
   - **Code organization**: One class per file, `src/Commands/` for Artisan commands, `src/` root for service providers
   - **Error handling**: Return `Command::FAILURE` on validation errors, `$this->error()` for user-facing messages, `try/catch \Throwable` for DB operations
   - **Logging approaches**: Use `$this->error()`, `$this->warn()`, `$this->info()`, and `$this->table()` for Artisan output
   - **Documentation style**: Minimal inline comments, rely on self-documenting code with clear naming
   - **Testing patterns**: Orchestra Testbench TestCase, `setUp()` for schema/data creation, `tearDown()` for cleanup, `$this->artisan()` with `expectsOutput()` and `assertExitCode()`

3. Follow the most consistent patterns found in the codebase
4. When conflicting patterns exist, prioritize patterns in newer files
5. Never introduce patterns not found in the existing codebase

## Code Quality Standards

### Maintainability
- Write self-documenting code with clear naming
- Follow the PSR-12 coding style standard (as specified in CONTRIBUTING.md)
- Keep functions focused on single responsibilities
- Limit function complexity and length to match existing patterns
- One logical change per PR

### Security
- Follow existing patterns for input validation (e.g., `filter_var()` with `FILTER_VALIDATE_URL`)
- Apply `add addslashes()` for SQL string escaping as done in the existing codebase
- Use parameterized queries via Laravel's Query Builder (`DB::table()->where()->update()`)
- Follow existing patterns for sanitizing user-provided input
- Handle sensitive data (database URLs) with care

### Testability
- Follow established patterns for testable code
- Use Orchestra Testbench for all package tests
- Create test tables in `setUp()` and drop them in `tearDown()` using `Schema::create()` and `Schema::dropIfExists()`
- Follow the existing test naming convention: `test_descriptive_snake_case_name()`
- Use `$this->artisan()` with argument/option arrays for testing Artisan commands
- Assert with `expectsOutput()`, `assertExitCode()`, and `assertDatabaseHas()`

## Documentation Requirements

- Match the level and style of comments found in existing code (minimal — rely on clear naming)
- Document parameters and return types only when non-obvious
- Follow existing patterns for documenting non-obvious behavior
- Update the README if you add or change user-facing features

## Testing Approach

### Unit Testing
- Match the exact structure and style of existing unit tests in `tests/`
- Follow the same naming conventions for test classes (`XxxTest extends TestCase`)
- Use the same assertion patterns found in existing tests (`expectsOutput`, `assertExitCode`, `assertDatabaseHas`)
- Follow existing patterns for test isolation (fresh tables per test via setUp/tearDown)
- Run `vendor/bin/phpunit` to verify tests pass

### End-to-End Testing
- Follow the same integration-style test patterns found in the codebase
- Test the full Artisan command workflow end-to-end as the existing tests do
- Verify both success and failure paths

## PHP / Laravel Guidelines

### PHP Guidelines
- Target PHP 8.0 as the minimum version
- Use type hints where the existing codebase uses them
- Follow the same import organization (`use` statements after namespace declaration)
- Use arrow functions (`fn()`) for short closures as seen in the codebase
- Apply the same error handling patterns (try/catch Throwable for DB operations)

### Laravel Guidelines
- Target Laravel 8–12 compatibility — never use features exclusive to a single Laravel version
- Use Laravel's Query Builder (`DB::table()`) over raw SQL where possible
- Use `DB::raw()` only when necessary for database-specific functions (e.g., `REPLACE()`)
- Follow the existing ServiceProvider pattern for package registration
- Use `runningInConsole()` guard when registering console-only commands
- Use Schema Builder (`$schema->getTables()`, `$schema->getColumns()`) for introspection, with Doctrine DBAL fallback for older Laravel versions

## Version Control Guidelines

- Follow Conventional Commits format (as specified in CONTRIBUTING.md):
  - `feat:` for new features
  - `fix:` for bug fixes
  - `docs:` for documentation changes
  - `test:` for test additions/changes
- Follow Semantic Versioning as applied in the codebase
- Branch from `main` for all changes

## General Best Practices

- Follow PSR-12 coding style exactly
- Match naming conventions: PascalCase classes, camelCase methods, snake_case test methods
- Apply error handling consistent with existing patterns
- Use `$this->error()` / `$this->warn()` / `$this->info()` for user-facing console output
- Match the existing approach to configuration and command signatures
- Use `addslashes()` for SQL string escaping as done in the codebase

## Project-Specific Guidance

- Scan the codebase thoroughly before generating any code
- Respect existing architectural boundaries (Commands, ServiceProviders, Tests)
- Match the style and patterns of surrounding code
- This is a **package** (library type), not an application — all code must be framework-agnostic and work when installed via Composer
- Support the full PHP 8.0–8.4 and Laravel 8–12 compatibility matrix
- When in doubt, prioritize consistency with existing code over external best practices
- Run `vendor/bin/phpunit` after making changes to verify nothing is broken
