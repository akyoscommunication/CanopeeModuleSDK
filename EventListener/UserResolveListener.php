<?php

namespace Akyos\CanopeeModuleSDK\EventListener;

use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use League\Bundle\OAuth2ServerBundle\Event\UserResolveEvent;

final readonly class UserResolveListener
{
    public function __construct(
        private UserRepository $userRepository,
        private ParameterBagInterface $parameterBag,
    )
    {
    }

    public function onUserResolve(UserResolveEvent $event): void
    {
        try {
            $parameters = $this->parameterBag->get('canopee_module_sdk');
            $userIdentifier = 'uuid';
            if($parameters and isset($parameters['user_identifier'])) {
                $userIdentifier = $parameters['user_identifier'];
            }
            $user = $this->userRepository->findOneBy([$userIdentifier => $event->getUsername(), 'moduleToken' => $event->getPassword(), 'active' => true, 'deletedState' => 'alive']);
        } catch (AuthenticationException $e) {
            return;
        }

        $event->setUser($user);
    }
}
