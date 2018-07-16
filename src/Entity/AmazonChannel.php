<?php

namespace App\Entity;

use App\Entity\Model\UserRelatedInterface;
use App\Entity\Traits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AmazonChannelRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Serializer\ExclusionPolicy("all")
 */
class AmazonChannel implements UserRelatedInterface
{
    use IdentityTrait, TimestampableEntity, BlameableEntity;

    const STATUS_IDLE = 10;
    const STATUS_IN_PROGRESS = 20;
    const STATUS_SUCCESS = 30;
    const STATUS_ERROR = 40;

    const REPORT_PROCESSING_STATUS_DONE = '_DONE_';
    const FEED_PROCESSING_STATUS_DONE = '_DONE_';

    const MARKETPLACE_CA = 'A2EUQ1WTGCTBG2';
    const MARKETPLACE_US = 'ATVPDKIKX0DER';
    const MARKETPLACE_MX = 'A1AM78C64UM0Y8';
    const MARKETPLACE_DE = 'A1PA6795UKMFR9';
    const MARKETPLACE_ES = 'A1RKKUPIHCS9HS';
    const MARKETPLACE_FR = 'A13V1IB3VIYZZH';
    const MARKETPLACE_IN = 'A21TJRUUN4KGV';
    const MARKETPLACE_IT = 'APJ6JRA9NG5V4';
    const MARKETPLACE_UK = 'A1F83G8C2ARO7P';
    const MARKETPLACE_JP = 'A1VC38T7YXB528';
    const MARKETPLACE_CN = 'AAHKV2X7AFYLW';

    const MARKETPLACE_REGION_CN = 'CN';
    const MARKETPLACE_REGION_NA = 'NA';
    const MARKETPLACE_REGION_EU = 'EU';

    /**
     * For merchant fulfilled products/orders
     *
     * @see https://sellercentral.amazon.com/forums/thread.jspa?messageID=99011
     */
    const FULFILMENT_CHANNEL_DEFAULT = 'DEFAULT';

    const FULFILMENT_CHANNEL_MFN = 'MFN';
    const FULFILMENT_CHANNEL_AFN = 'AFN';

    /**
     * @see http://docs.developer.amazonservices.com/en_UK/dev_guide/DG_Endpoints.html
     */
    private static $amazonMWSEndpoints = [
        self::MARKETPLACE_CA => 'https://mws.amazonservices.ca',     //Canada
        self::MARKETPLACE_US => 'https://mws.amazonservices.com',    //United States
        self::MARKETPLACE_MX => 'https://mws.amazonservices.com.mx', //Mexico
        self::MARKETPLACE_DE => 'https://mws-eu.amazonservices.com', //Germany
        self::MARKETPLACE_ES => 'https://mws-eu.amazonservices.com', //ES
        self::MARKETPLACE_FR => 'https://mws-eu.amazonservices.com', //France
        self::MARKETPLACE_IN => 'https://mws.amazonservices.in',     //India
        self::MARKETPLACE_IT => 'https://mws-eu.amazonservices.com', //Italy
        self::MARKETPLACE_UK => 'https://mws-eu.amazonservices.com', //United Kingdom
        self::MARKETPLACE_JP => 'https://mws.amazonservices.jp',     //Japan
        self::MARKETPLACE_CN => 'https://mws.amazonservices.com.cn', //China
    ];

    private static $amazonSiteUrls = [
        self::MARKETPLACE_US => 'https://www.amazon.com/gp/aag/main?seller=%s',
        self::MARKETPLACE_CN => 'https://www.amazon.cn/gp/aag/main?seller=%s',
        //TODO: Add others
    ];

    public static function getServiceUrlByMarketplaceId($marketplaceId)
    {
        if (isset(self::$amazonMWSEndpoints[$marketplaceId])) {
            return self::$amazonMWSEndpoints[$marketplaceId];
        }
    }

    private static $amazonDefaultCurrenciesIso = [
        self::MARKETPLACE_CA => 'CAD', //Canada
        self::MARKETPLACE_US => 'USD', //United States
        self::MARKETPLACE_MX => 'MXN', //Mexico
        self::MARKETPLACE_DE => 'EUR', //Germany
        self::MARKETPLACE_ES => 'EUR', //ES
        self::MARKETPLACE_FR => 'EUR', //France
        self::MARKETPLACE_IN => 'INR', //India
        self::MARKETPLACE_IT => 'EUR', //Italy
        self::MARKETPLACE_UK => 'GBP', //United Kingdom
        self::MARKETPLACE_JP => 'JPY', //Japan
        self::MARKETPLACE_CN => 'CNY', //China
    ];

