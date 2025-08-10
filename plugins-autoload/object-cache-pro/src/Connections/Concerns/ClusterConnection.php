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

namespace RedisCachePro\Connections\Concerns;

use Generator;

use RedisCachePro\Connections\Transaction;

trait ClusterConnection
{
    /**
     * Execute pipelines as atomic `MULTI` transactions.
     *
     * @return object
     */
    public function pipeline()
    {
        return $this->multi();
    }

    /**
     * Hijack `multi()` calls to allow command logging.
     *
     * @param  ?int  $type
     * @return object
     */
    public function multi(?int $type = null)
    {
        return Transaction::multi($this);
    }

    /**
     * Yields all keys matching the given pattern.
     *
     * @param  string|null  $pattern
     * @param  int  $limit
     * @return \Generator<array<int, mixed>>
     */
    public function listKeys(?string $pattern = null, int $limit = 500): Generator
    {
        foreach ($this->client->_masters() as $primary) {
            $iterator = null;

            do {
                $keys = $this->client->scan($iterator, $primary, $pattern, $limit);

                if (! empty($keys)) {
                    yield $keys;
                }
            } while ($iterator > 0);
        }
    }

    /**
     * Pings first primary node.
     *
     * To ping a specific node, pass name of key as a string, or a hostname and port as array.
     *
     * @param  string|array<mixed>  $parameter
     * @return bool
     */
    public function ping($parameter = null)
    {
        if (\is_null($parameter)) {
            $primaries = $this->client->_masters();
            $parameter = \reset($primaries);
        }

        return $this->command('ping', [$parameter]);
    }

    /**
     * Fetches information from the first primary node.
     *
     * To fetch information from a specific node, pass the key or a host:port as array.
     *
     * @param  string|null  $section
     * @param  string|array<string>  $key
     * @return bool
     */
    public function info($section = null, $key = null)
    {
        $sections = [
            'server', 'clients', 'persistence',
            'threads', 'cpu', 'memory',
            'sentinel', 'cluster', 'replication',
            'modules', 'keyspace',
            'stats', 'errorstats', 'latencystats', 'commandstats',
            'all', 'default', 'everything',
        ];

        $validSection = \is_string($section)
            && \in_array(\strtolower($section), $sections);

        // support old `info($key_or_address)` signature
        if (\is_null($key) && (\is_array($section) || ! $validSection)) {
            $key = $section;
            $section = null;
        }

        if (\is_null($key)) {
            $primaries = $this->client->_masters();
            $key = \reset($primaries);
        }

        return $this->command('info', array_filter([$key, $section]));
    }

    /**
     * Fetches slowlog information from all primary nodes.
     *
     * @param  string  ...$parameters
     * @return mixed
     */
    public function slowlog(...$parameters)
    {
        $log = [];

        foreach ($this->client->_masters() as $primary) {
            $result = $this->rawCommand($primary, 'slowlog', ...$parameters);

            if ($result === false) {
                return false;
            }

            foreach ($result as $item) {
                $key = implode('|', array_slice($item[3], 0, 2));

                if (! array_key_exists($key, $log) || $log[$key][2] < $item[2]) {
                    $log[$key] = $item;
                }
            }
        }

        return array_values($log);
    }

    /**
     * Call `EVAL` script one key at a time and on all primaries when needed.
     *
     * @param  string  $script
     * @param  array<mixed>  $args
     * @param  int  $keys
     * @return mixed
     */
    public function eval(string $script, array $args = [], int $keys = 0)
    {
        if ($keys === 0) {
            // Will go to random primary
            return $this->command('eval', [$script, $args, 0]);
        }

        if ($keys === 1) {
            if (strpos($args[0], '{') === false) {
                // Must be run on all primaries
                return $this->evalWithoutHashTag($script, $args, 1);
            }

            // Will be called on the primary matching the hash-tag of the key
            return $this->command('eval', [$script, $args, 1]);
        }

        $results = [];

        foreach (array_slice($args, 0, $keys) as $key) {
            // Call this method recursively for each key
            $results[$key] = $this->eval($script, array_merge([$key], array_slice($args, $keys)), 1);
        }

        return $results;
    }

    /**
     * Call `EVAL` script on all primary nodes.
     *
     * @param  string  $script
     * @param  array<mixed>  $args
     * @param  int  $keys
     * @return mixed
     */
    protected function evalWithoutHashTag(string $script, array $args = [], int $keys = 0)
    {
        $results = [];
        $primaries = $this->client->_masters();

        foreach ($primaries as $primary) {
            $key = $this->randomKey($primary);

            if ($key) {
                $results[] = $this->command('eval', [$script, array_merge([$key], $args, ['use-argv']), 1]);
            }
        }

        return $results;
    }

    /**
     * Returns Redis cluster nodes.
     *
     * @return array<int, array<string, string|false>>
     */
    public function nodes()
    {
        $nodes = $this->rawCommand(
            $this->client->_masters()[0],
            'CLUSTER',
            'NODES'
        );

        $nodes = array_filter(explode("\n", $nodes));

        return array_map(function ($node) {
            $fields = explode(' ', $node);

            return [
                'address' => strstr($fields[1], '@', true),
                'flags' => str_replace(['myself,', 'myself'], '', $fields[2]),
            ];
        }, $nodes);
    }

    /**
     * Flush all nodes on the Redis cluster.
     *
     * @param  bool|null  $async
     * @return true
     */
    public function flushdb($async = null)
    {
        $useAsync = $async ?? $this->config->async_flush;

        foreach ($this->client->_masters() as $primary) {
            $useAsync
                ? $this->rawCommand($primary, 'flushdb', 'async')
                : $this->command('flushdb', [$primary]);
        }

        return true;
    }
}
