<?php

namespace Akyos\CanopeeModuleSDK\Class;

class Get extends AbstractQuery
{
    public function __construct(?string $resource = null)
    {
        $this->resource = $resource;
        $this->method = 'GET';
    }

    public function onPreQuery(): void
    {
        // Blank method
    }

    public function onSetData(): void
    {
        // Blank method
    }
}
