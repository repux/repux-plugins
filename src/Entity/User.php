<?php

namespace App\Entity;

use App\Entity\Traits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(schema="public")
 */
class User implements UserInterface
{
    use IdentityTrait, TimestampableEntity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=42)
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
}
