<?php

namespace Akyos\CanopeeModuleSDK\Class;

interface QueryInterface
{
    // After data are set on the query, this method is called
    public function onSetData(): void;
}