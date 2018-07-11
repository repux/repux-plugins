<?php

namespace App\Entity;

use App\Entity\Traits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Serializer\ExclusionPolicy("all")
 */
class ChannelAmazonProcess
{
    const TYPE_IMPORT_ORDERS = 1;
    const TYPE_IMPORT_CUSTOMERS = 2;
    const TYPE_IMPORT_STOCKS = 3;

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
     * @var ChannelAmazon|null
     *
     * @ORM\ManyToOne(targetEntity="ChannelAmazon")
     * @ORM\JoinColumn(name="channel_amazon_id", referencedColumnName="id")
     *
     * @Assert\NotBlank()
     */
    private $channelAmazon;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Expose
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $parameters;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", options={"default":0})
     *
     * @Serializer\Expose
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     */
    private $data;

    /**
     * @Serializer\Expose
     */
    protected $createdAt;

    public function getChannelAmazon()
    {
        return $this->channelAmazon;
    }

    public function setChannelAmazon(ChannelAmazon $channelAmazon)
    {
        $this->channelAmazon = $channelAmazon;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(int $type)
    {
        $this->type = $type;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(string $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(string $data)
    {
        $this->data = $data;
    }
}
