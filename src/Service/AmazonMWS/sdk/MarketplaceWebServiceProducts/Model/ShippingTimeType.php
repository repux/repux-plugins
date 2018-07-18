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

namespace App\Service\AmazonMWS\sdk\MarketplaceWebServiceProducts\Model;

use App\Service\AmazonMWS\sdk\MarketplaceWebServiceProducts\ClientModel;

/**
 * ShippingTimeType
 *
 * Properties:
 * <ul>
 *
 * <li>Max: string</li>
 *
 * </ul>
 */
class ShippingTimeType extends ClientModel
{
    public function __construct($data = null)
    {
        $this->_fields = [
            'Max' => ['FieldValue' => null, 'FieldType' => 'string'],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the Max property.
     *
     * @return String Max.
     */
    public function getMax()
    {
        return $this->_fields['Max']['FieldValue'];
    }

    /**
     * Set the value of the Max property.
     *
     * @param string max
     *
     * @return this instance
     */
    public function setMax($value)
    {
        $this->_fields['Max']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if Max is set.
     *
     * @return true if Max is set.
     */
    public function isSetMax()
    {
        return !is_null($this->_fields['Max']['FieldValue']);
    }

    /**
     * Set the value of Max, return this.
     *
     * @param max
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withMax($value)
    {
        $this->setMax($value);

        return $this;
    }
}
