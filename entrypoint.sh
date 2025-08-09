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
exec "$@"
