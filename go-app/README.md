# ConNix - Go Implementation

A GitHub-like repository management system built with Go, Templ, and Alpine.js.

## Features

- GitHub OAuth authentication
- Repository management (create, edit, delete, transfer)
- Organization management with member roles
- User following system
- Activity feeds
- Notification system
- Responsive UI with Tailwind CSS and Alpine.js

## Prerequisites

- Go 1.22 or higher
- GitHub OAuth application credentials

## Setup

1. Clone the repository and navigate to the go-app directory:
```bash
cd go-app
```

2. Install dependencies:
```bash
make init
```

3. Configure your environment:
```bash
cp .env.example .env
# Edit .env with your GitHub OAuth credentials
```

4. Create a GitHub OAuth App:
   - Go to GitHub Settings > Developer settings > OAuth Apps
   - Create a new OAuth App
   - Set Authorization callback URL to: `http://localhost:8000/auth/github/callback`
   - Copy the Client ID and Client Secret to your .env file

## Development

Run the development server with hot reload:
```bash
make dev
```

Or run without hot reload:
```bash
make run
```

## Building

Build the application:
```bash
make build
```

## Database Migrations

Create a new migration:
```bash
make migrate-create
```

Migrations are automatically applied on startup.

## Project Structure

```
go-app/
├── cmd/server/          # Application entry point
├── internal/
│   ├── auth/           # Authentication logic
│   ├── database/       # Database connection and migrations
│   ├── handlers/       # HTTP handlers
│   ├── middleware/     # HTTP middleware
│   ├── models/         # Data models
│   └── templates/      # Templ templates
├── migrations/         # SQL migrations
├── static/            # Static assets (CSS, JS)
└── Makefile          # Build commands
```

## Technologies

- **Go** - Backend language
- **Templ** - Type-safe HTML templating
- **Alpine.js** - Lightweight JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **SQLite** - Database (configurable for production)
- **Gorilla Mux** - HTTP router
- **Goth** - OAuth authentication

## Next Steps

The following features are partially implemented and need completion:

1. Repository CRUD operations
2. Organization management
3. User following system
4. Activity feed
5. Notifications
6. Repository transfer functionality
7. Search functionality
8. User settings pages

Each handler in `internal/handlers/handlers.go` marked as "Not implemented" needs to be completed with the corresponding business logic and templates.