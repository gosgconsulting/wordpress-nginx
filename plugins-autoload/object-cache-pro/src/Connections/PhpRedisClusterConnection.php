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

use RedisCachePro\Clients\PhpRedisCluster;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connectors\PhpRedisConnector;

/**
 * Distributed systems are hard.
 *
 * @mixin \RedisCachePro\Clients\PhpRedisCluster
 */
class PhpRedisClusterConnection extends PhpRedisConnection
{
    use Concerns\ClusterConnection;

    /**
     * The Redis cluster instance.
     *
     * @var \RedisCachePro\Clients\PhpRedisCluster
     */
    protected $client;

    /**
     * Create a new PhpRedis cluster connection.
     *
     * @param  \RedisCachePro\Clients\PhpRedisCluster  $client
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(PhpRedisCluster $client, Configuration $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->log = $this->config->logger;

        $this->setBackoff();
        $this->setSerializer();
        $this->setCompression();

        if (PhpRedisConnector::supports('get-meta')) {
            $this->supportsGetWithMeta = true;
        }
    }
}
