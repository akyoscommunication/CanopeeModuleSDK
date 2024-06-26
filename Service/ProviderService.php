<?php

namespace Akyos\CanopeeModuleSDK\Service;

use Akyos\CanopeeModuleSDK\Class\AbstractQuery;
use Akyos\CanopeeModuleSDK\Class\AbstractQueryObject;
use Akyos\CanopeeModuleSDK\Class\Query;
use Akyos\CanopeeModuleSDK\Entity\UserToken;
use Akyos\CanopeeModuleSDK\Repository\UserTokenRepository;
use Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use stdClass;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use League\Bundle\OAuth2ServerBundle\Entity\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
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
            } catch (ClientException $e) {
                if ($e->getCode() === Response::HTTP_UNAUTHORIZED) {
                    $this->refreshTokens();
                    $request = $this->request($query);
                    $response = $this->client->getResponse($request);
                } else {
                    throw $e;
                }
            } catch (Exception $e) {
                dd($e);
            }
            $data = json_decode($response->getBody()->getContents());
            if(is_array($data)) {
                $object = new stdClass();
                foreach ($data as $key => $value)
                {
                    $object->$key = $value;
                }
                $data = $object;
            }
            return $data ?: new \stdClass();
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
        $body = $query->getBody();
        $options = [];
        $pathParams = '';
        $resource = $query->getResource();

        if ($body instanceof AbstractQueryObject) {
            $resource = $body->resource;
            $options['body'] = json_encode($body->dataTransform($this->container));
        } else {
            $options['body'] = !empty($query->getBody()) ? json_encode($body) : null;
        }

        foreach ($query->getPathParams() as $value) {
            $pathParams .= '/'.$value;
        }

        $request = $this->client->getAuthenticatedRequest(
            $query->getMethod(),
            $this->moduleUrl . 'api/' . $resource. $pathParams . '?' . http_build_query($query->getQueryParams()),
            $this->userToken->getAccessToken(),
            $options
        );

        foreach($query->getHeaders() as $key => $value) {
            $request = $request->withAddedHeader($key, $value);
        }

        return $request;
    }

    private function refreshTokens(): void
    {
        try {
            $response = $this->client->getAccessToken('refresh_token', [
                'refresh_token' => $this->userToken->getRefreshToken(),
            ]);
            $this->userToken->setRefreshToken($response->getRefreshToken());
            $this->userToken->setAccessToken($response->getToken());
            $this->userTokenRepository->add($this->userToken, true);
        } catch (IdentityProviderException $e) {
            try {
                $this->userToken = $this->newUserToken($this->userToken->getUser());
            } catch (Exception $e) {
                dd($e);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function send(AbstractQuery $query): AbstractQuery
    {
        $this->isInitialized();

        $query->onPreQuery();
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
                $userToken = $this->newUserToken($this->user);
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

    /**
     * @throws Exception
     */
    private function newUserToken(mixed $user): UserToken
    {
        try {
            $response = $this->client->getAccessToken('password', [
                'username' => str_contains($user->getUserIdentifier(), '@') ? $user->getId() : $user->getUserIdentifier(),
                'password' => $user->getModuleToken(),
            ]);

            $userToken = (new UserToken())
                ->setUser($user)
                ->setRefreshToken($response->getRefreshToken())
                ->setAccessToken($response->getToken())
                ->setModule($this->target)
            ;
            $this->user->addUserToken($userToken);
            $this->userTokenRepository->add($userToken, true);

            return $userToken;
        } catch (IdentityProviderException $e) {
            throw new Exception('Invalid credentials '.$e->getMessage());
        }
    }
}
