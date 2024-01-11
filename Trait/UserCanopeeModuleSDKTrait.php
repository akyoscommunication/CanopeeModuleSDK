<?php

namespace Akyos\CanopeeModuleSDK\Trait;

use Akyos\CanopeeModuleSDK\Entity\UserToken;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserCanopeeModuleSDKTrait
{
    public const GROUP_USER_READ = 'user:read';

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    #[Groups([self::GROUP_USER_READ])]
    protected ?string $moduleToken = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserToken::class)]
    private Collection $userTokens;

    public function getModuleToken(): ?string
    {
        return $this->moduleToken;
    }

    public function setModuleToken(?string $moduleToken): void
    {
        $this->moduleToken = $moduleToken;
    }

    /**
     * @return Collection<int, UserToken>
     */
    public function getUserTokens(): Collection
    {
        return $this->userTokens;
    }

    public function addUserToken(UserToken $userToken): static
    {
        if (!$this->userTokens->contains($userToken)) {
            $this->userTokens->add($userToken);
            $userToken->setUser($this);
        }

        return $this;
    }

    public function removeUserToken(UserToken $userToken): static
    {
        if ($this->userTokens->removeElement($userToken)) {
            // set the owning side to null (unless already changed)
            if ($userToken->getUser() === $this) {
                $userToken->setUser(null);
            }
        }

        return $this;
    }
}
