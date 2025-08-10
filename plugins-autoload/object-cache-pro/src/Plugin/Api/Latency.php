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

namespace RedisCachePro\Plugin\Api;

use Throwable;

use WP_REST_Server;

use RedisCachePro\Configuration\Configuration;
use RedisCachePro\ObjectCaches\ObjectCacheInterface;

class Latency extends Controller
{
    /**
     * The resource name of this controller's route.
     *
     * @var string
     */
    protected $resource_name = 'latency';

    /**
     * Register all REST API routes.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, "/{$this->resource_name}", [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Returns the REST API response for the request.
     *
     * @param  \WP_REST_Request  $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items($request)
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCacheInterface) {
            return $this->notSupportedError();
        }

        if (! $wp_object_cache->connection()) {
            return $this->notConnectedError();
        }

        $config = $wp_object_cache->config();

        if ($config->cluster) {
            return rest_ensure_response($this->pingCluster());
        }

        if ($config->sentinels) {
            return rest_ensure_response($this->pingSentinelServers());
        }

        if ($config->servers) {
            return rest_ensure_response($this->pingReplicatedServers());
        }

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response([$this->ping($config)]);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the latency in microseconds of a single Redis node.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return array<string, mixed>
     */
    protected function ping(Configuration $config)
    {
        try {
            $connection = $config->connector::connectToInstance($config);

            $start = $this->now();
            $connection->ping();
            $time = $this->now() - $start;
            $connection->close();

            $result = [
                'url' => $this->formatHost($config),
                'latency' => round($time * 1000000),
            ];
        } catch (Throwable $exception) {
            $result = [
                'url' => $this->formatHost($config),
                'error' => $exception->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * Returns the latency in microseconds of a Redis Sentinel node.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  \RedisSentinel  $sentinel
     * @return array<string, mixed>
     */
    protected function pingSentinel(Configuration $config, $sentinel)
    {
        try {
            $start = $this->now();
            $sentinel->ping();
            $time = $this->now() - $start;

            $result = [
                'url' => $this->formatHost($config),
                'latency' => round($time * 1000000),
            ];
        } catch (Throwable $exception) {
            $result = [
                'url' => $this->formatHost($config),
                'error' => $exception->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * Measure the latency of cluster connection.
     *
     * @return array<mixed>
     */
    protected function pingCluster()
    {
        global $wp_object_cache;

        $config = clone $wp_object_cache->config();
        $nodes = $wp_object_cache->connection()->nodes();

        $results = [];

        usort($nodes, function ($a, $b) {
            return $a['address'] <=> $b['address'];
        });

        foreach ($nodes as $node) {
            $host = explode(':', $node['address']);

            $config->setHost(
                count($host) === 3
                    ? implode(':', [$host[0], $host[1]])
                    : $host[0]
            );

            $config->setPort(
                count($host) === 3
                    ? (int) $host[2]
                    : (int) $host[1]
            );

            $flags = str_replace(['master', 'slave'], ['primary', 'replica'], $node['flags']);

            $results[] = array_merge(
                $this->ping($config),
                ['note' => $flags]
            );
        }

        return $results;
    }

    /**
     * Measure the node latency of Sentinel connection.
     *
     * @return array<mixed>
     */
    protected function pingSentinelServers()
    {
        global $wp_object_cache;

        $connection = $wp_object_cache->connection();

        $config = clone $wp_object_cache->config();
        $config->setUrl($connection->sentinelUrl());

        $sentinel = $this->pingSentinel($config, $connection->sentinel());

        return array_merge(
            [$sentinel],
            $this->pingReplicatedServers()
        );
    }

    /**
     * Measure the node latency of replicated connection.
     *
     * @return array<mixed>
     */
    protected function pingReplicatedServers()
    {
        global $wp_object_cache;

        $connection = $wp_object_cache->connection();

        $nodes = array_merge(
            [$connection->primary()],
            $connection->replicas()
        );

        $results = [];

        foreach ($nodes as $node) {
            $config = clone $wp_object_cache->config();
            $config->setHost($node->getHost());
            $config->setPort($node->getPort());

            $results[] = $this->ping($config);
        }

        return $results;
    }

    /**
     * Returns a readable URL for the given config.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return string
     */
    protected function formatHost($config)
    {
        return sprintf(
            '%s://%s:%s',
            $config->scheme,
            $config->host,
            $config->port
        );
    }

    /**
     * Returns the system's current time in microseconds.
     * Will use high resolution time when available.
     *
     * @return float
     */
    protected function now()
    {
        static $supportsHRTime;

        if (\is_null($supportsHRTime)) {
            $supportsHRTime = \function_exists('hrtime');
        }

        return $supportsHRTime
            ? \hrtime(true) * 1e-9 // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.hrtimeFound
            : \microtime(true);
    }

    /**
     * Retrieves the endpoint's schema, conforming to JSON Schema.
     *
     * @return array<string, mixed>
     */
    public function get_item_schema()
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'objectcache_latency',
            'type' => 'object',
            'properties' => [
                'url' => [
                    'description' => 'The pinged Redis node.',
                    'type' => 'string',
                ],
                'latency' => [
                    'description' => 'The measured latency in microseconds.',
                    'type' => 'integer',
                ],
                'error' => [
                    'description' => 'The error message, if any.',
                    'type' => 'string',
                ],
            ],
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema($this->schema);
    }
}
