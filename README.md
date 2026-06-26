# Laravel DB URL Replacer

[![Tests](https://github.com/RenderbitTechnologies/laravel-db-url-replacer/actions/workflows/run-tests.yml/badge.svg)](https://github.com/RenderbitTechnologies/laravel-db-url-replacer/actions/workflows/run-tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/renderbit/laravel-db-url-replacer.svg?style=flat-square)](https://packagist.org/packages/renderbit/laravel-db-url-replacer)
[![PHP Version](https://img.shields.io/packagist/php-v/renderbit/laravel-db-url-replacer.svg?style=flat-square)](https://packagist.org/packages/renderbit/laravel-db-url-replacer)
[![License](https://img.shields.io/packagist/l/renderbit/laravel-db-url-replacer.svg?style=flat-square)](https://packagist.org/packages/renderbit/laravel-db-url-replacer)

A Laravel Artisan command to search and replace URLs across your database tables. Supports:

- Targeting all text-type columns (`varchar`, `text`, `longtext`, `mediumtext`, `char`, `string`) in all tables by default
- Limiting to specific tables or columns using options
- Validates both old and new URLs, plus table and column existence
- Dry run mode to preview changes without modifying data
- Summary output with affected rows per table/column

## Installation

```bash
composer require --dev renderbit/laravel-db-url-replacer
```

Package auto-discovery is supported — no manual provider registration needed.

### Compatibility

| PHP    | Laravel |
|--------|---------|
| 8.0–8.4 | 8–12  |

## Usage

```bash
php artisan db:replace-url "http://old.url" "https://new.url"
```

### Options

| Option | Description |
|--------|-------------|
| `--tables=table1,table2` | Comma-separated list of tables to limit the replacement to |
| `--columns=col1,col2` | Comma-separated list of columns to limit the replacement to |
| `--dry-run` | Preview what would be changed without modifying any data |

### Examples

```bash
# Replace across all text columns in all tables
php artisan db:replace-url "http://old.example.com" "https://new.example.com"

# Target only specific tables
php artisan db:replace-url "http://old.example.com" "https://new.example.com" --tables=posts,comments

# Target only specific columns
php artisan db:replace-url "http://old.example.com" "https://new.example.com" --columns=content,excerpt

# Preview changes without writing
php artisan db:replace-url "http://old.example.com" "https://new.example.com" --dry-run
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

Tests are run automatically via GitHub Actions on push and pull requests, across the full PHP/Laravel compatibility matrix.

## License

MIT
