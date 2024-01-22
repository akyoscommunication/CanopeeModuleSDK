<?php

namespace Akyos\CanopeeModuleSDK\Class;

class Post extends AbstractQuery
{
    public function __construct(?string $resource = null)
    {
        $this->resource = $resource;
        $this->method = 'POST';
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
