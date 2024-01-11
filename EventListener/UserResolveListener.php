<?php

namespace Akyos\CanopeeModuleSDK\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use League\Bundle\OAuth2ServerBundle\Event\UserResolveEvent;

final readonly class UserResolveListener
{
    public function __construct(
        private UserRepository $userRepository,
        private ContainerInterface $container
    )
    {
    }

    public function onUserResolve(UserResolveEvent $event): void
    {
        try {
            $parameters = $this->container->getParameter('user_identifier');
            $userIdentifier = 'uuid';
            if($parameters) {
                $userIdentifier = $parameters;
            }
            $user = $this->userRepository->findOneBy([$userIdentifier => $event->getUsername(), 'moduleToken' => $event->getPassword(), 'deletedState' => 'alive']);
        } catch (AuthenticationException $e) {
            return;
        }

        $event->setUser($user);
    }
}
