# Laravel DB URL Replacer

A Laravel Artisan command that allows you to search and replace URLs in your database across all text columns, or in selected tables/columns.

## Features
- Search all `TEXT`-like columns by default
- Optional filtering by table(s) or column(s)
- Supports dry-run mode
- Tabular summary of changes

## Installation

```bash
composer require renderbit/laravel-db-url-replacer --dev
```

## Usage

```bash
php artisan db:replace-url http://old.url https://new.url
```

Optional:
```bash
--tables=users,posts       # Restrict to tables
--columns=content,body     # Restrict to columns
--dry-run                  # Simulate without applying changes
```

## License
MIT