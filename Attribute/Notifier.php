<?php

namespace Akyos\CanopeeModuleSDK\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Notifier
{
    public function __construct(
        public string $name,
        public string $description = ''
    )
    {
    }
}