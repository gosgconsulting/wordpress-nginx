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

// Map WORDPRESS_SITE_URL env to the expected WP constants if provided
$derivedSiteUrl = getenv('WP_HOME') ?: getenv('WP_SITEURL') ?: getenv('WORDPRESS_SITE_URL');
if ($derivedSiteUrl) {
    if (!defined('WP_HOME')) {
        define('WP_HOME', $derivedSiteUrl);
    }
    if (!defined('WP_SITEURL')) {
        define('WP_SITEURL', $derivedSiteUrl);
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
    $redisHost        = getenv('WP_REDIS_HOST') ?: 'valkey';
    $redisPort        = (int) (getenv('WP_REDIS_PORT') ?: 6379);
    $redisDb          = (int) (getenv('WP_REDIS_DATABASE') ?: 0);
    $redisPassword    = getenv('WP_REDIS_PASSWORD') ?: null;
    $redisTimeout     = (float) (getenv('WP_REDIS_TIMEOUT') ?: 1.0);
    $redisReadTimeout = (float) (getenv('WP_REDIS_READ_TIMEOUT') ?: 1.0);
    $redisMaxTtl      = (int) (getenv('WP_REDIS_MAXTTL') ?: 3600);
    $redisPrefix      = getenv('WP_CACHE_KEY_SALT') ?: '';
    $redisToken       = getenv('WP_REDIS_LICENSE_TOKEN') ?: null;

    define('WP_REDIS_CONFIG', [
        'token'        => $redisToken,
        'client'       => 'phpredis',
        'host'         => $redisHost,
        'port'         => $redisPort,
        'database'     => $redisDb,
        'password'     => $redisPassword,
        'serializer'   => 'igbinary',
        'prefix'       => $redisPrefix,
        'timeout'      => $redisTimeout,
        'read_timeout' => $redisReadTimeout,
        'maxttl'       => $redisMaxTtl,
    ]);
}
require_once(ABSPATH . 'wp-settings.php');
