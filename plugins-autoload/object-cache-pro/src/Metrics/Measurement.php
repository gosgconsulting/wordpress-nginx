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

namespace RedisCachePro\Metrics;

class Measurement
{
    /**
     * The unique identifier of the measurement.
     *
     * @var string
     */
    public $id;

    /**
     * The Unix timestamp with microseconds of the measurement.
     *
     * @var float
     */
    public $timestamp;

    /**
     * The hostname on which the measurement was taken.
     *
     * @var string|null
     */
    public $hostname;

    /**
     * The URL path of the request, if applicable.
     *
     * @var string|null
     */
    public $path;

    /**
     * The WordPress measurement.
     *
     * @var \RedisCachePro\Metrics\WordPressMetrics
     */
    public $wp;

    /**
     * The Redis measurement.
     *
     * @var \RedisCachePro\Metrics\RedisMetrics|null
     */
    public $redis;

    /**
     * The Relay measurement.
     *
     * @var \RedisCachePro\Metrics\RelayMetrics|null
     */
    public $relay;

    /**
     * Makes a new instance.
     *
     * @return self
     */
    public static function make()
    {
        $self = new self;
        $self->id = substr(md5(uniqid((string) mt_rand(), true)), 12);
        $self->timestamp = microtime(true);

        return $self;
    }

    /**
     * Collect optional properties.
     *
     * @param  array<string>  $properties
     * @return self
     */
    public function with($properties)
    {
        foreach ($properties as $property) {
            if ($property === 'path') {
                $this->path = $_SERVER['REQUEST_URI'] ?? null;
            }

            if ($property === 'hostname') {
                if (isset($_ENV['DYNO'])) {
                    $this->hostname = $_ENV['DYNO']; // Heroku
                } else {
                    $this->hostname = gethostname() ?: null;
                }
            }
        }

        return $this;
    }

    /**
     * Returns an rfc3339 compatible timestamp.
     *
     * @return string
     */
    public function rfc3339()
    {
        return substr_replace(
            date('c', intval($this->timestamp)),
            substr((string) fmod($this->timestamp, 1), 1, 7),
            19,
            0
        );
    }

    /**
     * Returns a new instance from given JSON.
     *
     * @param  string  $json
     * @return ?self
     */
    public static function fromJson(string $json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $measurement = new self;

        foreach ($data as $key => $value) {
            if ($key === 'wp') {
                $measurement->wp = WordPressMetrics::fromArray($value);
            } elseif ($key === 'redis') {
                $measurement->redis = $value ? RedisMetrics::fromArray($value) : null;
            } elseif ($key === 'relay') {
                $measurement->relay = $value ? RelayMetrics::fromArray($value) : null;
            } else {
                $measurement->{$key} = $value;
            }
        }

        return $measurement;
    }

    /**
     * Returns the measurement as array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $array = $this->wp ? $this->wp->toArray() : [];

        if ($this->redis) {
            $redis = $this->redis->toArray();

            $array += array_combine(array_map(static function ($key) {
                return "redis-{$key}";
            }, array_keys($redis)), $redis);
        }

        if ($this->relay) {
            $relay = $this->relay->toArray();

            $array += array_combine(array_map(static function ($key) {
                return "relay-{$key}";
            }, array_keys($relay)), $relay);
        }

        return $array;
    }

    /**
     * Returns the request metrics in string format.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(' ', array_filter([
            $this->wp,
            $this->redis ? (string) $this->redis : null,
            $this->relay ? (string) $this->relay : null,
        ]));
    }

    /**
     * Helper method to access metrics.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (strpos($name, '->') !== false) {
            [$type, $metric] = explode('->', $name);

            if (strpos($metric, '-') !== false) {
                $metric = lcfirst(str_replace('-', '', ucwords($metric, '-')));
            }

            if (property_exists($this, $type)) {
                return $this->{$type}->{$metric} ?? null;
            }
        }

        trigger_error(
            sprintf('Undefined property: %s::$%s', get_called_class(), $name),
            E_USER_WARNING
        );
    }
}
