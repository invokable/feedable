<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Elements;

class Author
{
    public function __construct(
        public ?string $name = null,
        public ?string $url = null,
        public ?string $avatar = null,
    ) {
        //
    }

    public static function make(
        ?string $name = null,
        ?string $url = null,
        ?string $avatar = null,
    ): static {
        return new static($name, $url, $avatar);
    }
}
