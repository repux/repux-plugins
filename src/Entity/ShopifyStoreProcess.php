<?php

namespace App\Entity;

use App\Entity\Traits\IdentityTrait;
use App\Validator\Constraints\ShopifyStoreProcessParameters;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Serializer\ExclusionPolicy("all")
 */
class ShopifyStoreProcess
{
    const TYPE_IMPORT_ORDERS = 1;

    const STATUS_IDLE = 10;
    const STATUS_IN_PROGRESS = 20;
    const STATUS_SUCCESS = 30;
    const STATUS_EMPTY_RESPONSE = 31;
    const STATUS_ERROR = 40;

    const PARAMETER_PERIOD_THIS_MONTH = 1;
    const PARAMETER_PERIOD_LAST_MONTH = 2;
    const PARAMETER_PERIOD_LAST_QUARTER = 3;
    const PARAMETER_PERIOD_ALL = 'all';

    use IdentityTrait, TimestampableEntity;

    /**
     * @var ShopifyStore|null
     *
     * @ORM\ManyToOne(targetEntity="ShopifyStore")
     * @ORM\JoinColumn(name="shopify_store_id")
     *
     * @Assert\NotBlank()
     */
    private $shopifyStore;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     *
     * @ShopifyStoreProcessParameters()
     */
    private $parameters;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"default":0})
     *
     * @Serializer\Expose
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $data;

    protected $createdAt;

    public function getShopifyStore(): ?ShopifyStore
    {
        return $this->shopifyStore;
    }

    public function setShopifyStore(ShopifyStore $shopifyStore): void
    {
        $this->shopifyStore = $shopifyStore;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function setParameters(?string $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data)
    {
        $this->data = $data;
    }
}
