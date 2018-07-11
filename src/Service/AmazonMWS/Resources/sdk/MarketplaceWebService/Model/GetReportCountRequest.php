<?php
/**
 *  PHP Version 5
 *
 * @category    Amazon
 * @package     MarketplaceWebService
 * @copyright   Copyright 2009 Amazon Technologies, Inc.
 * @link        http://aws.amazon.com
 * @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 * @version     2009-01-01
 */

/*******************************************************************************
 *  Marketplace Web Service PHP5 Library
 *  Generated: Thu May 07 13:07:36 PDT 2009
 *
 */

namespace App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\Model;

use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebService\ClientModel;

/**
 * GetReportCountRequest
 *
 * Properties:
 * <ul>
 *
 * <li>Marketplace: string</li>
 * <li>Merchant: string</li>
 * <li>ReportTypeList: Model\TypeList</li>
 * <li>Acknowledged: bool</li>
 * <li>AvailableFromDate: string</li>
 * <li>AvailableToDate: string</li>
 *
 * </ul>
 */
class GetReportCountRequest extends ClientModel
{
    /**
     * Construct new GetReportCountRequest
     *
     * @param mixed $data DOMElement or Associative Array to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>Marketplace: string</li>
     * <li>Merchant: string</li>
     * <li>ReportTypeList: Model\TypeList</li>
     * <li>Acknowledged: bool</li>
     * <li>AvailableFromDate: string</li>
     * <li>AvailableToDate: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = [
            'Marketplace' => ['FieldValue' => null, 'FieldType' => 'string'],
            'Merchant' => ['FieldValue' => null, 'FieldType' => 'string'],
            'MWSAuthToken' => ['FieldValue' => null, 'FieldType' => 'string'],
            'ReportTypeList' => ['FieldValue' => null, 'FieldType' => 'TypeList'],
            'Acknowledged' => ['FieldValue' => null, 'FieldType' => 'bool'],
            'AvailableFromDate' => ['FieldValue' => null, 'FieldType' => 'DateTime'],
            'AvailableToDate' => ['FieldValue' => null, 'FieldType' => 'DateTime'],
        ];
        parent::__construct($data);
    }

    /**
     * Gets the value of the Marketplace property.
     *
     * @return string Marketplace
     */
    public function getMarketplace()
    {
        return $this->fields['Marketplace']['FieldValue'];
    }

