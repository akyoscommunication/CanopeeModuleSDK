<?php

namespace Akyos\CanopeeModuleSDK\Class;

interface QueryInterface
{
    // Before query the API, this method is called
    public function onPreQuery(): void;

    // After data returned by API are set on the query, this method is called
    public function onSetData(): void;
}