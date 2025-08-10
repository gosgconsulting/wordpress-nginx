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

namespace RedisCachePro\Support;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
class PluginApiResponse
{
    //
}

class PluginApiUpdateResponse extends PluginApiResponse
{
    /** @var string */
    public $version;

    /** @var string */
    public $php;

    /** @var string */
    public $wp;

    /** @var ?string */
    public $package;

    /** @var ?object */
    public $license;
}

class PluginApiInfoResponse extends PluginApiResponse
{
    /** @var string */
    public $slug;

    /** @var string */
    public $name;

    /** @var string */
    public $homepage;

    /** @var ?string */
    public $download_link;

    /** @var string */
    public $author;

    /** @var string */
    public $author_profile;

    /** @var string */
    public $requires;

    /** @var string */
    public $requires_php;

    /** @var string */
    public $tested;

    /** @var string */
    public $added;

    /** @var string */
    public $version;

    /** @var string */
    public $last_updated;

    /** @var int */
    public $active_installs;

    /** @var object */
    public $icons;

    /** @var object */
    public $banners;

    /** @var object */
    public $sections;

    /** @var object */
    public $contributors;
}

class PluginApiLicenseResponse extends PluginApiResponse
{
    /** @var ?string */
    public $token;

    /** @var string */
    public $state;

    /** @var string */
    public $stability;

    /** @var ?string */
    public $plan;

    /** @var object */
    public $organization;
}

class ObjectCacheInfo
{
    /** @var bool */
    public $status;

    /** @var object */
    public $groups;

    /** @var array<string> */
    public $errors;

    /** @var array<string, string> */
    public $meta;
}

class ObjectCacheMetricsGroup
{
    /** @var int */
    public $keys = 0;

    /** @var int */
    public $memory = 0;

    /** @var float */
    public $wait = 0.0;
}
// phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses
