#!/bin/bash

# ==============================================================================
# Laravel Reverb Watchdog for cPanel
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"
LOG_FILE="$APP_DIR/storage/logs/reverb.log"

# Find proper PHP 8.3/8.4 binary on cPanel / EasyApache
PHP_BIN=""
for candidate in \
    "/usr/local/bin/ea-php84" \
    "/opt/cpanel/ea-php84/root/usr/bin/php" \
    "/usr/local/bin/ea-php83" \
    "/opt/cpanel/ea-php83/root/usr/bin/php" \
    "$(command -v ea-php84 2>/dev/null)" \
    "$(command -v ea-php83 2>/dev/null)" \
    "$(command -v php 2>/dev/null)" \
    "php"
do
    if [ -n "$candidate" ] && ([ -x "$candidate" ] || command -v "$candidate" >/dev/null 2>&1); then
        # Verify it meets minimum PHP version requirement (8.3+)
        PHP_VER=$("$candidate" -r 'echo PHP_VERSION_ID;' 2>/dev/null || echo 0)
        if [ "$PHP_VER" -ge 80300 ]; then
            PHP_BIN="$candidate"
            break
        fi
    fi
done

if [ -z "$PHP_BIN" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: Could not find PHP >= 8.3 binary. Please specify your cPanel PHP 8.4 path in scripts/run_reverb.sh." >> "$LOG_FILE"
    exit 1
fi

# Check if Reverb is already running
if pgrep -f "$APP_DIR/artisan reverb:start" > /dev/null 2>&1; then
    exit 0
fi

# Verify 'reverb:start' exists in artisan
if ! "$PHP_BIN" "$APP_DIR/artisan" list --raw 2>/dev/null | grep -E '^reverb:start\b' > /dev/null 2>&1; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: 'reverb:start' command not found in artisan." >> "$LOG_FILE"
    echo "Please run: 'cd $APP_DIR && composer install' to install laravel/reverb on your cPanel server." >> "$LOG_FILE"
    exit 1
fi

# Start Reverb in the background with nohup using absolute path
cd "$APP_DIR" || exit 1
nohup "$PHP_BIN" "$APP_DIR/artisan" reverb:start >> "$LOG_FILE" 2>&1 &
