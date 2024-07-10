<?php

namespace Akyos\CanopeeModuleSDK\Class\Table;

class TR
{
    public iterable $ths;

    public function __construct(iterable $ths)
    {
        $this->ths = $ths;
    }
}
