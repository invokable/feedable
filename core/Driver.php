<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core;

use Illuminate\Support\Collection;

class Driver
{
    protected static array $drivers = [];

    /**
     * Register driver information.
     * RSSHubのNamespaceとRouteを合わせたドライバー情報。
     * 各ドライバーのServiceProviderで登録。
     */
    public static function register(
        string $id,
        string $name,
        ?string $url = null,
        ?array $categories = null,
        ?string $description = null,
        ?string $example = null,
        ?string $lang = null,
    ): void {
        static::$drivers[$id] = compact(
            'name',
            'url',
            'categories',
            'description',
            'example',
            'lang',
        );
    }

    public static function get(string $id, array $default = []): array
    {
        return data_get(static::$drivers, $id, $default);
    }

    public static function collect(): Collection
    {
        return new Collection(static::$drivers);
    }

    public static function toJson($options = 0)
    {
        return json_encode(array_values(static::$drivers), $options);
    }
}
