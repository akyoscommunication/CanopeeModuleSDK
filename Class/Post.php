<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Symfony\Component\HttpFoundation\Request;

class Post extends AbstractQuery
{
    public function __construct(?string $resource = null)
    {
        $this->resource = $resource;
        $this->method = Request::METHOD_POST;
    }

    public function onPreQuery(): void
    {
        $this->setHeaders(['Content-Type' => 'application/ld+json']);
    }

    public function onSetData(): void
    {
        // Blank method
    }
}
