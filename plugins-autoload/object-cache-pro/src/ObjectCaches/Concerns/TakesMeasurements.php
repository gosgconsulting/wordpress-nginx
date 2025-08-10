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

use Throwable;

use RedisCachePro\Metrics\Measurement;
use RedisCachePro\Metrics\Measurements;
use RedisCachePro\Metrics\RedisMetrics;
use RedisCachePro\Metrics\RelayMetrics;
use RedisCachePro\Metrics\WordPressMetrics;

use RedisCachePro\Clients\PhpRedis;
use RedisCachePro\Connections\RelayConnection;
use RedisCachePro\Configuration\Configuration;

use function RedisCachePro\log;

trait TakesMeasurements
{
    /**
     * The gathered metrics for the current request.
     *
     * @var \RedisCachePro\Metrics\Measurement|null
     */
    protected $requestMeasurement;

    /**
     * Retrieve measurements of the given type and range.
     *
     * @param  string|int  $min
     * @param  string|int  $max
     * @param  string|int|null  $offset
     * @param  string|int|null  $count
     * @return \RedisCachePro\Metrics\Measurements
     */
    public function measurements($min = '-inf', $max = '+inf', $offset = null, $count = null): Measurements
    {
        $options = [];

        if (is_int($offset) && is_int($count)) {
            $options['limit'] = [$offset, $count];
        }

        $measurements = new Measurements;

        try {
            $measurements->push(
                ...$this->withoutMutations(function () use ($max, $min, $options) {
                    return array_filter(array_map(
                        [Measurement::class, 'fromJson'],
                        $this->connection->zRevRangeByScore(
                            (string) $this->id('measurements', 'analytics'),
                            (string) $max,
                            (string) $min,
                            $options
                        )
                    ));
                })
            );
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        $this->metrics->read('analytics');

        return $measurements;
    }

    /**
     * Return number of metrics stored.
     *
     * @param  string  $min
     * @param  string  $max
     * @return int
     */
    public function countMeasurements($min = '-inf', $max = '+inf')
    {
        $count = $this->connection->zcount(
            (string) $this->id('measurements', 'analytics'),
            (string) $min,
            (string) $max
        );

        $this->metrics->read('analytics');

        return $count;
    }

    /**
     * Stores metrics for the current request.
     *
     * @return void
     */
    protected function storeMeasurements()
    {
        if (! $this->config->analytics->enabled) {
            return;
        }

        $random = (mt_rand() / mt_getrandmax()) * 100;
        $chance = max(min($this->config->analytics->sample_rate, 100), 0);

        if ($random >= $chance) {
            return;
        }

        $now = time();
        $id = (string) $this->id('measurements', 'analytics');

        $measurement = Measurement::make()->with(
            $this->config->analytics->include
        );

        try {
            $lastSample = $this->get('last-sample', 'analytics');

            if ($lastSample < $now - 3) {
                $measurement->redis = RedisMetrics::from($this);

                if (
                    $this->connection instanceof RelayConnection &&
                    $this->connection->hasInMemoryCache()
                ) {
                    $measurement->relay = RelayMetrics::from($this->connection);
                }

                $this->set('last-sample', $now, 'analytics');
            }

            $measurement->wp = WordPressMetrics::from($this);
            $measurement->wp->storeWrites++;

            $this->withoutMutations(function () use ($id, $measurement) {
                $this->connection->zadd($id, $measurement->timestamp, json_encode($measurement));
            });

            $this->metrics->write('analytics');
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        $this->requestMeasurement = $measurement;
    }

    /**
     * Discards old measurements.
     *
     * @return void
     */
    public function pruneMeasurements()
    {
        $id = $this->id('measurements', 'analytics');
        $retention = $this->config->analytics->retention;
        $remaining = 0;

        try {
            $threshold = $this->connection->zRangeByScore(
                (string) $id,
                (string) (microtime(true) - $retention),
                '+inf',
                ['limit' => [0, 1]]
            );

            $this->metrics->write('read');

            if (empty($threshold)) {
                return;
            }

            $remaining = $this->connection->zRank((string) $id, $threshold[0]);
            $this->metrics->write('read');

            if (! $remaining) {
                return;
            }
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        while ($remaining > 0) {
            $chunk = min($remaining, 500);

            $this->connection->zRemRangeByRank((string) $id, 0, $chunk - 1);
            $this->metrics->write('write');

            $remaining -= $chunk;
        }
    }

    /**
     * Returns a dump of the measurements.
     *
     * @return string|false
     */
    protected function dumpMeasurements()
    {
        if (
            $this->client() instanceof PhpRedis &&
            $this->config->compression === Configuration::COMPRESSION_ZSTD &&
            version_compare((string) phpversion('redis'), '5.3.5', '<')
        ) {
            log('warning', 'Unable to restore analytics when using Zstandard compression, please update to PhpRedis 5.3.5 or newer');

            return false;
        }

        try {
            $dump = $this->connection->dump(
                (string) $this->id('measurements', 'analytics')
            );

            $this->metrics->read('analytics');

            return $dump;
        } catch (Throwable $exception) {
            log('warning', "Failed to dump analytics ({$exception})");
        }

        return false;
    }

    /**
     * Restores the given measurements dump.
     *
     * @param  mixed  $measurements
     * @return bool|void
     */
    protected function restoreMeasurements($measurements)
    {
        try {
            $result = $this->connection->restore((string) $this->id('measurements', 'analytics'), 0, $measurements);

            $this->metrics->write('analytics');

            return $result;
        } catch (Throwable $exception) {
            log('warning', "Failed to restore analytics ({$exception})");
        }
    }

    /**
     * Return the gathered metrics for the current request.
     *
     * @return \RedisCachePro\Metrics\Measurement|null
     */
    public function requestMeasurement()
    {
        return $this->requestMeasurement;
    }
}
