<?php

namespace Akyos\CanopeeModuleSDK\Class;

use Psr\Container\ContainerInterface;

abstract class AbstractQueryObject
{
    public string $resource;

    abstract public function dataTransform(ContainerInterface $container): array;
}
