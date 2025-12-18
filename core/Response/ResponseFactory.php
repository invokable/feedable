<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;

class ResponseFactory
{
    public function __construct(protected string $format = 'rss') {}

    public static function format(string $format = 'rss'): static
    {
        return new static($format);
    }

    /**
     * TODO: 共通で使える引数を設定しつつ各フォーマットのレスポンスを生成
     */
    public function make(): Responsable
    {
        return match ($this->format) {
            'json' => $this->json(),
            'atom' => $this->atom(),
            default => $this->rss(),
        };
    }

    protected function json(): Responsable
    {
        return new JsonFeedResponse;
    }

    protected function rss(): Responsable
    {
        return new Rss2Response;
    }

    protected function atom(): Responsable
    {
        return new AtomResponse;
    }
}
