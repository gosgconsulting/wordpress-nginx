<?php

define('WP_CONTENT_DIR', '/var/www/wp-content');
define('WP_AUTO_UPDATE_CORE', false);

$table_prefix  = getenv('TABLE_PREFIX') ?: 'wp_';

foreach ($_ENV as $key => $value) {
    $capitalized = strtoupper($key);
    if (!defined($capitalized)) {
        // Convert string boolean values to actual booleans
        if (in_array($value, ['true', 'false'])) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        define($capitalized, $value);
    }
}

// Derive WP URLs robustly to avoid leaking internal ports (e.g., :8080)
// Priority: explicit WP_HOME/WP_SITEURL → WORDPRESS_SITE_URL → RAILWAY_PUBLIC_DOMAIN (https)
if (!defined('WP_HOME') || !defined('WP_SITEURL')) {
    $siteFromEnv   = getenv('WP_HOME') ?: getenv('WP_SITEURL') ?: getenv('WORDPRESS_SITE_URL') ?: '';
    $railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN') ?: '';

    $finalUrl = $siteFromEnv;
    if (!$finalUrl && $railwayDomain) {
        // Ensure https scheme for public domain
        $host = preg_replace('#^https?://#i', '', $railwayDomain);
        $finalUrl = 'https://' . $host;
    }

    if ($finalUrl) {
        // Strip any port unless explicitly allowed
        if (!getenv('WP_ALLOW_PORT_IN_URL')) {
            $finalUrl = preg_replace('#:(\d{2,5})(?=/|$)#', '', $finalUrl);
        }
        // Ensure scheme is present
        if (!preg_match('#^https?://#i', $finalUrl)) {
            $finalUrl = 'https://' . $finalUrl;
        }
        if (!defined('WP_HOME')) {
            define('WP_HOME', $finalUrl);
        }
        if (!defined('WP_SITEURL')) {
            define('WP_SITEURL', $finalUrl);
        }
    }
}

// Derive DB_* constants from common platform variables if not explicitly provided
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
    $mysqlHost = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '';
    $mysqlPort = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '';
    $mysqlDb   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: '';
    $mysqlUser = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: '';
    $mysqlPass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

    if ($mysqlHost && $mysqlDb && $mysqlUser) {
        if (!defined('DB_HOST')) {
            $hostWithPort = $mysqlHost . ($mysqlPort ? ':' . $mysqlPort : '');
            define('DB_HOST', $hostWithPort);
        }
        if (!defined('DB_NAME')) {
            define('DB_NAME', $mysqlDb);
        }
        if (!defined('DB_USER')) {
            define('DB_USER', $mysqlUser);
        }
        if (!defined('DB_PASSWORD')) {
            define('DB_PASSWORD', $mysqlPass);
        }
    } else {
        $databaseUrl = getenv('DATABASE_URL') ?: getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL');
        if ($databaseUrl) {
            $parts = parse_url($databaseUrl);
            if ($parts && isset($parts['scheme']) && in_array(strtolower($parts['scheme']), ['mysql', 'mariadb'])) {
                $host = $parts['host'] ?? 'localhost';
                $port = isset($parts['port']) ? (string)$parts['port'] : '';
                $user = $parts['user'] ?? '';
                $pass = $parts['pass'] ?? '';
                $path = $parts['path'] ?? '';
                $db   = ltrim($path, '/');
                if ($host && $db && $user) {
                    if (!defined('DB_HOST')) {
                        $hostWithPort = $host . ($port ? ':' . $port : '');
                        define('DB_HOST', $hostWithPort);
                    }
                    if (!defined('DB_NAME')) {
                        define('DB_NAME', $db);
                    }
                    if (!defined('DB_USER')) {
                        define('DB_USER', $user);
                    }
                    if (!defined('DB_PASSWORD')) {
                        define('DB_PASSWORD', $pass);
                    }
                }
            }
        }
    }
}

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

require_once(ABSPATH . 'wp-secrets.php');
// Configure Object Cache Pro / Redis client using environment variables
if (!defined('WP_REDIS_CONFIG')) {
    define('WP_REDIS_CONFIG', [
        'token'        => getenv('WP_REDIS_LICENSE_TOKEN'),
        'client'       => 'phpredis',
        'host'         => getenv('WP_REDIS_HOST'),
        'port'         => (int) getenv('WP_REDIS_PORT'),
        'database'     => (int) (getenv('WP_REDIS_DATABASE') ?: 0),
        'username'     => getenv('WP_REDIS_USERNAME') ?: 'default',
        'password'     => getenv('WP_REDIS_PASSWORD') ?: null,
        'serializer'   => 'igbinary',
        'prefix'       => getenv('WP_CACHE_KEY_SALT'),
        'timeout'      => (float) (getenv('WP_REDIS_TIMEOUT') ?: 1.0),
        'read_timeout' => (float) (getenv('WP_REDIS_READ_TIMEOUT') ?: 1.0),
        'maxttl'       => (int) (getenv('WP_REDIS_MAXTTL') ?: 3600),
    ]);
}
require_once(ABSPATH . 'wp-settings.php');