    private static $amazonMarketPlaceAbbr = [
        self::MARKETPLACE_CA => 'CA', //Canada
        self::MARKETPLACE_US => 'US', //United States
        self::MARKETPLACE_MX => 'MX', //Mexico
        self::MARKETPLACE_DE => 'DE', //Germany
        self::MARKETPLACE_ES => 'ES', //ES
        self::MARKETPLACE_FR => 'FR', //France
        self::MARKETPLACE_IN => 'IN', //India
        self::MARKETPLACE_IT => 'IT', //Italy
        self::MARKETPLACE_UK => 'UK', //United Kingdom
        self::MARKETPLACE_JP => 'JP', //Japan
        self::MARKETPLACE_CN => 'CN', //China
    ];

    private static $amazonMarketAddress = [
        self::MARKETPLACE_CA => 'amazon.ca', //Canada
        self::MARKETPLACE_US => 'amazon.com', //United States
        self::MARKETPLACE_MX => 'amazon.com.mx', //Mexico
        self::MARKETPLACE_CN => 'amazon.cn', //China
    ];

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     *
     * @Serializer\Expose
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     *
     * @Serializer\Expose
     */
    private $merchantId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     * @Assert\NotBlank()
     *
     * @Serializer\Expose
     */
    private $marketplaceId;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotBlank()
     *
     */
    private $apiToken;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default":false})
     *
     * @Serializer\Expose
     */
    private $authenticated;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, options={"default":0})
     *
     * @Serializer\Expose
     */
    private $status;

    /**
     * @var string
     *
     * sell in:
     *  United States:
     * $serviceUrl = "https://mws.amazonservices.com";
     *  United Kingdom
     * $serviceUrl = "https://mws.amazonservices.co.uk";
     *  Germany
     * $serviceUrl = "https://mws.amazonservices.de";
     *  France
     * $serviceUrl = "https://mws.amazonservices.fr";
     *  Italy
     * $serviceUrl = "https://mws.amazonservices.it";
     *  Japan
     * $serviceUrl = "https://mws.amazonservices.jp";
     * China
     * $serviceUrl = "https://mws.amazonservices.com.cn";
     *  Canada
     * $serviceUrl = "https://mws.amazonservices.ca";
     * India
     * $serviceUrl = "https://mws.amazonservices.in";
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     *
     */
    private $serviceUrl = 'http://127.0.0.1:18080';

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        $this->setAuthenticated(false);
    }

    /**
     * @return string
     */
    public function getMarketplaceId()
    {
        return $this->marketplaceId;
    }

    /**
     * @param string $marketplaceId
     */
    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;

        $this->setAuthenticated(false);
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        $this->setAuthenticated(false);
    }

    /**
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param boolean $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @param string $serviceUrl
     */
    public function setServiceUrl($serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
    }

    /**
     * @return mixed
     */
    public function getDefaultCurrencyIso()
    {
        return self::$amazonDefaultCurrenciesIso[$this->getMarketplaceId()];
    }

    /**
     * @return string|null
     */
    public function getSiteUrl()
    {
        if (isset(self::$amazonSiteUrls[$this->marketplaceId])) {
            return sprintf(self::$amazonSiteUrls[$this->marketplaceId], $this->merchantId);
        }
    }

    /**
     */
    public function getMarketplaceAbbrLabel()
    {
        return $this->getMarketplaceAbbr();
    }

    /**
     * @return string
     */
    public function getMarketplaceAbbr()
    {
        if (isset(self::$amazonMarketPlaceAbbr[$this->marketplaceId])) {
            return self::$amazonMarketPlaceAbbr[$this->marketplaceId];
        }
        throw new \InvalidArgumentException(
            "No marketplace abbreviation found for marketplace with id '{$this->marketplaceId}'"
        );
    }

    /**
     * @return string
     */
    public function getMarketplaceAddress()
    {
        if (isset(self::$amazonMarketAddress[$this->marketplaceId])) {
            return self::$amazonMarketAddress[$this->marketplaceId];
        }
        throw new \InvalidArgumentException(
            "No marketplace address found for marketplace with id '{$this->marketplaceId}'"
        );
    }

    /**
     * @param $marketplaceId
     * @return mixed
     */
    public static function getMarketplaceAbbrByMarketplaceId($marketplaceId)
    {
        if (isset(self::$amazonMarketPlaceAbbr[$marketplaceId])) {
            return self::$amazonMarketPlaceAbbr[$marketplaceId];
        }
        throw new \InvalidArgumentException(
            "No marketplace abbreviation found for marketplace with id '{$marketplaceId}'"
        );
    }

    /**
     * @param $marketplaceId
     * @return string
     */
    public static function getMarketplaceRegionByMarketplaceId($marketplaceId)
    {
        switch ($marketplaceId) {
            case self::MARKETPLACE_CN:
                return self::MARKETPLACE_REGION_CN;
            case self::MARKETPLACE_CA:
            case self::MARKETPLACE_US:
            case self::MARKETPLACE_MX:
                return self::MARKETPLACE_REGION_NA;
            default:
                throw new \InvalidArgumentException(
                    "No marketplace region abbreviation found for marketplace with id '{$marketplaceId}'"
                );
        }
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }
}
