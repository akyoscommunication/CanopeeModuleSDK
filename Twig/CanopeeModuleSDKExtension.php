<?php

namespace Akyos\CanopeeModuleSDK\Twig;

use Akyos\CanopeeModuleSDK\Class\AbstractQuery;
use Akyos\CanopeeSDK\Service\ModuleService;
use Akyos\CanopeeModuleSDK\Service\ProviderService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CanopeeModuleSDKExtension extends AbstractExtension
{

    public TagAwareAdapter $cache;

    public function __construct(
        private readonly ProviderService       $provider,
    ) {
        $this->cache = new TagAwareAdapter(new FilesystemAdapter());
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('query', [$this, 'query']),
            new TwigFunction('provider', [$this, 'provider']),
        ];
    }

    public function query(string $mime): AbstractQuery
    {
        $class = 'Akyos\\CanopeeModuleSDK\\Class\\' . $mime;
        return new $class();
    }

    public function provider(): ProviderService
    {
        return $this->provider;
    }
}
