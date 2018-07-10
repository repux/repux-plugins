<?php

namespace App\Entity;

use App\Entity\Model\UserRelatedInterface;
use App\Entity\Traits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShopifyStoreRepository")
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="user_store_name_unique", columns={"user_id", "name"})
 *     }
 * )
 *
 * @UniqueEntity(
 *     fields={"user", "name"},
 *     errorPath="name",
 *     message="A store with that name already exists."
 * )
 *
 * @Serializer\ExclusionPolicy("all")
 */
class ShopifyStore implements UserRelatedInterface
{
    use IdentityTrait, TimestampableEntity, BlameableEntity;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="shopifyStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=128)
     *
     * @Serializer\Expose
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $nonce;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $accessToken;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->user instanceof User ? $this->user->getId() : null;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(?string $nonce)
    {
        $this->nonce = $nonce;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function isVerified(): bool
    {
        return !empty($this->getAccessToken());
    }
}
