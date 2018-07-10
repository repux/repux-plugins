<?php

namespace App\Entity;

use App\Entity\Traits\IdentityTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class User implements UserInterface
{
    use IdentityTrait, TimestampableEntity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=42, unique=true)
     *
     * @Serializer\Expose
     */
    private $ethAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150)
     */
    private $authMessage;

    /**
     * @var UserAuthToken[]
     *
     * @ORM\OneToMany(targetEntity="UserAuthToken", mappedBy="user")
     */
    private $authTokens;

    /**
     * @var ShopifyStore[]|Collection
     *
     * @ORM\OneToMany(targetEntity="ShopifyStore", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $shopifyStores;

    public function getEthAddress(): ?string
    {
        return $this->ethAddress;
    }

    public function setEthAddress(string $ethAddress): void
    {
        $this->ethAddress = $ethAddress;
    }

    public function getAuthMessage(): ?string
    {
        return $this->authMessage;
    }

    public function setAuthMessage(string $authMessage): void
    {
        $this->authMessage = $authMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->ethAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return UserAuthToken[]
     */
    public function getAuthTokens(): array
    {
        return $this->authTokens;
    }

    /**
     * @param UserAuthToken[] $authTokens
     */
    public function setAuthTokens(array $authTokens): void
    {
        $this->authTokens = $authTokens;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getShopifyStore(): ?ShopifyStore
    {
        if (!$this->shopifyStores->isEmpty()) {
            return $this->shopifyStores->first();
        }

        return null;
    }

    /**
     * @return ShopifyStore[]|Collection
     */
    public function getShopifyStores(): Collection
    {
        return $this->shopifyStores;
    }

    public function addShopifyStore(ShopifyStore $shopifyStore)
    {
        if (!$this->shopifyStores->contains($shopifyStore)) {
            $this->shopifyStores->add($shopifyStore);
            $shopifyStore->setUser($this);
        }
    }

    public function removeShopifyStore(ShopifyStore $shopifyStore)
    {
        $this->shopifyStores->removeElement($shopifyStore);
    }

    public function equals($user): bool
    {
        return $user instanceof self && $this->id === $user->getId();
    }
}
