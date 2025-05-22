# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains a GitHub-like repository management system built with Laravel 12 and Livewire/Volt. It implements a social coding platform with organizations, repository management, activity feeds, and notification systems.

**Core Features:**
- Repository hosting with user/organization ownership
- Organization management with member roles and invitations
- Social features (following users, activity feeds)
- Repository transfer system between accounts
- Comprehensive notification system

## Key Commands

### Nix Development Environment

All commands should be run using the Nix development shell:

```bash
# Enter the development environment
nix develop

# If using direnv (recommended)
direnv allow
```

### PHP Development Commands

```bash
# Install PHP dependencies
nix develop -c composer install

# Start complete development environment (server, queue, logs, vite)
nix develop -c composer dev

# Run tests with automatic config clearing
nix develop -c composer test

# Run specific test
nix develop -c php artisan test --filter=TestName

# Code formatting and linting
nix develop -c ./vendor/bin/pint
nix develop -c treefmt

# Laravel commands
nix develop -c php artisan migrate
nix develop -c php artisan make:controller ControllerName
nix develop -c php artisan make:model ModelName -mf
```

### Frontend Development Commands

```bash
# Install JS dependencies
nix develop -c bun install

# Start Vite development server
nix develop -c bun run dev

# Build for production
nix develop -c bun run build
```

### Quick Start Commands

```bash
# Development
nix develop -c composer dev    # Start full dev environment
nix develop -c composer test   # Run test suite

# Production Build & Serve
nix run .#default             # Build application
nix run .#serve               # Serve with FrankenPHP

# Containerized Deployment  
nix run .#docker-load         # Build Docker image
nix run .#docker-run          # Build and run container
```

### Production Build and Deployment

#### Option 1: Local Build with FrankenPHP

```bash
# Build the complete application for production
nix run .#default

# Serve the built application with FrankenPHP
nix run .#serve

# Build and serve in sequence
nix run .#default && nix run .#serve
```

The build process:
1. **Installs PHP dependencies** with Composer (production-optimized)
2. **Installs JavaScript dependencies** with Bun
3. **Builds frontend assets** with Vite
4. **Sets up production environment** with proper .env configuration
5. **Generates application key** for Laravel
6. **Creates SQLite database** and runs migrations
7. **Caches configuration** for optimal performance

The built application is stored in `~/.cache/con-nix-build/app` and can be served with FrankenPHP on port 8000.

#### Option 2: Containerized Deployment

```bash
# Build and load Docker image using Nix
nix run .#docker-load

# Run the containerized application
docker run -p 8000:8000 con-nix-laravel:latest

# Or build and run in one command
nix run .#docker-run
```

**Container Features:**
- **Self-contained environment** - All dependencies included via Nix
- **Runtime build optimization** - Application builds on first container start
- **Persistent build state** - Subsequent container restarts skip the build process
- **Production-ready** - Uses FrankenPHP with optimized Laravel configuration
- **Port 8000** - Application available at http://localhost:8000

**Container Architecture:**
- Built with `dockerTools.buildLayeredImage` for optimal layer caching
- Includes PHP 8.4, FrankenPHP, Composer, Bun, and SQLite
- Runtime build process ensures fresh dependencies while maintaining reproducibility
- Writable storage and cache directories for Laravel operations

## Architecture

### Domain Model Structure

The application uses a three-tier ownership hierarchy:

1. **Users** - Individual accounts that can own repositories and organizations
2. **Organizations** - Group entities owned by users, can own repositories and have members
3. **Repositories** - Code repositories with polymorphic ownership (user OR organization)

**Key Relationships:**
- Users can follow other users and see their activity in feeds
- Organizations have members with roles (owner, admin, member)
- Repository transfers allow moving ownership between user accounts and organizations
- Activity system tracks all user actions for social feeds
- Notification system handles organization invites, follows, and repository activities

### Authorization Pattern

Uses Laravel Policies for granular permissions:
- `RepositoryPolicy` - Controls repository access, editing, deletion, and transfer
- `OrganizationPolicy` - Controls organization management and member operations

Authorization is enforced at controller level with `can()` methods and in Blade templates with `@can` directives.

### Frontend Architecture

- **Livewire/Volt** for reactive PHP components (settings pages)
- **Livewire Flux** component library for consistent UI elements
- **Tailwind CSS v4** with custom theme variables and dark mode support
- **Nested layout system** with `app.blade.php` + collapsible sidebar
- **Vite** for asset compilation with hot reloading

### Data Layer

- **SQLite** for development (configurable for production databases)
- **Eloquent models** with comprehensive factory support for testing
- **Database migrations** with descriptive naming conventions
- **Activity tracking** through observers for automatic logging

## Repository Transfer System

Key implementation details for the transfer feature:
- Transfers require confirmation (user must type repository name exactly)
- Only repository owners can initiate transfers
- Can only transfer to organizations the user owns
- Database updates toggle between `user_id` and `organization_id` fields
- Accessible via orange "Transfer" button on repository show pages

## Testing Strategy

- **Feature tests** cover complete user workflows including auth, CRUD operations, and authorization boundaries
- **Unit tests** for component isolation
- **In-memory SQLite** for test performance
- Tests automatically clear configuration cache before running
- Comprehensive coverage of repository transfer and organization invite workflows

## CSS Troubleshooting Workflow

If styling breaks with Tailwind v4 + Flux components:

1. Clear Laravel caches: `nix develop -c php artisan view:clear && php artisan config:clear`
2. Remove stale builds: `rm -rf public/build`
3. Restart dev environment: Stop `composer dev` and restart
4. Rebuild assets: `nix develop -c bun run build` (fallback)

## Development Notes

- Repository creation supports both personal and organization ownership via form selection
- Organization invites work via public token-based URLs (no auth required for invite pages)
- Activity feed shows actions from followed users only
- Notification system includes real-time unread counts via API endpoints
- All forms use Flux components for consistent styling and validation display
- Use `nix develop -c <command>` prefix for all development commands