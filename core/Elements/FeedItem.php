<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Elements;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

/**
 * Item object common to RSS2 and Atom.
 */
class FeedItem implements Arrayable
{
    use Conditionable;
    use Tappable;

    protected array $extra = [];

    public function __construct(
        public string|int|null $id = null,
        public ?string $title = null,
        public ?string $guid = null,
        public ?string $link = null,
        public ?string $author = null,
        public ?string $pubDate = null,
        public ?string $description = null,
        public ?string $thumbnail = null,
        public ?array $categories = null,
    ) {
        //
    }

    /**
     * Get property value with default.
     *
     * ```
     * $title = $item->get('title', 'Default Title');
     * ```
     */
    public function get(string $name, string|array|null $default = null): string|array|null
    {
        return $this->$name ?? data_get($this->extra, $name, $default);
    }

    /**
     * Fluently set property value.
     *
     * ```
     * $item->set('title', 'New Title')
     *      ->set('categories', ['News', 'Updates']);
     * ```
     */
    public function set(string $name, string|array|null $value): self
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->extra[$name] = $value;
        }

        return $this;
    }

    public function __get(string $name)
    {
        return data_get($this->extra, $name);
    }

    public function __set(string $name, string|array|null $value): void
    {
        $this->extra[$name] = $value;
    }

    public function toArray(): array
    {
        return Collection::make(get_object_vars($this))
            ->reject(fn ($value, $key) => $key === 'extra')
            ->merge($this->extra)
            ->toArray();
    }
}
