<?php

namespace Akyos\CanopeeModuleSDK\Interface;

use ReflectionParameter;

interface NotifierArgumentResolverInterface
{
    public function supports(ReflectionParameter $parameter): bool;

    public function resolve(ReflectionParameter $parameter): mixed;

}