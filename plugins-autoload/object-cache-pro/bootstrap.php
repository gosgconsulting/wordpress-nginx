<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro;

use RedisCachePro\Configuration\Configuration;

defined('ABSPATH') || exit;

spl_autoload_register(function ($fqcn) {
    if (strpos($fqcn, 'RedisCachePro\\') === 0) {
        require_once str_replace(['\\', 'RedisCachePro/'], ['/', __DIR__ . '/src/'], $fqcn) . '.php';
    }
});

(function ($config) {
    if (defined('WP_REDIS_CONFIG') || empty($config)) {
        return;
    }

    $config = json_decode((string) $config, true);
    $error = json_last_error();

    if ($error !== JSON_ERROR_NONE || ! is_array($config)) {
        log('warning', sprintf(
            'Unable to decode `OBJECTCACHE_CONFIG` environment variable (%s)',
            json_last_error_msg()
        ));

        return;
    }

    $array_replace_recursive = function ($current, $override) use (&$array_replace_recursive) {
        foreach ($override as $key => $value) {
            if (array_key_exists($key, $current) && is_array($current[$key]) && $current[$key] !== array_values($current[$key])) {
                $current[$key] = $array_replace_recursive($current[$key], $value);
            } else {
                $current[$key] = $value;
            }
        }

        return $current;
    };

    $array_merge_recursive = function ($current, $merge) use (&$array_merge_recursive) {
        foreach ($merge as $key => $value) {
            if (! array_key_exists($key, $current) || ! is_array($current[$key])) {
                $current[$key] = $value;
            } elseif ($current[$key] === array_values($current[$key])) {
                $current[$key] = array_merge($current[$key], (array) $value);
            } else {
                $current[$key] = $array_merge_recursive($current[$key], $value);
            }
        }

        return $current;
    };

    if (defined('OBJECTCACHE_OVERRIDE')) {
        $config = $array_replace_recursive($config, OBJECTCACHE_OVERRIDE);
    } elseif (defined('OBJECTCACHE_MERGE')) {
        $config = $array_merge_recursive($config, OBJECTCACHE_MERGE);
    }

    define('WP_REDIS_CONFIG', $config);
})(getenv('OBJECTCACHE_CONFIG'));

if (! function_exists(__NAMESPACE__ . '\log')) :
    /**
     * Log a message to `error_log()` and try to respect the configured log levels.
     *
     * @param  string  $level
     * @param  string  $message
     * @return void
     */
    function log($level, $message)
    {
        $config = Configuration::safelyFrom(
            defined('WP_REDIS_CONFIG') ? WP_REDIS_CONFIG : []
        );

        if ($config->log_levels && ! \in_array($level, $config->log_levels)) {
            return;
        }

        \error_log("objectcache.{$level}: {$message}");
    }
endif;
