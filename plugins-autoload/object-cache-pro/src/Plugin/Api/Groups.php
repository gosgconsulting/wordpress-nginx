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

use WP_Error;
use WP_REST_Server;

use RedisCachePro\ObjectCaches\ObjectCacheInterface;

class Groups extends Controller
{
    /**
     * The resource name of this controller's route.
     *
     * @var string
     */
    protected $resource_name = 'groups';

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
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::DELETABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Retrieves the query params for the collection.
     *
     * @return array<string, mixed>
     */
    public function get_collection_params()
    {
        $params = parent::get_collection_params();
        $params['context']['default'] = 'keys';

        return $params;
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

        $config = $wp_object_cache->config();
        $connection = $wp_object_cache->connection();

        if (! $connection) {
            return $this->notConnectedError();
        }

        if (
            ! method_exists($connection, 'listKeys') ||
            ! method_exists($connection, 'pipeline')
        ) {
            return $this->notSupportedError();
        }

        $prefix = $config->prefix;
        $pattern = is_null($prefix) ? null : "{$prefix}:*";

        $groups = [];

        if ($request->get_param('memory')) {
            $request->set_param('context', 'memory');

            if ($config->cluster) {
                return new WP_Error(
                    'objectcache_not_available',
                    'Memory usage is not supported on clustered connections.',
                    ['status' => 400]
                );
            }

            foreach ($connection->listKeys($pattern) as $keys) {
                $memoryUsage = [];
                $transaction = $connection->pipeline();

                foreach ($keys as $key) {
                    $memoryUsage[] = $id = $this->parseGroup($key);
                    $groups[$id]['keys'] = ($groups[$id]['keys'] ?? 0) + 1;
                    $groups[$id]['count'] = $groups[$id]['keys'];
                    $transaction->rawCommand('memory', 'usage', $key);
                }

                foreach ($transaction->exec() ?: [] as $i => $size) {
                    $id = $memoryUsage[$i];
                    $groups[$id]['bytes'] = ($groups[$id]['bytes'] ?? 0) + $size;
                }

                unset($memoryUsage);
            }
        } else {
            foreach ($connection->listKeys($pattern) as $keys) {
                foreach ($keys as $key) {
                    $id = $this->parseGroup($key);
                    $groups[$id]['keys'] = ($groups[$id]['keys'] ?? 0) + 1;
                    $groups[$id]['count'] = $groups[$id]['keys'];
                }
            }
        }

        $groups = $this->prepareGroupsForResponse($groups, $request);

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response($groups);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the REST API response for the request.
     *
     * @param  \WP_REST_Request  $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item($request)
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCacheInterface) {
            return new WP_Error(
                'objectcache_not_supported',
                'The object cache is not supported.',
                ['status' => 400]
            );
        }

        if (! $wp_object_cache->connection()) {
            return new WP_Error(
                'objectcache_not_connected',
                'The object cache is not connected.',
                ['status' => 400]
            );
        }

        $group = $request->get_param('group');

        if (! $group) {
            return new WP_Error(
                'no_group_provided',
                'No cache group was provided.',
                ['status' => 400]
            );
        }

        wp_cache_flush_group($group);

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response(true);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the key's group name.
     *
     * @param  string  $id
     * @return string
     */
    protected function parseGroup(string $id)
    {
        if (! strpos($id, ':')) {
            return '__ungrouped__';
        }

        if (strpos('options:alloptions:', $id) !== false) {
            $id = str_replace('options:alloptions:', 'options:alloptions-', $id);
        }

        return array_reverse(
            explode(':', $id)
        )[1];
    }

    /**
     * Transform the groups into the response format.
     *
     * @param  array<mixed>  $groups
     * @param  \WP_REST_Request  $request
     * @return array<array<string, mixed>>
     */
    protected function prepareGroupsForResponse(array $groups, $request)
    {
        array_walk($groups, function (&$item, $group) use ($request) {
            $item['group'] = str_replace(['{', '}'], '', (string) $group);
            $this->filter_response_by_context($item, $request['context']);
        });

        $groups = array_values($groups);

        $sortBy = $request->get_param('memory') ? 'bytes' : 'keys';

        usort($groups, static function ($a, $b) use ($sortBy) {
            return $b[$sortBy] <=> $a[$sortBy];
        });

        return $groups;
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
            'title' => 'objectcache_groups',
            'type' => 'object',
            'properties' => [
                'group' => [
                    'description' => 'The cache group name.',
                    'type' => 'string',
                    'context' => ['keys', 'bytes'],
                ],
                'keys' => [
                    'description' => 'The number of keys in the group.',
                    'type' => 'integer',
                    'context' => ['keys', 'bytes'],
                ],
                'count' => [
                    'description' => 'The number of keys in the group.',
                    'type' => 'integer',
                    'context' => ['keys', 'bytes'],
                    'deprecated' => true,
                ],
                'bytes' => [
                    'description' => 'The amount of memory used by the group.',
                    'type' => 'integer',
                    'context' => ['bytes'],
                ],
            ],
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema($this->schema);
    }
}
