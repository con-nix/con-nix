# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains a Laravel web application with Livewire integration, set up with a Nix development environment. The project is a GitHub-like repository management system with organizations. It uses:

1. Laravel 12 - PHP web framework
2. Livewire/Volt - PHP components for building dynamic UIs
3. Livewire/Flux - Component library for UI components
4. Vite + Tailwind CSS v4 - Frontend tooling with latest CSS framework
5. Nix Flake - Development environment configuration
6. SQLite - Default database for development

## Key Commands

### PHP Development Commands

```bash
# Install PHP dependencies
composer install

# Start the complete development environment (server, queue, logs, vite)
composer dev

# Run tests (clears config cache automatically)
composer test

# Run a specific test or test class
php artisan test --filter=TestName
php artisan test --filter=RepositoryTest

# Run the PHP linter (Laravel Pint)
./vendor/bin/pint

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Create a new controller
php artisan make:controller ControllerName

# Create a new model with migration and factory
php artisan make:model ModelName -mf

# Start the Laravel server only
php artisan serve
```

### Frontend Development Commands

```bash
# Install JS dependencies
npm install

# Start the Vite development server only
npm run dev

# Build for production
npm run build
```

### CSS/Styling Troubleshooting

The application uses Tailwind CSS v4 with Livewire Flux components. If CSS styling breaks:

1. **Clear Laravel caches first:**

    ```bash
    php artisan view:clear
    php artisan config:clear
    ```

2. **Remove stale production builds:**

    ```bash
    rm -rf public/build
    ```

3. **Rebuild production assets (as fallback):**

    ```bash
    npm run build
    ```

4. **Restart development server completely:**

    - Stop `composer dev` (Ctrl+C)
    - Run `composer dev` again to restart all services

5. **Check Vite dev server connectivity:**
    ```bash
    curl -s http://localhost:5173/@vite/client | head -5
    ```

The CSS configuration includes:

- Tailwind CSS v4 with `@tailwindcss/vite` plugin
- Flux component library CSS import
- Custom theme variables for consistent design
- Dark mode support with custom variant

### Nix Development Environment

```bash
# Enter the development environment
nix develop

# If using direnv (recommended)
direnv allow
```

The Nix shell provides:

- PHP 8.4
- Composer
- PHPStan and Psalm for static analysis
- PHPUnit for testing
- Bun for JavaScript package management
- Prettier for code formatting

## Architecture

### Domain Models

The application manages a three-tier ownership structure:

1. **Users** - Individual accounts that can own repositories and organizations
2. **Organizations** - Groups owned by users that can own repositories
3. **Repositories** - Code repositories that can be owned by either users or organizations

Key relationships:

- Users can own multiple organizations and repositories
- Organizations belong to one user but can own multiple repositories
- Repositories belong to either a user OR an organization (polymorphic ownership)
- Repository transfers can move ownership between user and organization accounts

### Authorization System

Uses Laravel Policies for granular permissions:

- `RepositoryPolicy` - Controls repository access, editing, deletion, and transfer
- `OrganizationPolicy` - Controls organization management

Authorization checks are implemented at the controller level with `can()` methods and in Blade templates with `@can` directives.

### Frontend Architecture

- **Layouts**: Nested layout system with `app.blade.php` wrapping content in `<flux:main>` and `app/sidebar.blade.php` providing the base structure
- **Components**: Uses Livewire Flux components (`<flux:button>`, `<flux:field>`, etc.) for consistent UI
- **Navigation**: Collapsible sidebar with JavaScript toggle functionality
- **Styling**: Tailwind v4 with custom theme variables and dark mode support

### Data Layer

- **Models**: Standard Laravel Eloquent models with factory support
- **Migrations**: Database schema versioning with descriptive migration names
- **Factories**: Comprehensive model factories for testing and seeding
- **Database**: SQLite for development, easily configurable for production databases

## Testing

The test suite follows Laravel conventions:

1. **Feature Tests** - Integration tests for complete user workflows:

    - Authentication flow testing
    - Repository CRUD operations
    - Organization management
    - Repository transfer functionality
    - Authorization boundary testing

2. **Unit Tests** - Component isolation testing

Test database uses in-memory SQLite for performance. Tests automatically clear configuration cache before running.

### Repository Transfer Feature

The application includes a repository transfer system allowing users to move repositories between personal accounts and organizations. Key implementation details:

- Transfer form with confirmation requirement (must type repository name)
- Authorization: only repository owners can transfer, only to organizations they own
- Database updates: toggles between `user_id` and `organization_id` fields
- UI: Orange "Transfer" button on repository show pages
- Routes: `GET /repositories/{repository}/transfer` and `PATCH /repositories/{repository}/transfer`

## Development Notes

- The application uses Laravel's resource controllers for standard CRUD operations
- Livewire Volt is used for reactive components in settings pages
- Repository creation supports both personal and organization ownership via form selection
- The sidebar includes JavaScript for collapse/expand functionality
- All forms use Flux components for consistent styling and validation display
