#!/bin/bash

# terminate on errors
set -e

# Configure Nginx to listen on the platform-provided port (e.g., Railway $PORT)
PORT_TO_USE="${PORT:-80}"
# Replace common defaults first
sed -i "s/listen 80 default_server;/listen ${PORT_TO_USE} default_server;/" /etc/nginx/nginx.conf || true
sed -i "s/listen \[::\]:80 default_server;/listen [::]:${PORT_TO_USE} default_server;/" /etc/nginx/nginx.conf || true
# If config already had a different port, replace any existing one
sed -i "s/listen \[::\]:[^;]* default_server;/listen [::]:${PORT_TO_USE} default_server;/" /etc/nginx/nginx.conf || true
sed -i "s/listen [0-9][0-9]* default_server;/listen ${PORT_TO_USE} default_server;/" /etc/nginx/nginx.conf || true

# Check if volume is empty
if [ ! "$(ls -A "/var/www/wp-content" 2>/dev/null)" ]; then
    echo 'Setting up wp-content volume'
    # Copy wp-content from Wordpress src to volume
    cp -r /usr/src/wordpress/wp-content /var/www/
    chown -R nobody:nobody /var/www
fi
# Check if wp-secrets.php exists
if ! [ -f "/var/www/wp-content/wp-secrets.php" ]; then
    echo '<?php' > /var/www/wp-content/wp-secrets.php
    # Check that secrets environment variables are not set
    if [ ! $AUTH_KEY ] \
    && [ ! $SECURE_AUTH_KEY ] \
    && [ ! $LOGGED_IN_KEY ] \
    && [ ! $NONCE_KEY ] \
    && [ ! $AUTH_SALT ] \
    && [ ! $SECURE_AUTH_SALT ] \
    && [ ! $LOGGED_IN_SALT ] \
    && [ ! $NONCE_SALT ]; then
        echo "Generating wp-secrets.php"
        # Generate secrets
        curl -f https://api.wordpress.org/secret-key/1.1/salt/ >> /var/www/wp-content/wp-secrets.php
    fi
fi

# Ensure wp-content is always writable (plugins/themes/uploads/upgrade)
umask 0002
mkdir -p /var/www/wp-content/{uploads,plugins,themes,upgrade} || true
chown -R nobody:nobody /var/www/wp-content || true
find /var/www/wp-content -type d -exec chmod 775 {} \; || true
find /var/www/wp-content -type f -exec chmod 664 {} \; || true

# Restore missing default themes if the volume lost them
for THEME in twentytwentyfive twentytwentyfour twentytwentythree; do
  if [ -d "/usr/src/wordpress/wp-content/themes/${THEME}" ] && [ ! -d "/var/www/wp-content/themes/${THEME}" ]; then
    cp -r "/usr/src/wordpress/wp-content/themes/${THEME}" "/var/www/wp-content/themes/" || true
    chown -R nobody:nobody "/var/www/wp-content/themes/${THEME}" || true
  fi
done

# Auto-install/activate plugins from image if requested
# Accept: yes/true/1/on (case-insensitive). Any other value (no/false/0/off/empty) disables.
_auto_plugins_flag="$(printf '%s' "${WP_AUTO_PLUGINS:-}" | tr '[:upper:]' '[:lower:]')"
if [ "$_auto_plugins_flag" = "yes" ] || [ "$_auto_plugins_flag" = "true" ] || [ "$_auto_plugins_flag" = "1" ] || [ "$_auto_plugins_flag" = "on" ]; then
  # Source directory: root plugins-autoload; ignore optional subfolder 'library'
  SRC_BASE="/usr/src/wordpress/wp-content/plugins-autoload"
  SRC_DIR="$SRC_BASE"
  DEST_DIR="/var/www/wp-content/plugins"
  if [ -d "$SRC_DIR" ]; then
    mkdir -p "$DEST_DIR"
    # Copy unpacked plugin folders
    find "$SRC_DIR" -maxdepth 1 -mindepth 1 -type d -printf '%f\n' 2>/dev/null | while read -r DIRNAME; do
      if [ "${DIRNAME,,}" = "library" ]; then continue; fi
      cp -r "$SRC_DIR/$DIRNAME" "$DEST_DIR/" 2>/dev/null || true
    done
    # Unzip any plugin archives (*.zip)
    find "$SRC_DIR" -maxdepth 1 -mindepth 1 -type f -name '*.zip' -printf '%f\n' 2>/dev/null | while read -r ZIPNAME; do
      unzip -o "$SRC_DIR/$ZIPNAME" -d "$DEST_DIR" >/dev/null 2>&1 || true
    done
    chown -R nobody:nobody "$DEST_DIR" || true
    # Activate only plugins provided in plugins-autoload (dirs or zip basenames)
    if [ -x /usr/local/bin/wp ] && /usr/local/bin/wp --path=/usr/src/wordpress core is-installed >/dev/null 2>&1; then
      AUTOPLUGS=""
      for DIRNAME in $(find "$SRC_DIR" -maxdepth 1 -mindepth 1 -type d -printf '%f\n' 2>/dev/null); do
        if [ "${DIRNAME,,}" != "library" ]; then AUTOPLUGS="$AUTOPLUGS $DIRNAME"; fi
      done
      for ZIPNAME in $(find "$SRC_DIR" -maxdepth 1 -mindepth 1 -type f -name '*.zip' -printf '%f\n' 2>/dev/null); do AUTOPLUGS="$AUTOPLUGS ${ZIPNAME%.zip}"; done
      for PLUG in $AUTOPLUGS; do
        /usr/local/bin/wp --path=/usr/src/wordpress plugin activate "$PLUG" >/dev/null 2>&1 || true
      done
    fi
  fi
fi

# If active theme directory is missing, activate a safe default via WP-CLI when possible
if [ -x /usr/local/bin/wp ] && /usr/local/bin/wp --path=/usr/src/wordpress core is-installed >/dev/null 2>&1; then
  ACTIVE_SLUG=$(/usr/local/bin/wp --path=/usr/src/wordpress option get stylesheet 2>/dev/null || true)
  if [ -n "$ACTIVE_SLUG" ] && [ ! -d "/var/www/wp-content/themes/${ACTIVE_SLUG}" ]; then
    for CANDIDATE in twentytwentyfive twentytwentyfour twentytwentythree; do
      if [ -d "/var/www/wp-content/themes/${CANDIDATE}" ]; then
        /usr/local/bin/wp --path=/usr/src/wordpress theme activate "${CANDIDATE}" >/dev/null 2>&1 || true
        break
      fi
    done
  fi
fi

# Ensure Nginx cache directories exist and are writable
mkdir -p /var/cache/nginx/fastcgi /var/cache/nginx/fastcgi_long || true
chown -R nginx:nginx /var/cache/nginx || true

exec "$@"
