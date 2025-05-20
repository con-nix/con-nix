# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains a Laravel web application with Livewire integration, set up with a Nix development environment. The project uses:

1. Laravel 12 - PHP web framework
2. Livewire/Volt - PHP components for building dynamic UIs
3. Livewire/Flux - Component library
4. Vite + Tailwind CSS - Frontend tooling
5. Nix Flake - Development environment configuration

## Key Commands

### PHP Development Commands

```bash
# Install PHP dependencies
composer install

# Start the development server, queue worker, log viewer, and frontend build
composer dev

# Run tests
composer test

# Run a specific test
php artisan test --filter=TestName

# Run browser tests with Laravel Dusk (using Docker)
./docker-dusk.sh

# Run browser tests with Laravel Dusk (in Nix environment)
./run-dusk.sh

# Run a specific Dusk test
./run-dusk.sh --filter=TestName

# Run the PHP linter
./vendor/bin/pint

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Create a new controller
php artisan make:controller ControllerName

# Create a new model with migration
php artisan make:model ModelName -m

# Start the Laravel server only
php artisan serve
```

### Frontend Development Commands

```bash
# Install JS dependencies
npm install

# Start the Vite development server
npm run dev

# Build for production
npm run build
```

### Nix Development Environment

The project uses Nix Flake for a reproducible development environment:

```bash
# Enter the development environment 
nix develop

# If using direnv
direnv allow
```

The Nix shell provides access to:
- PHP 8.4
- Composer
- PHPStan and Psalm for static analysis
- PHPUnit for testing
- Bun for JavaScript package management
- Prettier for code formatting

## Architecture

### Core Components

1. **Laravel Framework**
   - Follows standard Laravel architecture with Models, Controllers, and Views
   - Uses Livewire for reactive UI components

2. **Authentication System**
   - Complete authentication flow with login, registration, and password reset
   - Email verification functionality

3. **Settings Pages**
   - User profile management
   - Password updates
   - Account deletion functionality
   - Appearance settings

4. **Frontend**
   - Tailwind CSS for styling
   - Vite for asset compilation and hot module replacement

### Database

The project is configured to use SQLite by default for development, but can be configured to use other databases supported by Laravel.

## Testing

The test suite is organized into Feature and Unit tests, following Laravel's testing conventions:

1. **Feature Tests** - Test complete features including HTTP requests/responses
   - Authentication tests
   - Dashboard tests
   - Settings tests

2. **Unit Tests** - Test individual components in isolation

Tests use an in-memory SQLite database for performance.