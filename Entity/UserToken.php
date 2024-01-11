<?php

namespace Akyos\CanopeeModuleSDK\Entity;

use Akyos\CanopeeModuleSDK\Repository\UserTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userTokens')]
    private mixed $user = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $accessToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $refreshToken = null;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    protected ?string $module = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    public function setUser(mixed $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }


    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): self
    {
        $this->module = $module;

        return $this;
    }
}
