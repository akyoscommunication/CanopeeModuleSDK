<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Symfony\Component\HttpFoundation\Request;

class Patch extends AbstractQuery
{
    public function __construct(?string $resource = null)
    {
        $this->resource = $resource;
        $this->method = Request::METHOD_PATCH;
    }

    public function onPreQuery(): void
    {
        $this->setHeaders(['Content-Type' => 'application/merge-patch+json']);
    }

    public function onSetData(): void
    {
        // Blank method
    }
}
