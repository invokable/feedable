<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Support;

use League\Uri\Uri;

class AbsoluteUri
{
    /**
     * Resolve a relative URI against a base URI.
     *
     * ```
     * use Revolution\Feedable\Core\Support\AbsoluteUri;
     *
     * $absoluteUri = AbsoluteUri::resolve('http://example.com/path/', '../other-path/resource');
     * // Result: 'http://example.com/other-path/resource'
     * ```
     */
    public static function resolve(string $base, string $relative): string
    {
        return Uri::new(trim($base))->resolve(trim($relative))->toString();
    }
}
