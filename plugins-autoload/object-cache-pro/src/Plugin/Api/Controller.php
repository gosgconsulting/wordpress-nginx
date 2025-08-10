<?php
/**
 * Copyright © 2019-2024 Rhubarb Tech Inc. All Rights Reserved.
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
use WP_REST_Controller;

use RedisCachePro\Plugin;

abstract class Controller extends WP_REST_Controller
{
    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->namespace = 'objectcache/v1';
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @param  \WP_REST_Request  $request
     * @return true|\WP_Error
     */
    public function get_items_permissions_check($request)
    {
        /**
         * Filter the capability required to access REST API endpoints.
         *
         * @param  string  $capability  The capability name.
         */
        $capability = (string) apply_filters('objectcache_rest_capability', Plugin::Capability);

        if (current_user_can($capability)) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            'Sorry, you are not allowed to do that.',
            ['status' => rest_authorization_required_code()]
        );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @param  \WP_REST_Request  $request
     * @return true|\WP_Error
     */
    public function update_item_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }

    /**
     * Checks if a given request has access to delete a specific item.
     *
     * @param  \WP_REST_Request  $request
     * @return true|\WP_Error
     */
    public function delete_item_permissions_check($request)
    {
        return $this->get_items_permissions_check($request);
    }

    /**
     * Returns a not supported error.
     *
     * @return \WP_Error
     */
    protected function notSupportedError()
    {
        return new WP_Error(
            'objectcache_not_supported',
            'The object cache is not supported.',
            ['status' => 400]
        );
    }

    /**
     * Returns a not connected error.
     *
     * @return \WP_Error
     */
    protected function notConnectedError()
    {
        return new WP_Error(
            'objectcache_not_connected',
            'The object cache is not connected.',
            ['status' => 400]
        );
    }
}
