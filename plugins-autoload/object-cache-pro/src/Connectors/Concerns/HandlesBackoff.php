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

namespace RedisCachePro\Connectors\Concerns;

use RedisCachePro\Configuration\Configuration;

trait HandlesBackoff
{
    /**
     * Returns the delay in milliseconds before the next attempt.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  int  $attempt
     * @param  int  $previousDelay
     * @return int
     */
    public static function nextDelay(Configuration $config, int $attempt, int $previousDelay)
    {
        $random_range = function ($min, $max) {
            return $max < $min ? mt_rand($max, $min) : mt_rand($min, $max);
        };

        if ($config->backoff === Configuration::BACKOFF_SMART) {
            $cap = $config->timeout * 1000;
            $base = $config->retry_interval;

            return (int) min($cap, $random_range($base, $previousDelay * 3));
        }

        if ($config->backoff === Configuration::BACKOFF_NONE) {
            return $config->retry_interval;
        }

        return ++$attempt * $config->retry_interval;
    }
}
