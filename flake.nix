{
  description = "Example flake for PHP development";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    nix-shell.url = "github:loophp/nix-shell";
    systems.url = "github:nix-systems/default";
    treefmt-nix = {
      url = "github:numtide/treefmt-nix";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs = inputs @ {
    self,
    flake-parts,
    systems,
    treefmt-nix,
    ...
  }:
    flake-parts.lib.mkFlake {inherit inputs;} {
      systems = import systems;

      imports = [
        treefmt-nix.flakeModule
      ];

      perSystem = {
        config,
        self',
        inputs',
        pkgs,
        system,
        lib,
        ...
      }: let
        php = pkgs.api.buildPhpFromComposer {
          src = inputs.self;
          php = pkgs.php84; # Change to php56, php70, ..., php81, php82, php83 etc.
        };
      in {
        # Configure treefmt
        treefmt = {
          projectRootFile = "flake.nix";
          programs = {
            prettier = {
              enable = true;
              includes = ["*.js" "*.ts" "*.json" "*.md" "*.css" "*.html" "*.vue"];
            };
            alejandra = {
              enable = true;
            };
          };
        };
        _module.args.pkgs = import self.inputs.nixpkgs {
          inherit system;
          overlays = [inputs.nix-shell.overlays.default];
          config.allowUnfree = true;
        };

        devShells.default = pkgs.mkShellNoCC {
          name = "php-devshell";
          buildInputs = [
            pkgs.nixd
            pkgs.laravel
            php
            php.packages.composer
            php.packages.phpstan
            php.packages.psalm
            pkgs.phpunit
            self'.packages.satis
                            pkgs.phpactor
            pkgs.bun
            pkgs.nodePackages.prettier
            pkgs.chromium # Added for Laravel Dusk
            pkgs.glib # Required by ChromeDriver
            pkgs.chromedriver # Use Nix's ChromeDriver instead of Laravel's
            pkgs.nss # Required for libnss3.so
            pkgs.sqlite
            pkgs.go
                            pkgs.frankenphp
            pkgs.tailwindcss-language-server
                            pkgs.htmx-lsp
          ];

          # Setup a suitable environment for Laravel Dusk
          shellHook = ''
            export DUSK_CHROME_BINARY=${pkgs.chromium}/bin/chromium
            export DUSK_DRIVER_URL=http://localhost:9515
          '';
        };

        checks = {
          inherit (self'.packages) drupal satis symfony-demo laravel;
        };

        packages = {
          default = pkgs.writeShellApplication {
            name = "con-nix-build";
            
            runtimeInputs = [
              php
              php.packages.composer
              pkgs.bun
              pkgs.sqlite
              pkgs.frankenphp
            ];
            
            text = ''
              set -e
              
              echo "Building Laravel application with Nix..."
              
              # Create build directory
              BUILD_DIR="$HOME/.cache/con-nix-build"
              APP_DIR="$BUILD_DIR/app"
              
              # Remove old build, handling read-only files
              if [ -d "$BUILD_DIR" ]; then
                chmod -R u+w "$BUILD_DIR" 2>/dev/null || true
                rm -rf "$BUILD_DIR"
              fi
              mkdir -p "$APP_DIR"
              
              # Copy source files (including dotfiles)
              shopt -s dotglob
              cp -r ${inputs.self}/* "$APP_DIR/"
              cd "$APP_DIR"
              
              # Make all files writable (since they come from Nix store)
              chmod -R u+w .
              
              # Create required directories first with proper permissions
              mkdir -p storage/framework/{cache,sessions,testing,views}
              mkdir -p storage/logs
              mkdir -p storage/app/{private,public}
              mkdir -p bootstrap/cache
              chmod -R 755 storage bootstrap/cache
              
              echo "Installing PHP dependencies..."
              composer install --no-dev --optimize-autoloader --no-interaction
              
              echo "Setting up production environment..."
              cp .env.example .env
              sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
              sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
              sed -i 's|DB_DATABASE=.*|DB_DATABASE=database/database.sqlite|' .env
              
              echo "Generating application key..."
              php artisan key:generate --force
              
              echo "Installing JS dependencies and building assets..."
              bun install
              bun run build
              
              echo "Creating database and running migrations..."
              touch database/database.sqlite
              chmod 644 database/database.sqlite
              php artisan migrate --force --no-interaction
              
              echo "Caching configuration for production..."
              php artisan config:cache
              php artisan route:cache
              php artisan view:cache
              
              # Final permission setting
              chmod -R 755 storage bootstrap/cache
              
              echo "Build completed successfully!"
              echo "Application built in: $APP_DIR"
              echo ""
              echo "To run the application:"
              echo "  nix run .#serve"
              echo ""
              echo "Or build and serve in one command:"
              echo "  nix run .#default && nix run .#serve"
            '';
          };
          
          # Convenience package to serve the built application
          serve = pkgs.writeShellApplication {
            name = "con-nix-serve";
            
            runtimeInputs = [
              php
              pkgs.frankenphp
              pkgs.sqlite
            ];
            
            text = ''
              set -e
              
              BUILD_DIR="$HOME/.cache/con-nix-build/app"
              
              if [ ! -d "$BUILD_DIR" ]; then
                echo "Error: Application not built yet. Run 'nix run .#default' first to build."
                exit 1
              fi
              
              cd "$BUILD_DIR"
              
              # Ensure runtime directories are writable
              chmod -R 755 storage bootstrap/cache
              
              echo "Starting FrankenPHP server..."
              echo "Application will be available at http://localhost:8000"
              echo "Press Ctrl+C to stop the server"
              echo ""
              
              frankenphp php-server \
                --listen :8000 \
                --root public \
                --access-log
            '';
          };

          # Build application as a proper derivation for containerization
          app = pkgs.stdenv.mkDerivation {
            pname = "con-nix-laravel-app";
            version = "1.0.0";
            
            src = inputs.self;
            
            nativeBuildInputs = [
              php
              php.packages.composer
              pkgs.bun
              pkgs.sqlite
            ];
            
            configurePhase = ''
              runHook preConfigure
              
              # Make files writable
              chmod -R u+w .
              
              # Create required directories
              mkdir -p storage/framework/{cache,sessions,testing,views}
              mkdir -p storage/logs
              mkdir -p storage/app/{private,public}
              mkdir -p bootstrap/cache
              
              runHook postConfigure
            '';
            
            buildPhase = ''
              runHook preBuild
              
              # Install PHP dependencies
              composer install --no-dev --optimize-autoloader --no-interaction
              
              # Set up production environment
              cp .env.example .env
              sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
              sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
              sed -i 's|DB_DATABASE=.*|DB_DATABASE=/app/database/database.sqlite|' .env
              
              # Generate application key
              php artisan key:generate --force
              
              # Install JS dependencies and build assets
              bun install
              bun run build
              
              # Create database and run migrations
              touch database/database.sqlite
              chmod 644 database/database.sqlite
              php artisan migrate --force --no-interaction
              
              # Cache configuration
              php artisan config:cache
              php artisan route:cache
              php artisan view:cache
              
              runHook postBuild
            '';
            
            installPhase = ''
              runHook preInstall
              
              mkdir -p $out
              
              # Copy all application files
              cp -r . $out/
              
              # Set proper permissions
              chmod -R 755 $out/storage $out/bootstrap/cache
              chmod 644 $out/database/database.sqlite
              
              runHook postInstall
            '';
            
            meta = {
              description = "Laravel application built for containerization";
            };
          };

          # Docker container image using Nix
          docker = pkgs.dockerTools.buildLayeredImage {
            name = "con-nix-laravel";
            tag = "latest";
            
            contents = [
              php
              pkgs.frankenphp
              pkgs.sqlite
              pkgs.bash
              pkgs.coreutils
              pkgs.gnused
              pkgs.fakeNss
              # Use a script that builds the app at runtime
              (pkgs.writeShellApplication {
                name = "build-and-serve";
                runtimeInputs = [
                  php
                  php.packages.composer
                  pkgs.bun
                  pkgs.sqlite
                  pkgs.frankenphp
                ];
                text = ''
                  set -e
                  
                  # Only build if not already built
                  if [ ! -f /app/.built ]; then
                    echo "Building Laravel application..."
                    cd /app
                    
                    # Install PHP dependencies
                    composer install --no-dev --optimize-autoloader --no-interaction
                    
                    # Set up production environment
                    cp .env.example .env
                    sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
                    sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
                    sed -i 's|DB_DATABASE=.*|DB_DATABASE=/app/database/database.sqlite|' .env
                    
                    # Generate application key
                    php artisan key:generate --force
                    
                    # Install JS dependencies and build assets
                    bun install
                    bun run build
                    
                    # Create database and run migrations
                    touch database/database.sqlite
                    chmod 644 database/database.sqlite
                    php artisan migrate --force --no-interaction
                    
                    # Cache configuration
                    php artisan config:cache
                    php artisan route:cache
                    php artisan view:cache
                    
                    # Mark as built
                    touch /app/.built
                    
                    echo "Build completed!"
                  fi
                  
                  echo "Starting Laravel application with FrankenPHP..."
                  echo "Application will be available on port 8000"
                  
                  cd /app
                  exec frankenphp php-server \
                    --listen :8000 \
                    --root public \
                    --access-log
                '';
              })
            ];
            
            extraCommands = ''
              # Create app directory and copy source
              mkdir -p app
              # Copy all source files
              ${pkgs.rsync}/bin/rsync -av --exclude='.git' ${inputs.self}/ app/
              
              # Make files writable
              chmod -R u+w app
              
              # Create required directories
              mkdir -p app/storage/framework/{cache,sessions,testing,views}
              mkdir -p app/storage/logs
              mkdir -p app/storage/app/{private,public}
              mkdir -p app/bootstrap/cache
              mkdir -p tmp
              
              # Set proper permissions
              chmod -R 777 app/storage app/bootstrap/cache tmp
            '';
            
            config = {
              Cmd = ["build-and-serve"];
              ExposedPorts = {
                "8000/tcp" = {};
              };
              Env = [
                "APP_ENV=production"
                "APP_DEBUG=false"
                "HOME=/tmp"
              ];
              WorkingDir = "/app";
            };
          };

          satis = php.buildComposerProject {
            pname = "satis";
            version = "3.0.0-dev";

            src = pkgs.fetchFromGitHub {
              owner = "composer";
              repo = "satis";
              rev = "547552004cc8526baeda5bc85eb595542acd3536";
              hash = "sha256-69HUEIUrOOrBhqaFQkHl89EAklKIsuyM47n1MbU3ZgY=";
            };

            vendorHash = "sha256-SAN77IVrxQQz4yuUexbSw0aXFhGR5DTXFFCj0bANYKw=";

            meta.mainProgram = "satis";
          };

          drupal = php.buildComposerProject {
            pname = "drupal";
            version = "11.1.7-dev";

            src = pkgs.fetchFromGitHub {
              owner = "drupal";
              repo = "drupal";
              rev = "20238e2d53337f20190f84d4976cf861219ca2f6";
              hash = "sha256-jf28r44VDP9MzShoJMFD+6xSUcKBRGYJ1/ruQ3nGTRE=";
            };

            vendorHash = "";
          };

          symfony-demo-image = pkgs.dockerTools.buildLayeredImage {
            name = self'.packages.symfony-demo.pname;
            tag = "latest";

            contents = let
              caddyFile = pkgs.writeText "Caddyfile" ''
                {
                    email youremail@domain.com
                }

                :80 {
                    root * /app/public
                    log
                    encode gzip
                    php_fastcgi 127.0.0.1:9000
                    file_server
                }

                :443 {
                    root * /app/public
                    log
                    encode gzip
                    php_fastcgi 127.0.0.1:9000
                    file_server
                    tls internal {
                        on_demand
                    }
                }
              '';
            in [
              php
              pkgs.caddy
              pkgs.fakeNss
              (pkgs.writeScriptBin "start-server" ''
                #!${pkgs.runtimeShell}
                php-fpm -D -y /etc/php-fpm.d/www.conf.default
                caddy run --adapter caddyfile --config ${caddyFile}
              '')
            ];

            extraCommands = ''
              ln -s ${self'.packages.symfony-demo}/share/php/${self'.packages.symfony-demo.pname}/ app
              mkdir -p tmp
              chmod -R 777 tmp
              cp ${self'.packages.symfony-demo}/share/php/${self'.packages.symfony-demo.pname}/data/database.sqlite tmp/database.sqlite
              chmod +w tmp/database.sqlite
            '';

            config = {
              Cmd = ["start-server"];
              ExposedPorts = {
                "80/tcp" = {};
                "443/tcp" = {};
              };
            };
          };

          laravel = php.buildComposerProject {
            pname = "laravel";
            version = "9.0.0-dev";

            src = pkgs.fetchFromGitHub {
              owner = "laravel";
              repo = "laravel";
              rev = "1c027454d9a1522b9e2ad86f41bb0b6980f2faf3";
              hash = "sha256-oX9ayffGwfQdU+wgB31pO8BseWnlTESsR/3rnJSBzEw=";
            };

            vendorHash = "";
          };

          symfony-demo = php.buildComposerProject {
            pname = "symfony-demo";
            version = "1.0.0";

            src = pkgs.fetchFromGitHub {
              owner = "symfony";
              repo = "demo";
              rev = "e8a754777bd400ecf87e8c6eeea8569d4846d357";
              hash = "sha256-ZG0O8O4X5t/GkAVKhcedd3P7WXYiZ0asMddX1XfUVR4=";
            };

            composerNoDev = false;
            composerNoPlugins = false;

            preInstall = ''
              ls -la
            '';

            vendorHash = "sha256-Nv9pRQJ2Iij1IxPNcCk732Q79FWB/ARJRvjPVVyLMEc=";
          };
        };

        apps = {
          default = {
            type = "app";
            program = lib.getExe self'.packages.default;
          };
          
          serve = {
            type = "app";
            program = lib.getExe self'.packages.serve;
          };
          
          docker-load = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "docker-load";
                
                runtimeInputs = [
                  pkgs.docker
                ];
                
                text = ''
                  set -e
                  
                  echo "Building Docker image with Nix..."
                  nix build .#docker
                  
                  echo "Loading image into Docker..."
                  docker load < result
                  
                  echo "Docker image loaded successfully!"
                  echo "Run with: docker run -p 8000:8000 con-nix-laravel:latest"
                '';
              }
            );
          };
          
          docker-run = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "docker-run";
                
                runtimeInputs = [
                  pkgs.docker
                ];
                
                text = ''
                  set -e
                  
                  echo "Building and loading Docker image..."
                  nix run .#docker-load
                  
                  echo "Starting container..."
                  echo "Application will be available at http://localhost:8000"
                  echo "Press Ctrl+C to stop the container"
                  
                  docker run --rm -p 8000:8000 con-nix-laravel:latest
                '';
              }
            );
          };


          symfony-demo = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "php-symfony-demo";

                runtimeInputs = [php];

                text = ''
                  APP_CACHE_DIR=$(mktemp -u)/cache
                  APP_LOG_DIR=$APP_CACHE_DIR/log
                  DATABASE_URL=sqlite:///$APP_CACHE_DIR/database.sqlite

                  export APP_CACHE_DIR
                  export APP_LOG_DIR
                  export DATABASE_URL

                  mkdir -p "$APP_CACHE_DIR"
                  mkdir -p "$APP_LOG_DIR"

                  cp -f ${self'.packages.symfony-demo}/share/php/symfony-demo/data/database.sqlite "$APP_CACHE_DIR"/database.sqlite
                  chmod +w "$APP_CACHE_DIR"/database.sqlite

                  ${lib.getExe pkgs.symfony-cli} serve --document-root ${self'.packages.symfony-demo}/share/php/symfony-demo/public --allow-http
                '';
              }
            );
          };

          # nix run .#satis -- --version
          satis = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "satis";

                text = ''
                  ${lib.getExe self'.packages.satis} "$@"
                '';
              }
            );
          };

          # nix run .#composer -- --version
          composer = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "composer";

                runtimeInputs = [
                  php
                  php.packages.composer
                ];

                text = ''
                  ${lib.getExe php.packages.composer} "$@"
                '';
              }
            );
          };

          # nix run .#grumphp -- --version
          grumphp = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "grumphp";

                runtimeInputs = [php];

                text = ''
                  ${lib.getExe php.packages.grumphp} "$@"
                '';
              }
            );
          };
        };
      };
    };
}
