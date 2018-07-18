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
 * NumberOfOfferListingsList
 *
 * Properties:
 * <ul>
 *
 * <li>OfferListingCount: array</li>
 *
 * </ul>
 */
class NumberOfOfferListingsList extends ClientModel
{
    public function __construct($data = null)
    {
        $this->_fields = [
            'OfferListingCount' => ['FieldValue' => [], 'FieldType' => ['OfferListingCountType']],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the OfferListingCount property.
     *
     * @return List<OfferListingCountType> OfferListingCount.
     */
    public function getOfferListingCount()
    {
        if ($this->_fields['OfferListingCount']['FieldValue'] == null) {
            $this->_fields['OfferListingCount']['FieldValue'] = [];
        }

        return $this->_fields['OfferListingCount']['FieldValue'];
    }

    /**
     * Set the value of the OfferListingCount property.
     *
     * @param array offerListingCount
     *
     * @return this instance
     */
    public function setOfferListingCount($value)
    {
        if (!$this->_isNumericArray($value)) {
            $value = [$value];
        }
        $this->_fields['OfferListingCount']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Clear OfferListingCount.
     */
    public function unsetOfferListingCount()
    {
        $this->_fields['OfferListingCount']['FieldValue'] = [];
    }

    /**
     * Check to see if OfferListingCount is set.
     *
     * @return true if OfferListingCount is set.
     */
    public function isSetOfferListingCount()
    {
        return !empty($this->_fields['OfferListingCount']['FieldValue']);
    }

    /**
     * Add values for OfferListingCount, return this.
     *
     * @param offerListingCount
     *             New values to add.
     *
     * @return This instance.
     */
    public function withOfferListingCount()
    {
        foreach (func_get_args() as $OfferListingCount) {
            $this->_fields['OfferListingCount']['FieldValue'][] = $OfferListingCount;
        }

        return $this;
    }
}
