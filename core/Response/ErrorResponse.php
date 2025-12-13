<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

readonly class ErrorResponse implements Responsable
{
    public function __construct(
        protected ?string $error = null,
        protected int $status = 500,
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
        $html = Blade::render(File::get(__DIR__.'/views/error.blade.php'), [
            'error' => $this->error,
            'status' => $this->status,
        ]);

        return response($html, $this->status)
            ->header('Content-Type', 'text/html');
    }
}
