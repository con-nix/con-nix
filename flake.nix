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
            # PHP formatting
            php-cs-fixer = {
              enable = true;
              includes = ["*.php"];
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
          mezzio-skeleton = {
            type = "app";
            program = lib.getExe (
              pkgs.writeShellApplication {
                name = "mezzio-skeleton-demo";

                runtimeInputs = [php];

                text = ''
                  ${lib.getExe php} -S 0.0.0.0:8080 -t ${self'.packages.mezzio}/share/php/${self'.packages.mezzio.pname}/public/
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
