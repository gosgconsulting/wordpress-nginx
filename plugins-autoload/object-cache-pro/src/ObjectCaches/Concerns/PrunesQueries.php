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

namespace RedisCachePro\ObjectCaches\Concerns;

/**
 * WordPress 6.3 introduced query cache groups that have no cleanup mechanism.
 *
 * By default the `queryttl` configuration option will expire all query cache
 * keys after 24 hours. However, if no query expiration is set this train will
 * attempt to prune (most) stale keys from the `*-queries` cache groups.
 */
trait PrunesQueries
{
    /**
     * The query groups and their corresponding data group.
     *
     * @var array<string, string>
     */
    protected $queryGroups = [
        'user-queries' => 'users',
        'post-queries' => 'posts',
        'comment-queries' => 'comment',
        'term-queries' => 'terms',
        'network-queries' => 'networks',
        'site-queries' => 'sites',
    ];

    /**
     * Returns the query groups.
     *
     * @return array<string, string>
     */
    public function queryGroups(): array
    {
        return $this->queryGroups;
    }

    /**
     * Prunes stale keys from `*-queries` cache groups.
     *
     * @param  array<string, string>  $lastChanged
     * @return array<string>
     */
    public function pruneQueries(array $lastChanged): array
    {
        $staleKeys = [];

        foreach ($lastChanged as $group => $timestamp) {
            $timestamp = $this->parseQueryTimestamp($timestamp);
        }

        foreach ($this->connection->listKeys('*-queries:*') as $keys) {
            foreach ($keys as $key) {
                $parts = array_reverse(explode(':', $key));
                $group = $parts[1];

                if (empty($lastChanged[$group])) {
                    continue;
                }

                $timestamp = $this->parseQueryTimestamp(
                    substr($parts[0], strpos($parts[0], '-0.') + 1),
                    strstr($parts[0], '-', true) ?: null,
                    $group
                );

                if ($timestamp && $timestamp < $lastChanged[$group]) {
                    $staleKeys[] = $key;
                }
            }
        }

        if (! empty($staleKeys)) {
            $pipeline = $this->connection->pipeline();
            $method = $this->config->async_flush ? 'unlink' : 'del';

            $chunks = array_chunk($staleKeys, 100);

            foreach ($chunks as $chunk) {
                $pipeline->{$method}($chunk);
            }

            $pipeline->exec();
        }

        return $staleKeys;
    }

    /**
     * Returns the timestamp as float from the given string.
     *
     * @param  string  $string
     * @param  ?string  $key
     * @param  ?string  $group
     * @return float|null
     */
    protected function parseQueryTimestamp($string, $key = null, $group = null)
    {
        $timestamps = substr_count($string, '0.');

        if ($timestamps === 1) {
            return round(array_sum(explode(' ', str_replace('-', ' ', $string))), 3);
        }

        if ($timestamps === 2) {
            $first = trim(substr($string, 0, strpos($string, '0.', 12) ?: null), '-');

            // `wp_query:<key>:<posts><terms>` (post-queries)
            if ($group === 'post-queries' && $key === 'wp_query') {
                return $this->parseQueryTimestamp($first);
            }

            // `adjacent_post:<key>:<posts><terms>` (post-queries)
            if ($group === 'post-queries' && $key === 'adjacent_post') {
                return $this->parseQueryTimestamp($first);
            }

            // `comment_feed:<key>:<comment>:<posts>` (comment-queries)
            if ($group === 'comment-queries' && $key === 'comment_feed') {
                return $this->parseQueryTimestamp($first);
            }

            // `get_users:<key>:<users><posts>` (user-queries)
            if ($group === 'user-queries' && $key === 'get_users') {
                return $this->parseQueryTimestamp($first);
            }
        }

        return null;
    }
}
