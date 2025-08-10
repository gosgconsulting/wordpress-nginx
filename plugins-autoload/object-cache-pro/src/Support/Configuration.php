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

namespace RedisCachePro\Support;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
class AnalyticsConfiguration
{
    /**
     * Whether to collect and display analytics.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Whether to restore analytics data after cache flushes.
     *
     * @var bool
     */
    public $persist;

    /**
     * The number of seconds to keep analytics before purging them.
     *
     * @var int
     */
    public $retention;

    /**
     * The sample rate for analytics in the range of 0 to 100.
     *
     * @var int|float
     */
    public $sample_rate;

    /**
     * Whether to print a HTML comment with non-sensitive metrics.
     *
     * @var bool
     */
    public $footnote;

    /**
     * The list of optional properties to include to analytics.
     *
     * @var array<string>
     */
    public $include;
}

class RelayConfiguration
{
    /**
     * Whether to use Relay's in-memory cache.
     *
     * @var bool
     */
    public $cache;

    /**
     * Whether to register Relay event listeners.
     *
     * @var bool
     */
    public $listeners;

    /**
     * Whether to enable client-side invalidation.
     *
     * @var bool
     */
    public $invalidations;

    /**
     * When set, only keys matching these patterns will be cached in Relay's in-memory cache, unless they match `relay.ignored`.
     *
     * @var ?array<string>
     */
    public $allowed;

    /**
     * Keys matching these patterns will not be cached in Relay's in-memory cache.
     *
     * @var ?array<string>
     */
    public $ignored;

    /**
     * The adaptive cache configuration.
     *
     * @var RelayAdaptiveConfiguration
     */
    public $adaptive;
}

class RelayAdaptiveConfiguration
{
    /**
     * Number of horizontal cells in the adaptive cache.
     * This should scale with the number of unique keys in the database.
     * Supported values: 512 - (2^31).
     *
     * @var int
     */
    public $width;

    /**
     * Number of vertical cells. Supported values: 1 - 8.
     *
     * @var int
     */
    public $depth;

    /**
     * Minimum number of calculated events (reads + writes) before
     * Relay will use the ratio to determine if a key should be cached.
     *
     * @var int
     */
    public $events;

    /**
     * Minimum ratio of reads to writes before Relay will cache a key.
     *
     * @var float
     */
    public $ratio;

    /**
     * The formula Relay will use to calculate the read/write ratio for each key.
     *
     * - `pure`: reads / writes
     * - `scaled`: (reads / writes)^(1.01 * log(1 + reads + writes))
     *
     * @var string
     */
    public $formula;
}
// phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses
