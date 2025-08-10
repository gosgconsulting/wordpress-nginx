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

namespace RedisCachePro\Plugin;

use RedisCachePro\ObjectCaches\ObjectCacheInterface;
use RedisCachePro\ObjectCaches\MeasuredObjectCacheInterface;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Schedule
{
    /**
     * Boot Schedule component.
     *
     * @return void
     */
    public function bootSchedule()
    {
        add_filter('cron_schedules', [$this, 'cronSchedules']);
        add_action('objectcache_prune_queries', [$this, 'pruneQueries']);
        add_action('objectcache_prune_analytics', [$this, 'pruneAnalytics']);
        add_action('objectcache_metrics_snapshot', [$this, 'captureMetricsSnapshot']);

        if (wp_doing_cron() && ! wp_next_scheduled('objectcache_metrics_snapshot')) {
            wp_schedule_event(time(), 'every_five_minutes', 'objectcache_metrics_snapshot');
        }

        if (wp_doing_cron() && ! wp_next_scheduled('objectcache_prune_analytics')) {
            wp_schedule_event(time(), 'hourly', 'objectcache_prune_analytics');
        }

        if (wp_doing_cron() && ! wp_next_scheduled('objectcache_prune_queries')) {
            // wp_schedule_event(time(), 'hourly', 'objectcache_prune_queries');
        }
    }

    /**
     * Called by lifecycle uninstall hook.
     *
     * @return void
     */
    public function unschedule()
    {
        wp_unschedule_event(
            (int) wp_next_scheduled('objectcache_prune_queries'),
            'objectcache_prune_queries'
        );

        wp_unschedule_event(
            (int) wp_next_scheduled('objectcache_prune_analytics'),
            'objectcache_prune_analytics'
        );

        wp_unschedule_event(
            (int) wp_next_scheduled('objectcache_metrics_snapshot'),
            'objectcache_metrics_snapshot'
        );
    }

    /**
     * Callback to add custom cron schedules.
     *
     * @param  array<string, array<string, int|string>>  $schedules
     * @return array<string, array<string, int|string>>
     */
    public function cronSchedules($schedules)
    {
        if (! isset($schedules['every_five_minutes'])) {
            $schedules['every_five_minutes'] = [
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display' => 'Every 5 minutes',
            ];
        }

        return $schedules;
    }

    /**
     * Callback for the scheduled `objectcache_prune_analytics` hook.
     *
     * @return void
     */
    public function pruneAnalytics()
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof MeasuredObjectCacheInterface) {
            return;
        }

        if (! method_exists($wp_object_cache, 'pruneMeasurements')) {
            return;
        }

        $wp_object_cache->pruneMeasurements();
    }

    /**
     * Callback for the scheduled `objectcache_prune_queries` hook.
     *
     * @return void
     */
    public function pruneQueries()
    {
        global $wp_object_cache;

        $lastChanged = [];

        foreach ($wp_object_cache->queryGroups() as $queryGroup => $dataGroup) {
            $lastChanged[$queryGroup] = wp_cache_get_last_changed($dataGroup);
        }

        $wp_object_cache->pruneQueries($lastChanged);
    }

    /**
     * Callback for scheduled `objectcache_metrics` hook.
     *
     * @return void
     */
    public function captureMetricsSnapshot()
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCacheInterface) {
            return;
        }

        if (! $wp_object_cache instanceof MeasuredObjectCacheInterface) {
            return;
        }

        if (empty($wp_object_cache->config()->analytics->enabled)) {
            return;
        }

        $hours = 6;
        $interval = 5 * MINUTE_IN_SECONDS;

        $snapshots = get_site_option('objectcache_snapshots');

        if (! is_array($snapshots)) {
            $snapshots = [];
        }

        $measurements = $wp_object_cache->measurements();

        $time = (int) (time() / $interval) * $interval;

        $snapshots[$time] = [
            'redisKeys' => $measurements->max('redis->keys'),
            'storeReads' => $measurements->max('wp->storeReads'),
            'storeWrites' => $measurements->max('wp->storeWrites'),
            'msCacheAvg' => $measurements->max('wp->msCacheAvg'),
            'msCacheRatio' => $measurements->max('wp->msCacheRatio'),
        ];

        $entries = ($hours * HOUR_IN_SECONDS) / ($interval / 60);
        $snapshots = array_slice($snapshots, -$entries, null, true);

        update_site_option('objectcache_snapshots', $snapshots);

        $snapshot = get_site_option('objectcache_snapshot');

        if (! is_array($snapshot)) {
            $snapshot = $snapshots[$time];
        }

        foreach ($snapshots as $time => $values) {
            foreach ($values as $metric => $value) {
                if ($value > $snapshot[$metric]) {
                    $snapshot[$metric] = $value;
                }
            }
        }

        update_site_option('objectcache_snapshot', $snapshot);
    }
}
