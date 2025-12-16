<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core;

use Illuminate\Support\Collection;

class Driver
{
    protected static array $drivers = [];

    /**
     * Register driver information.
     *
     * Registered with each driver's Service Provider.
     *
     * @param  string  $id  Unique Driver ID (e.g. 'mirror', 'famitsu')
     * @param  string  $name  User-facing Driver Name
     * @param  string|null  $url  Target site URL
     * @param  array|null  $categories  Categories or Tags associated with the Driver
     * @param  string|null  $description  Brief description of the Driver's functionality. Markdown supported.
     * @param  string|null  $example  Example URL demonstrating Driver usage
     * @param  string|null  $lang  Language code (e.g. 'en', 'ja')
     * @param  bool  $browser  Indicates whether the driver requires a browser environment such as Playwright.
     */
    public static function about(
        string $id,
        string $name,
        ?string $url = null,
        ?array $categories = null,
        ?string $description = null,
        ?string $example = null,
        ?string $lang = null,
        bool $browser = false,
    ): void {
        static::$drivers[$id] = compact(
            'name',
            'url',
            'categories',
            'description',
            'example',
            'lang',
            'browser',
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
