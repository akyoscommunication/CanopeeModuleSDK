<?php

namespace Akyos\CanopeeModuleSDK\Service;

use Akyos\CanopeeModuleSDK\Class\AbstractQuery;
use Akyos\CanopeeModuleSDK\Class\Query;
use Akyos\CanopeeModuleSDK\Entity\UserToken;
use Akyos\CanopeeModuleSDK\Repository\UserTokenRepository;
use Exception;
use League\Bundle\OAuth2ServerBundle\Entity\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProviderService
{
    private mixed $user;
    private string $clientId;
    private string $clientSecret;
    private string $moduleUrl;
    private ?UserToken $userToken = null;
    private ?string $target = null;
    private bool $isInitialized = false;
    private GenericProvider $client;

    public function __construct(
        private Security $security,
        private ContainerInterface $container,
        private UserTokenRepository $userTokenRepository,
    ){}

    public function get(AbstractQuery $query): \stdClass
    {
        if($_SERVER['APP_ENV'] !== 'test') {
            $request = $this->request($query);
            try {
                $response = $this->client->getResponse($request);
            } catch (Exception $e) {
                $this->refreshTokens();
                $request = $this->request($query);
                $response = $this->client->getResponse($request);
            }
            return json_decode($response->getBody()->getContents());
        }
        return (object) [
            'hydra:member' => [],
            'hydra:totalItems' => 0,
            '@type' => '',
            '@id' => '',
            '@context' => '',
        ];
    }

    private function request(AbstractQuery $query): RequestInterface
    {
        $pathParams = '';
        if(!empty($query->getBody())){
            $options['body'] = json_encode($query->getBody());
        }
        foreach ($query->getPathParams() as $value) {
            $pathParams .= '/'.$value;
        }
        return $this->client->getAuthenticatedRequest(
            $query->getMethod(),
            $this->moduleUrl . 'api/' . $query->getResource(). $pathParams . '?' . http_build_query($query->getQueryParams()),
            $this->userToken->getAccessToken(),
            $options ?? []
        );
    }

    private function refreshTokens(): void
    {
        $response = $this->client->getAccessToken('refresh_token', [
            'refresh_token' => $this->userToken->getRefreshToken(),
        ]);
        $this->userToken->setRefreshToken($response->getRefreshToken());
        $this->userToken->setAccessToken($response->getToken());
        $this->userTokenRepository->add($this->userToken, true);
    }

    /**
     * @throws Exception
     */
    public function send(AbstractQuery $query): AbstractQuery
    {
        $this->isInitialized();

        $data = $this->get($query);
        $query->processData($data);
        return $query;
    }

    /**
     * @throws Exception
     */
    public function setUser(mixed $user): self
    {
        $this->isInitialized();

        $this->user = $user;

        if(!$this->user) {
            throw new Exception('User is not authenticated');
        }

        if ($_SERVER['APP_ENV'] !== 'test') {
            $userToken = $this->userTokenRepository->findOneBy(['user' => $this->user, 'module' => $this->target]);
            if(!$userToken) {

                $response = $this->client->getAccessToken('password', [
                    'username' => str_contains($this->user->getUserIdentifier(), '@') ? $this->user->getId() : $this->user->getUserIdentifier(),
                    'password' => $this->user->getModuleToken(),
                ]);

                $userToken = (new UserToken())
                    ->setUser($this->user)
                    ->setRefreshToken($response->getRefreshToken())
                    ->setAccessToken($response->getToken())
                    ->setModule($this->target)
                ;
                $this->user->addUserToken($userToken);
                $this->userTokenRepository->add($userToken, true);
            }
            $this->userToken = $userToken;
        }
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setTarget(?string $target): self
    {
        $this->isInitialized();

        $this->target = $target;

        $this->clientId = $this->container->getParameter('modules')[$target]['id'];
        $this->clientSecret = $this->container->getParameter('modules')[$target]['secret'];
        $this->moduleUrl = $this->container->getParameter('modules')[$target]['endpoint'];

        $this->client = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'urlAuthorize' => $this->moduleUrl.'authorize',
            'urlAccessToken' => $this->moduleUrl.'token',
            'urlResourceOwnerDetails' => $this->moduleUrl,
        ]);

        if(!$this->user) {
            $this->user = $this->security->getUser();
        }
        $this->setUser($this->user);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function initialize(?string $target, mixed $user = null): self
    {
        $this->isInitialized = true;
        $this->user = $user;
        $this->setTarget($target);

        return $this;
    }

    private function isInitialized(): void
    {
        if(!$this->isInitialized) {
            throw new Exception('ProviderService is not initialized');
        }
    }
}
