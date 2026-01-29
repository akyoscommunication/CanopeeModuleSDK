<?php

namespace Akyos\CanopeeModuleSDK\Service\Notifier;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionMethod;
use RuntimeException;

readonly class NotifierExecutor
{
    public function __construct(
        private ContainerInterface $notifierLocator,
        private iterable $argumentResolvers,
    ) {}

    /**
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function execute(string $serviceClass, string $methodName): void
    {
        if (!$this->notifierLocator->has($serviceClass)) {
            throw new RuntimeException(sprintf(
                'Notifier service "%s" not found',
                $serviceClass
            ));
        }

        $service = $this->notifierLocator->get($serviceClass);

        $method = new ReflectionMethod($service, $methodName);

        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            foreach ($this->argumentResolvers as $resolver) {
                if ($resolver->supports($parameter)) {
                    $arguments[] = $resolver->resolve($parameter);
                    continue 2;
                }
            }

            throw new RuntimeException(
                sprintf('No resolver for parameter "%s"', $parameter->getName())
            );
        }

        $method->invokeArgs($service, $arguments);
    }
}
