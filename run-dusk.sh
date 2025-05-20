#!/usr/bin/env bash

# Ensure ChromeDriver is not already running
pkill -f "chromedriver" || true

# Set environment variables for Dusk
export DISPLAY=:0
export DUSK_DRIVER_URL=http://localhost:9515
export DUSK_CHROME_BINARY=$(which chromium)

# Find chromedriver from Nix
CHROMEDRIVER=$(which chromedriver)

if [ ! -x "$CHROMEDRIVER" ]; then
  echo "ChromeDriver not found! Make sure you're in the nix development environment."
  exit 1
fi

# Start ChromeDriver in the background 
"$CHROMEDRIVER" --port=9515 &
CHROMEDRIVER_PID=$!

# Give ChromeDriver time to start
sleep 2

# Run Dusk tests
php artisan dusk "$@"
RESULT=$?

# Kill ChromeDriver
kill $CHROMEDRIVER_PID

exit $RESULT