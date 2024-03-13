<?php

namespace Akyos\CanopeeModuleSDK\Class;

class Delete extends AbstractQuery
{
    public function __construct(?string $resource = null)
    {
        $this->resource = $resource;
        $this->method = 'DELETE';
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
