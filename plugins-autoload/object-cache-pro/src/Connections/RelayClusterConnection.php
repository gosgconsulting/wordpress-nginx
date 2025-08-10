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

namespace RedisCachePro\Connections;

use Relay\Relay;
use Relay\Cluster;

use RedisCachePro\Clients\RelayCluster;
use RedisCachePro\Connectors\RelayConnector;
use RedisCachePro\Configuration\Configuration;

/**
 * Distributed systems are hard.
 *
 * @mixin \RedisCachePro\Clients\RelayCluster
 */
class RelayClusterConnection extends RelayConnection
{
    use Concerns\ClusterConnection;

    /**
     * The Redis cluster instance.
     *
     * @var \RedisCachePro\Clients\RelayCluster
     */
    protected $client;

    /**
     * Create a new Relay cluster connection.
     *
     * @param  \RedisCachePro\Clients\RelayCluster  $client
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(RelayCluster $client, Configuration $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->log = $this->config->logger;

        $this->setBackoff();
        $this->setSerializer();
        $this->setCompression();

        if (RelayConnector::supports('get-meta')) {
            $this->supportsGetWithMeta = true;
        }
    }

    /**
     * Returns statistics about Relay.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return array<mixed>
     */
    public function stats()
    {
        return Relay::stats();
    }

    /**
     * Returns the number of keys cached in memory for the current connection.
     *
     * @return int|float
     */
    public function keysInMemory()
    {
        $stats = $this->memoize('stats');
        $endpoints = (array) $this->endpointId();

        $keys = 0;

        foreach ($stats['endpoints'] as $id => $data) {
            if (in_array($id, $endpoints)) {
                $keys += array_sum(array_map(function ($connection) {
                    return $connection['keys'][$this->config->database] ?? 0;
                }, $data['connections'] ?? [])) ?: 0;
            }
        }

        return $keys;
    }

    /**
     * Flushes the Relay memory for the current FPM pool if needed.
     *
     * @param  float  $flushRequestedAt
     * @param  int    $database
     * @return void
     */
    public function maybeFlushRelayMemory($flushRequestedAt, $database)
    {
        $endpoints = (array) $this->endpointId();

        foreach ($endpoints as $endpoint) {
            $flushedAt = $this->lastMemoryFlush($endpoint ?: null, $database);

            if ($flushedAt < $flushRequestedAt) {
                $this->flushMemory($endpoint ?: null, $database);
            }
        }
    }
}
