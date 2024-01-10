<?php

namespace Akyos\CanopeeModuleSDK;

use Akyos\CanopeeModuleSDK\DependencyInjection\CanopeeModuleSDKExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CanopeeModuleSDK extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new CanopeeModuleSDKExtension();
        }
        return $this->extension;
    }
}