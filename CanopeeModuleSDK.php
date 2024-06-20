<?php

namespace Akyos\CanopeeModuleSDK;

use Akyos\CanopeeModuleSDK\DependencyInjection\CanopeeModuleSDKExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['KnpPaginatorBundle'])) {
            $container->prependExtensionConfig('knp_paginator', [
                'template' => [
                    'pagination' => '@CanoppeModuleSDK/table/Pagination/sliding.html.twig',
                    'sortable' => '@CanoppeModuleSDK/table/Pagination/sortable_link.html.twig',
                ],
            ]);
        }
    }
}
