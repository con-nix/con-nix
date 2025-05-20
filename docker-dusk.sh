#!/usr/bin/env bash

# Create a temporary Docker container to run Laravel Dusk tests
docker run --rm -it \
  -v "$(pwd)":/app \
  -w /app \
  --network host \
  php:8.4-cli \
  bash -c "apt-get update && apt-get install -y wget unzip libzip-dev chromium && \
           docker-php-ext-install zip && \
           php artisan dusk:chrome-driver && \
           php artisan dusk"