<?php

namespace Akyos\CanopeeModuleSDK\Service\Notifier;

use Akyos\CanopeeModuleSDK\Attribute\Notifier;
use ReflectionClass;

readonly class NotifierScanner
{
    public function __construct(
        private iterable $notifierServices
    )
    {
    }

    public function getNotifiers(): array
    {
        $notifiers = [];

        foreach ($this->notifierServices as $service) {
            $reflectionClass = new ReflectionClass($service);

            foreach ($reflectionClass->getMethods() as $method) {
                foreach ($method->getAttributes(Notifier::class) as $attribute) {
                    $instance = $attribute->newInstance();

                    $notifiers[] = [
                        'serviceId' => $reflectionClass->getName(),
                        'method' => $method->getName(),
                        'name' => $instance->name,
                        'description' => $instance->description,
                        'requirements' => $instance->requirements,
                    ];
                }
            }
        }

        return $notifiers;
    }
}
