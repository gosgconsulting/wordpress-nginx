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

// Clear WordPress cookies when host changes (avoid redirect loops after domain switch)
$__currentHost = $_SERVER['HTTP_HOST'] ?? '';
$__lastHost = $_COOKIE['__wp_last_host'] ?? '';
if ($__currentHost) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if ($__lastHost && strcasecmp($__lastHost, $__currentHost) !== 0) {
        foreach (array_keys($_COOKIE) as $ck) {
            if (preg_match('/^(wordpress_logged_in|wordpress_sec|wordpress_test_cookie|wp-settings-\d+|wp-settings-time-\d+|comment_author)/', $ck)) {
                @setcookie($ck, '', time() - 3600, '/', '', $isSecure, true);
            }
        }
    }
    @setcookie('__wp_last_host', $__currentHost, time() + 86400 * 30, '/', '', $isSecure, false);
}

// Optional: allow additional config via env early (so user-defined defines take precedence)
$__extraCfgEarly = getenv('WP_EXTRA_CONFIG') ?: getenv('WORDPRESS_CONFIG_EXTRA');
if ($__extraCfgEarly) {
    @eval($__extraCfgEarly);
}

// Derive WP URLs robustly to avoid leaking internal ports (e.g., :8080)
// Modes:
// - WP_URL_MODE=auto → build from request Host/Scheme (handles multiple domains)
// - Otherwise: explicit WP_HOME/WP_SITEURL → WORDPRESS_SITE_URL → RAILWAY_PUBLIC_DOMAIN (https)
if (!defined('WP_HOME') || !defined('WP_SITEURL')) {
    $urlMode = getenv('WP_URL_MODE') ?: '';
    if (strtolower($urlMode) === 'auto') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host) {
            // Strip port unless explicitly allowed
            if (!getenv('WP_ALLOW_PORT_IN_URL')) {
                $host = preg_replace('#:(\\d{2,5})$#', '', $host);
            }
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            $scheme = $isHttps ? 'https' : 'http';
            $finalUrl = $scheme . '://' . $host;
            if (!defined('WP_HOME')) {
                define('WP_HOME', $finalUrl);
            }
            if (!defined('WP_SITEURL')) {
                define('WP_SITEURL', $finalUrl);
            }
        }
    } else {
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
}

// Derive DB_* constants from common platform variables if not explicitly provided
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
    // Prefer explicit DB_* envs
    $dbHost = getenv('DB_HOST');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPass = getenv('DB_PASSWORD');

    // Fallback to WORDPRESS_DB_* (used by some platforms)
    if (!$dbHost) { $dbHost = getenv('WORDPRESS_DB_HOST'); }
    if (!$dbName) { $dbName = getenv('WORDPRESS_DB_NAME'); }
    if (!$dbUser) { $dbUser = getenv('WORDPRESS_DB_USER'); }
    if (!$dbPass) { $dbPass = getenv('WORDPRESS_DB_PASSWORD'); }

    // Fallback to MariaDB/Mysql common variable names
    if (!$dbHost) {
        $mariadbHost = getenv('MARIADB_HOST') ?: getenv('MARIADB_PRIVATE_HOST') ?: getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '';
        $mariadbPort = getenv('MARIADB_PORT') ?: getenv('MARIADB_PRIVATE_PORT') ?: getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '';
        if ($mariadbHost) {
            $dbHost = $mariadbHost . ($mariadbPort ? ':' . $mariadbPort : '');
        }
    }
    if (!$dbName) { $dbName = getenv('MARIADB_DATABASE') ?: getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: ''; }
    if (!$dbUser) { $dbUser = getenv('MARIADB_USER') ?: getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: ''; }
    if (!$dbPass) { $dbPass = getenv('MARIADB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: ''; }

    // Apply if available
    if ($dbHost && $dbName && $dbUser) {
        if (!defined('DB_HOST')) { define('DB_HOST', $dbHost); }
        if (!defined('DB_NAME')) { define('DB_NAME', $dbName); }
        if (!defined('DB_USER')) { define('DB_USER', $dbUser); }
        if (!defined('DB_PASSWORD')) { define('DB_PASSWORD', $dbPass); }
    } else {
        // URL-based fallbacks
        $databaseUrl = getenv('DATABASE_URL') ?: getenv('MARIADB_URL') ?: getenv('MARIADB_PRIVATE_URL') ?: getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL');
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
                    if (!defined('DB_NAME')) { define('DB_NAME', $db); }
                    if (!defined('DB_USER')) { define('DB_USER', $user); }
                    if (!defined('DB_PASSWORD')) { define('DB_PASSWORD', $pass); }
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
