.PHONY: build run dev test clean templ migrate

# Build the application
build: templ
	go build -o con-nix cmd/server/main.go

# Run the application
run: build
	./con-nix

# Development with hot reload using Air
dev:
	air

# Generate Templ files
templ:
	templ generate

# Run tests
test:
	go test ./...

# Clean build artifacts
clean:
	rm -f con-nix
	rm -f database.db

# Install dependencies
deps:
	go mod download
	go install github.com/a-h/templ/cmd/templ@latest
	go install github.com/cosmtrek/air@latest

# Create a new migration
migrate-create:
	@read -p "Enter migration name: " name; \
	timestamp=$$(date +%Y%m%d%H%M%S); \
	touch migrations/$${timestamp}_$${name}.up.sql; \
	touch migrations/$${timestamp}_$${name}.down.sql; \
	echo "Created migrations/$${timestamp}_$${name}.up.sql and migrations/$${timestamp}_$${name}.down.sql"

# Initialize the project
init: deps
	cp .env.example .env
	@echo "Please update .env with your GitHub OAuth credentials"