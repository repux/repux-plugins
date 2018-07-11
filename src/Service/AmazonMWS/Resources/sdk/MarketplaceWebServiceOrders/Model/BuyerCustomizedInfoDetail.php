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
 * @package  Marketplace Web Service Orders
 * @version  2013-09-01
 * Library Version: 2015-09-24
 * Generated: Fri Sep 25 20:06:28 GMT 2015
 */

namespace App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\Model;

use App\Service\AmazonMWS\Resources\sdk\MarketplaceWebServiceOrders\ClientModel;

/**
 * BuyerCustomizedInfoDetail
 *
 * Properties:
 * <ul>
 *
 * <li>CustomizedURL: string</li>
 *
 * </ul>
 */
class BuyerCustomizedInfoDetail extends ClientModel
{
    public function __construct($data = null)
    {
        $this->_fields = [
            'CustomizedURL' => ['FieldValue' => null, 'FieldType' => 'string'],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the CustomizedURL property.
     *
     * @return String CustomizedURL.
     */
    public function getCustomizedURL()
    {
        return $this->_fields['CustomizedURL']['FieldValue'];
    }

    /**
     * Set the value of the CustomizedURL property.
     *
     * @param string customizedURL
     *
     * @return this instance
     */
    public function setCustomizedURL($value)
    {
        $this->_fields['CustomizedURL']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if CustomizedURL is set.
     *
     * @return true if CustomizedURL is set.
     */
    public function isSetCustomizedURL()
    {
        return !is_null($this->_fields['CustomizedURL']['FieldValue']);
    }

    /**
     * Set the value of CustomizedURL, return this.
     *
     * @param customizedURL
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withCustomizedURL($value)
    {
        $this->setCustomizedURL($value);

        return $this;
    }
}
