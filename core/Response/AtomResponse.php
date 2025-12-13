<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

readonly class AtomResponse implements Responsable
{
    public function __construct(
        protected ?string $title = null,
        protected ?string $link = null,
        protected ?string $image = null,
        protected ?string $language = null,
        protected ?array $items = null,
    ) {
        //
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        $atom = Blade::render(File::get(__DIR__.'/views/atom.blade.php'), [
            'title' => $this->title,
            'items' => $this->items,
            //
        ]);

        return response($atom)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
