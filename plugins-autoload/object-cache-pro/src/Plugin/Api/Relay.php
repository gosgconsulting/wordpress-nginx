<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.v.
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

use WP_REST_Server;

use RedisCachePro\ObjectCaches\ObjectCacheInterface;

class Relay extends Controller
{
    /**
     * The resource name of this controller's route.
     *
     * @var string
     */
    protected $resource_name = 'relay';

    /**
     * Register all REST API routes.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, "/{$this->resource_name}/adaptive", [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_collection_params(),
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

        /** @var \RedisCachePro\Connections\ConnectionInterface $connection */
        $connection = $wp_object_cache->connection();

        if (! method_exists($connection, 'listKeys')) {
            return $this->notSupportedError();
        }

        $client = $connection->client();

        if (! method_exists($client, 'adaptiveCache')) {
            return $this->notSupportedError();
        }

        $adaptiveCache = $client->adaptiveCache();

        $stats = [];

        foreach ($connection->listKeys('*') as $keys) {
            foreach ($keys as $key) {
                /** @var array{reads: int, writes: int}|false $data */
                if (! $data = $adaptiveCache->stats($key)) {
                    continue;
                }

                if ($data['reads'] < 1) {
                    continue;
                }

                $stats[] = array_merge([
                    'key' => $key,
                    'reads' => $data['reads'],
                    'writes' => $data['writes'],
                    'ratio' => $data['reads'] / max($data['writes'], 1),
                ], $data);
            }
        }

        usort($stats, function ($a, $b) {
            if ($b['ratio'] === $a['ratio']) {
                return strcmp($b['key'], $a['key']);
            }

            return $b['ratio'] > $a['ratio'] ? 1 : -1;
        });

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response($stats);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Retrieves the endpoint's schema, conforming to JSON Schema.
     *
     * @return array<string, mixed>
     */
    public function get_item_schema()
    {
        $this->schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'objectcache_adaptive',
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'key' => [
                        'description' => 'The cache key.',
                        'type' => 'string',
                    ],
                    'reads' => [
                        'description' => 'The number of reads.',
                        'type' => 'integer',
                    ],
                    'writes' => [
                        'description' => 'The number of writes.',
                        'type' => 'integer',
                    ],
                    'ratio' => [
                        'description' => 'The ratio of reads to writes.',
                        'type' => 'number',
                    ],
                ],
            ],
        ];

        return $this->add_additional_fields_schema($this->schema);
    }
}
