# Contributing to Laravel DB URL Replacer

Thanks for your interest in contributing! Here's how to get started.

## Requirements

- PHP 8.0 or higher
- Composer
- A Laravel 8–12 application for local testing

## Getting Started

1. Fork and clone the repository:

```bash
git clone https://github.com/<your-username>/laravel-db-url-replacer.git
cd laravel-db-url-replacer
```

2. Install dependencies:

```bash
composer install
```

3. Run the test suite:

```bash
vendor/bin/phpunit
```

## Development Workflow

1. Create a branch from `main`:

```bash
git checkout -b your-feature-name
```

2. Make your changes.
3. Write or update tests for your changes.
4. Run `vendor/bin/phpunit` to ensure all tests pass.
5. Commit your changes with a clear, descriptive message.
6. Push to your fork and open a pull request against `main`.

## Code Guidelines

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style.
- Keep changes focused — one logical change per PR.
- Update the README if you add or change user-facing features.
- Add tests for new functionality.

## Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/) where possible:

```
feat: add support for custom column types
fix: handle empty table names gracefully
docs: update usage examples
test: add edge case for dry-run mode
```

## Reporting Bugs

Use the [Bug Report](https://github.com/RenderbitTechnologies/laravel-db-url-replacer/issues/new?template=bug_report.yml) template when filing issues.

## Requesting Features

Use the [Feature Request](https://github.com/RenderbitTechnologies/laravel-db-url-replacer/issues/new?template=feature_request.yml) template.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
