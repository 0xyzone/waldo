#!/bin/bash

# ==============================================================================
# Laravel Reverb Watchdog for cPanel
# ==============================================================================
# Usage:
# 1. Update the PHP_BIN and APP_DIR paths below if needed for your cPanel user.
# 2. In cPanel > Cron Jobs, add a cron to run every minute:
#    * * * * * /bin/bash /home/USERNAME/public_html/scripts/run_reverb.sh > /dev/null 2>&1
# ==============================================================================

# Automatically detect current script directory and project root
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

# Fallback or specific cPanel PHP 8.4 binary path (adjust if your host uses a different version)
PHP_BIN="$(which ea-php84 2>/dev/null || which ea-php83 2>/dev/null || which php 2>/dev/null || echo "php")"

LOG_FILE="$APP_DIR/storage/logs/reverb.log"

# Check if Reverb is already running
if pgrep -f "$APP_DIR/artisan reverb:start" > /dev/null 2>&1; then
    # Already running, nothing to do
    exit 0
fi

# Not running -> start in background with nohup
cd "$APP_DIR" || exit 1
nohup "$PHP_BIN" artisan reverb:start >> "$LOG_FILE" 2>&1 &

