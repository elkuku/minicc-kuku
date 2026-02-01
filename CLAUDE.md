# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MiniCC KuKu is a Symfony 8.x financial management application for handling credit cards, deposits, transactions, and store management. It includes features for VAT calculation, PDF generation, and reporting.

## Tech Stack

- **PHP 8.3+** with Symfony 8.x
- **Doctrine ORM 3.3+** with PostgreSQL (configurable)
- **Twig 3.x** templating with Bootstrap 5.3
- **Stimulus.js** for frontend interactivity
- **wkhtmltopdf** via KnpSnappy for PDF generation
- **Google API Client** for OAuth2 and integrations

## Build & Test Commands

```bash
# Full test suite (drops/creates test DB, runs migrations, fixtures, PHPUnit, PHPStan, Rector)
make tests

# Quick code quality checks only (PHPStan + Rector dry-run)
make tests2

# Run PHPUnit tests directly
symfony php vendor/bin/phpunit

# Run a single test file
symfony php vendor/bin/phpunit tests/Controller/WelcomeTest.php

# Run PHPStan static analysis
vendor/bin/phpstan --memory-limit=2G

# Check for outdated dependencies
composer check
```

## Development Commands

```bash
# Start development server
symfony server:start

# Database operations
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load

# Create new migration after entity changes
symfony console doctrine:migrations:diff
```

## Architecture

### Entity Model

The domain model centers around financial transactions for stores:

- **User** - System users with roles (ROLE_USER, ROLE_CASHIER, ROLE_ADMIN). Identified by email, supports Google OAuth.
- **Store** - Business locations belonging to users
- **Transaction** - Financial records linking users, stores, and payment methods with amounts and dates
- **Deposit** - Bank deposits linked to transactions
- **PaymentMethod** - Payment options (bar, bank, etc.)
- **Contract** - Financial contracts with template-based PDF generation

### Controller Organization

Controllers are organized by domain under `src/Controller/`:
- `Admin/` - Admin-only operations (payments, tasks, database backup, rent collection)
- `Contracts/` - Contract CRUD and PDF generation
- `Deposits/` - Deposit management and lookup
- `Transactions/` - Transaction CRUD
- `Stores/` - Store management
- `Users/` - User management
- `Security/` - Login form and Google OAuth authentication
- `Download/` - CSV/Excel exports
- `Mail/` - Email notifications (planillas, transaction reports)

### Authentication

Dual authentication via `LoginFormAuthenticator` and `GoogleIdentityAuthenticator`. Admin routes (`/admin/*`) require ROLE_ADMIN. Role hierarchy: ROLE_ADMIN includes ROLE_CASHIER.

## Code Quality

- **PHPStan Level 8** - Strict type checking enabled
- **Rector** - Code quality checks (run in dry-run mode via `make tests`)
- `src/Service/PhpXlsxGenerator.php` is excluded from PHPStan analysis

## Docker Development

```bash
# Start MySQL and PhpMyAdmin
docker-compose up -d

# Database backup
docker exec CONTAINER /usr/bin/mysqldump -u main -pmain main > backup.sql

# Database restore
cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u main -pmain main
```

PhpMyAdmin available at port 6080.
