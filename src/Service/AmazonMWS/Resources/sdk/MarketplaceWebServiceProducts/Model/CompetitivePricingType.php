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
 * CompetitivePricingType
 *
 * Properties:
 * <ul>
 *
 * <li>CompetitivePrices: CompetitivePriceList</li>
 * <li>NumberOfOfferListings: NumberOfOfferListingsList</li>
 * <li>TradeInValue: MoneyType</li>
 *
 * </ul>
 */
class CompetitivePricingType extends ClientModel
{
    public function __construct($data = null)
    {
        $this->_fields = [
            'CompetitivePrices' => ['FieldValue' => null, 'FieldType' => 'CompetitivePriceList'],
            'NumberOfOfferListings' => ['FieldValue' => null, 'FieldType' => 'NumberOfOfferListingsList'],
            'TradeInValue' => ['FieldValue' => null, 'FieldType' => 'MoneyType'],
        ];
        parent::__construct($data);
    }

    /**
     * Get the value of the CompetitivePrices property.
     *
     * @return CompetitivePriceList CompetitivePrices.
     */
    public function getCompetitivePrices()
    {
        return $this->_fields['CompetitivePrices']['FieldValue'];
    }

    /**
     * Set the value of the CompetitivePrices property.
     *
     * @param CompetitivePriceList competitivePrices
     *
     * @return this instance
     */
    public function setCompetitivePrices($value)
    {
        $this->_fields['CompetitivePrices']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if CompetitivePrices is set.
     *
     * @return true if CompetitivePrices is set.
     */
    public function isSetCompetitivePrices()
    {
        return !is_null($this->_fields['CompetitivePrices']['FieldValue']);
    }

    /**
     * Set the value of CompetitivePrices, return this.
     *
     * @param competitivePrices
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withCompetitivePrices($value)
    {
        $this->setCompetitivePrices($value);

        return $this;
    }

    /**
     * Get the value of the NumberOfOfferListings property.
     *
     * @return NumberOfOfferListingsList NumberOfOfferListings.
     */
    public function getNumberOfOfferListings()
    {
        return $this->_fields['NumberOfOfferListings']['FieldValue'];
    }

    /**
     * Set the value of the NumberOfOfferListings property.
     *
     * @param NumberOfOfferListingsList numberOfOfferListings
     *
     * @return this instance
     */
    public function setNumberOfOfferListings($value)
    {
        $this->_fields['NumberOfOfferListings']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if NumberOfOfferListings is set.
     *
     * @return true if NumberOfOfferListings is set.
     */
    public function isSetNumberOfOfferListings()
    {
        return !is_null($this->_fields['NumberOfOfferListings']['FieldValue']);
    }

    /**
     * Set the value of NumberOfOfferListings, return this.
     *
     * @param numberOfOfferListings
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withNumberOfOfferListings($value)
    {
        $this->setNumberOfOfferListings($value);

        return $this;
    }

    /**
     * Get the value of the TradeInValue property.
     *
     * @return MoneyType TradeInValue.
     */
    public function getTradeInValue()
    {
        return $this->_fields['TradeInValue']['FieldValue'];
    }

    /**
     * Set the value of the TradeInValue property.
     *
     * @param MoneyType tradeInValue
     *
     * @return this instance
     */
    public function setTradeInValue($value)
    {
        $this->_fields['TradeInValue']['FieldValue'] = $value;

        return $this;
    }

    /**
     * Check to see if TradeInValue is set.
     *
     * @return true if TradeInValue is set.
     */
    public function isSetTradeInValue()
    {
        return !is_null($this->_fields['TradeInValue']['FieldValue']);
    }

    /**
     * Set the value of TradeInValue, return this.
     *
     * @param tradeInValue
     *             The new value to set.
     *
     * @return This instance.
     */
    public function withTradeInValue($value)
    {
        $this->setTradeInValue($value);

        return $this;
    }
}
