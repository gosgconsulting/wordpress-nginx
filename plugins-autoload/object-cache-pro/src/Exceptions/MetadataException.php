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

namespace RedisCachePro\Exceptions;

use InvalidArgumentException;

class MetadataException extends ObjectCacheException
{
    const NOT_FOUND = 1;

    const DECODE_FAILED = 2;

    const CLIENT_CHANGED = 11;

    const DATABASE_CHANGED = 12;

    const PREFIX_CHANGED = 13;

    const SPLIT_ALLOPTIONS_CHANGED = 14;

    const SERIALIZER_CHANGED = 15;

    const COMPRESSION_CHANGED = 16;

    const VERSION_WORDPRESS = 101;

    /**
     * Creates a new exception for a risky configuration option change.
     *
     * @param  string  $option
     * @return self
     */
    public static function for($option)
    {
        $constant = constant(__CLASS__ . '::' . strtoupper($option) . '_CHANGED');

        if (! $constant) {
            throw new InvalidArgumentException("Unknown risky option: {$option}");
        }

        return new self("Risky configuration option `{$option}` changed", $constant);
    }
}