    /**
     * Sets the value of the Marketplace property.
     *
     * @param string Marketplace
     *
     * @return this instance
     */
    public function setMarketplace($value)
    {
        $this->fields['Marketplace']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the Marketplace and returns this instance
     *
     * @param string $value Marketplace
     *
     * @return GetReportCountRequest instance
     */
    public function withMarketplace($value)
    {
        $this->setMarketplace($value);

        return $this;
    }

    /**
     * Checks if Marketplace is set
     *
     * @return bool true if Marketplace  is set
     */
    public function isSetMarketplace()
    {
        return !is_null($this->fields['Marketplace']['FieldValue']);
    }

    /**
     * Gets the value of the Merchant property.
     *
     * @return string Merchant
     */
    public function getMerchant()
    {
        return $this->fields['Merchant']['FieldValue'];
    }

    /**
     * Sets the value of the Merchant property.
     *
     * @param string Merchant
     *
     * @return this instance
     */
    public function setMerchant($value)
    {
        $this->fields['Merchant']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the Merchant and returns this instance
     *
     * @param string $value Merchant
     *
     * @return GetReportCountRequest instance
     */
    public function withMerchant($value)
    {
        $this->setMerchant($value);

        return $this;
    }

    /**
     * Checks if Merchant is set
     *
     * @return bool true if Merchant  is set
     */
    public function isSetMerchant()
    {
        return !is_null($this->fields['Merchant']['FieldValue']);
    }

    /**
     * Gets the value of the MWSAuthToken property.
     *
     * @return string MWSAuthToken
     */
    public function getMWSAuthToken()
    {
        return $this->fields['MWSAuthToken']['FieldValue'];
    }

    /**
     * Sets the value of the MWSAuthToken property.
     *
     * @param string MWSAuthToken
     *
     * @return this instance
     */
    public function setMWSAuthToken($value)
    {
        $this->fields['MWSAuthToken']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the MWSAuthToken and returns this instance
     *
     * @param string $value MWSAuthToken
     *
     * @return GetReportCountRequest instance
     */
    public function withMWSAuthToken($value)
    {
        $this->setMWSAuthToken($value);

        return $this;
    }

    /**
     * Checks if MWSAuthToken is set
     *
     * @return bool true if MWSAuthToken  is set
     */
    public function isSetMWSAuthToken()
    {
        return !is_null($this->fields['MWSAuthToken']['FieldValue']);
    }

    /**
     * Gets the value of the ReportTypeList.
     *
     * @return TypeList ReportTypeList
     */
    public function getReportTypeList()
    {
        return $this->fields['ReportTypeList']['FieldValue'];
    }

    /**
     * Sets the value of the ReportTypeList.
     *
     * @param TypeList ReportTypeList
     *
     * @return void
     */
    public function setReportTypeList($value)
    {
        $this->fields['ReportTypeList']['FieldValue'] = $value;

        return;
    }

    /**
     * Sets the value of the ReportTypeList  and returns this instance
     *
     * @param TypeList $value ReportTypeList
     *
     * @return GetReportCountRequest instance
     */
    public function withReportTypeList($value)
    {
        $this->setReportTypeList($value);

        return $this;
    }

    /**
     * Checks if ReportTypeList  is set
     *
     * @return bool true if ReportTypeList property is set
     */
    public function isSetReportTypeList()
    {
        return !is_null($this->fields['ReportTypeList']['FieldValue']);
    }

    /**
     * Gets the value of the Acknowledged property.
     *
     * @return bool Acknowledged
     */
    public function getAcknowledged()
    {
        return $this->fields['Acknowledged']['FieldValue'];
    }

    /**
     * Sets the value of the Acknowledged property.
     *
     * @param bool Acknowledged
     *
     * @return this instance
     */
    public function setAcknowledged($value)
    {
        $this->fields['Acknowledged']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the Acknowledged and returns this instance
     *
     * @param bool $value Acknowledged
     *
     * @return GetReportCountRequest instance
     */
    public function withAcknowledged($value)
    {
        $this->setAcknowledged($value);

        return $this;
    }

    /**
     * Checks if Acknowledged is set
     *
     * @return bool true if Acknowledged  is set
     */
    public function isSetAcknowledged()
    {
        return !is_null($this->fields['Acknowledged']['FieldValue']);
    }

    /**
     * Gets the value of the AvailableFromDate property.
     *
     * @return string AvailableFromDate
     */
    public function getAvailableFromDate()
    {
        return $this->fields['AvailableFromDate']['FieldValue'];
    }

    /**
     * Sets the value of the AvailableFromDate property.
     *
     * @param string AvailableFromDate
     *
     * @return this instance
     */
    public function setAvailableFromDate($value)
    {
        $this->fields['AvailableFromDate']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the AvailableFromDate and returns this instance
     *
     * @param string $value AvailableFromDate
     *
     * @return GetReportCountRequest instance
     */
    public function withAvailableFromDate($value)
    {
        $this->setAvailableFromDate($value);

        return $this;
    }

    /**
     * Checks if AvailableFromDate is set
     *
     * @return bool true if AvailableFromDate  is set
     */
    public function isSetAvailableFromDate()
    {
        return !is_null($this->fields['AvailableFromDate']['FieldValue']);
    }

    /**
     * Gets the value of the AvailableToDate property.
     *
     * @return string AvailableToDate
     */
    public function getAvailableToDate()
    {
        return $this->fields['AvailableToDate']['FieldValue'];
    }

    /**
     * Sets the value of the AvailableToDate property.
     *
     * @param string AvailableToDate
     *
     * @return this instance
     */
    public function setAvailableToDate($value)
    {
        $this->fields['AvailableToDate']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Sets the value of the AvailableToDate and returns this instance
     *
     * @param string $value AvailableToDate
     *
     * @return GetReportCountRequest instance
     */
    public function withAvailableToDate($value)
    {
        $this->setAvailableToDate($value);

        return $this;
    }

    /**
     * Checks if AvailableToDate is set
     *
     * @return bool true if AvailableToDate  is set
     */
    public function isSetAvailableToDate()
    {
        return !is_null($this->fields['AvailableToDate']['FieldValue']);
    }
}
