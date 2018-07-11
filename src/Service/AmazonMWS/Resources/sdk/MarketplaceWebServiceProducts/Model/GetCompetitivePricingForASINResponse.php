<?php
/*******************************************************************************
 * Copyright 2009-2015 Amazon Services. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 *
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at: http://aws.amazon.com/apache2.0
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *******************************************************************************
 * PHP Version 5
 *
 * @category Amazon
 * @package  Marketplace Web Service Products
 * @version  2011-10-01
 * Library Version: 2015-09-01
 * Generated: Thu Sep 10 06:52:22 PDT 2015
 */

namespace App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceProducts\Model;

use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceProducts\ClientModel;

/**
 * GetCompetitivePricingForASINResponse
 *
 * Properties:
 * <ul>
 *
 * <li>GetCompetitivePricingForASINResult: array</li>
 * <li>ResponseMetadata: ResponseMetadata</li>
 * <li>ResponseHeaderMetadata: ResponseHeaderMetadata</li>
 *
 * </ul>
 */
class GetCompetitivePricingForASINResponse extends ClientModel
{
    public function __construct($data = null)
    {
        $this->_fields = [
            'GetCompetitivePricingForASINResult' => [
                'FieldValue' => [],
                'FieldType' => ['GetCompetitivePricingForASINResult'],
            ],
            'ResponseMetadata' => ['FieldValue' => null, 'FieldType' => 'ResponseMetadata'],
            'ResponseHeaderMetadata' => ['FieldValue' => null, 'FieldType' => 'ResponseHeaderMetadata'],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the GetCompetitivePricingForASINResult property.
     *
     * @return List<GetCompetitivePricingForASINResult> GetCompetitivePricingForASINResult.
     */
    public function getGetCompetitivePricingForASINResult()
    {
        if ($this->_fields['GetCompetitivePricingForASINResult']['FieldValue'] == null) {
            $this->_fields['GetCompetitivePricingForASINResult']['FieldValue'] = [];
        }

        return $this->_fields['GetCompetitivePricingForASINResult']['FieldValue'];
    }

    /**
     * Set the value of the GetCompetitivePricingForASINResult property.
     *
     * @param array getCompetitivePricingForASINResult
     *
     * @return this instance
     */
    public function setGetCompetitivePricingForASINResult($value)
    {
        if (!$this->_isNumericArray($value)) {
            $value = [$value];
        }
        $this->_fields['GetCompetitivePricingForASINResult']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Clear GetCompetitivePricingForASINResult.
     */
    public function unsetGetCompetitivePricingForASINResult()
    {
        $this->_fields['GetCompetitivePricingForASINResult']['FieldValue'] = [];
    }

    /**
     * Check to see if GetCompetitivePricingForASINResult is set.
     *
     * @return true if GetCompetitivePricingForASINResult is set.
     */
    public function isSetGetCompetitivePricingForASINResult()
    {
        return !empty($this->_fields['GetCompetitivePricingForASINResult']['FieldValue']);
    }

    /**
     * Add values for GetCompetitivePricingForASINResult, return this.
     *
     * @param getCompetitivePricingForASINResult
     *             New values to add.
     *
     * @return This instance.
     */
    public function withGetCompetitivePricingForASINResult()
    {
        foreach (func_get_args() as $GetCompetitivePricingForASINResult) {
            $this->_fields['GetCompetitivePricingForASINResult']['FieldValue'][] = $GetCompetitivePricingForASINResult;
        }

        return $this;
    }

    /**
     * Get the value of the ResponseMetadata property.
     *
     * @return ResponseMetadata ResponseMetadata.
     */
    public function getResponseMetadata()
    {
        return $this->_fields['ResponseMetadata']['FieldValue'];
    }

    /**
     * Set the value of the ResponseMetadata property.
     *
     * @param ResponseMetadata responseMetadata
     *
     * @return this instance
     */
    public function setResponseMetadata($value)
    {
        $this->_fields['ResponseMetadata']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if ResponseMetadata is set.
     *
     * @return true if ResponseMetadata is set.
     */
    public function isSetResponseMetadata()
    {
        return !is_null($this->_fields['ResponseMetadata']['FieldValue']);
    }

    /**
     * Set the value of ResponseMetadata, return this.
     *
     * @param responseMetadata
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withResponseMetadata($value)
    {
        $this->setResponseMetadata($value);

        return $this;
    }

    /**
     * Get the value of the ResponseHeaderMetadata property.
     *
     * @return ResponseHeaderMetadata ResponseHeaderMetadata.
     */
    public function getResponseHeaderMetadata()
    {
        return $this->_fields['ResponseHeaderMetadata']['FieldValue'];
    }

    /**
     * Set the value of the ResponseHeaderMetadata property.
     *
     * @param ResponseHeaderMetadata responseHeaderMetadata
     *
     * @return this instance
     */
    public function setResponseHeaderMetadata($value)
    {
        $this->_fields['ResponseHeaderMetadata']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if ResponseHeaderMetadata is set.
     *
     * @return true if ResponseHeaderMetadata is set.
     */
    public function isSetResponseHeaderMetadata()
    {
        return !is_null($this->_fields['ResponseHeaderMetadata']['FieldValue']);
    }

    /**
     * Set the value of ResponseHeaderMetadata, return this.
     *
     * @param responseHeaderMetadata
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withResponseHeaderMetadata($value)
    {
        $this->setResponseHeaderMetadata($value);

        return $this;
    }

    /**
     * Construct GetCompetitivePricingForASINResponse from XML string
     *
     * @param $xml
     *        XML string to construct from
     *
     * @return GetCompetitivePricingForASINResponse
     */
    public static function fromXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $response = $xpath->query("//*[local-name()='GetCompetitivePricingForASINResponse']");
        if ($response->length == 1) {
            return new GetCompetitivePricingForASINResponse(($response->item(0)));
        } else {
            throw new Exception ("Unable to construct GetCompetitivePricingForASINResponse from provided XML.
                                  Make sure that GetCompetitivePricingForASINResponse is a root element");
        }
    }

    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<GetCompetitivePricingForASINResponse xmlns=\"http://mws.amazonservices.com/schema/Products/2011-10-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</GetCompetitivePricingForASINResponse>";

        return $xml;
    }
}
