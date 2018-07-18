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

namespace App\Service\AmazonMWS\sdk\MarketplaceWebServiceProducts;

interface ClientInterface
{
    /**
     * Get Competitive Pricing For ASIN
     * Gets competitive pricing and related information for a product identified by
     * the MarketplaceId and ASIN.
     *
     * @param mixed $request array of parameters for GetCompetitivePricingForASIN request or
     *     GetCompetitivePricingForASIN object itself
     *
     * @see GetCompetitivePricingForASINRequest
     * @return GetCompetitivePricingForASINResponse
     *
     * @throws ClientException
     */
    public function getCompetitivePricingForASIN($request);

    /**
     * Get Competitive Pricing For SKU
     * Gets competitive pricing and related information for a product identified by
     * the SellerId and SKU.
     *
     * @param mixed $request array of parameters for GetCompetitivePricingForSKU request or GetCompetitivePricingForSKU
     *     object itself
     *
     * @see GetCompetitivePricingForSKURequest
     * @return GetCompetitivePricingForSKUResponse
     *
     * @throws ClientException
     */
    public function getCompetitivePricingForSKU($request);

    /**
     * Get Lowest Offer Listings For ASIN
     * Gets some of the lowest prices based on the product identified by the given
     * MarketplaceId and ASIN.
     *
     * @param mixed $request array of parameters for GetLowestOfferListingsForASIN request or
     *     GetLowestOfferListingsForASIN object itself
     *
     * @see GetLowestOfferListingsForASINRequest
     * @return GetLowestOfferListingsForASINResponse
     *
     * @throws ClientException
     */
    public function getLowestOfferListingsForASIN($request);

    /**
     * Get Lowest Offer Listings For SKU
     * Gets some of the lowest prices based on the product identified by the given
     * SellerId and SKU.
     *
     * @param mixed $request array of parameters for GetLowestOfferListingsForSKU request or
     *     GetLowestOfferListingsForSKU object itself
     *
     * @see GetLowestOfferListingsForSKURequest
     * @return GetLowestOfferListingsForSKUResponse
     *
     * @throws ClientException
     */
    public function getLowestOfferListingsForSKU($request);

    /**
     * Get Lowest Priced Offers For ASIN
     * Retrieves the lowest priced offers based on the product identified by the given
     *     ASIN.
     *
     * @param mixed $request array of parameters for GetLowestPricedOffersForASIN request or
     *     GetLowestPricedOffersForASIN object itself
     *
     * @see GetLowestPricedOffersForASINRequest
     * @return GetLowestPricedOffersForASINResponse
     *
     * @throws ClientException
     */
    public function getLowestPricedOffersForASIN($request);

    /**
     * Get Lowest Priced Offers For SKU
     * Retrieves the lowest priced offers based on the product identified by the given
     *     SellerId and SKU.
     *
     * @param mixed $request array of parameters for GetLowestPricedOffersForSKU request or GetLowestPricedOffersForSKU
     *     object itself
     *
     * @see GetLowestPricedOffersForSKURequest
     * @return GetLowestPricedOffersForSKUResponse
     *
     * @throws ClientException
     */
    public function getLowestPricedOffersForSKU($request);

    /**
     * Get Matching Product
     * GetMatchingProduct will return the details (attributes) for the
     * given ASIN.
     *
     * @param mixed $request array of parameters for GetMatchingProduct request or GetMatchingProduct object itself
     *
     * @see GetMatchingProductRequest
     * @return GetMatchingProductResponse
     *
     * @throws ClientException
     */
    public function getMatchingProduct($request);

    /**
     * Get Matching Product For Id
     * GetMatchingProduct will return the details (attributes) for the
     * given Identifier list. Identifer type can be one of [SKU|ASIN|UPC|EAN|ISBN|GTIN|JAN]
     *
     * @param mixed $request array of parameters for GetMatchingProductForId request or GetMatchingProductForId object
     *     itself
     *
     * @see GetMatchingProductForIdRequest
     * @return GetMatchingProductForIdResponse
     *
     * @throws ClientException
     */
    public function getMatchingProductForId($request);

    /**
     * Get My Price For ASIN
     * <!-- Wrong doc in current code -->
     *
     * @param mixed $request array of parameters for GetMyPriceForASIN request or GetMyPriceForASIN object itself
     *
     * @see GetMyPriceForASINRequest
     * @return GetMyPriceForASINResponse
     *
     * @throws ClientException
     */
    public function getMyPriceForASIN($request);

    /**
     * Get My Price For SKU
     * <!-- Wrong doc in current code -->
     *
     * @param mixed $request array of parameters for GetMyPriceForSKU request or GetMyPriceForSKU object itself
     *
     * @see GetMyPriceForSKURequest
     * @return GetMyPriceForSKUResponse
     *
     * @throws ClientException
     */
    public function getMyPriceForSKU($request);

    /**
     * Get Product Categories For ASIN
     * Gets categories information for a product identified by
     * the MarketplaceId and ASIN.
     *
     * @param mixed $request array of parameters for GetProductCategoriesForASIN request or GetProductCategoriesForASIN
     *     object itself
     *
     * @see GetProductCategoriesForASINRequest
     * @return GetProductCategoriesForASINResponse
     *
     * @throws ClientException
     */
    public function getProductCategoriesForASIN($request);

    /**
     * Get Product Categories For SKU
     * Gets categories information for a product identified by
     * the SellerId and SKU.
     *
     * @param mixed $request array of parameters for GetProductCategoriesForSKU request or GetProductCategoriesForSKU
     *     object itself
     *
     * @see GetProductCategoriesForSKURequest
     * @return GetProductCategoriesForSKUResponse
     *
     * @throws ClientException
     */
    public function getProductCategoriesForSKU($request);

    /**
     * Get Service Status
     * Returns the service status of a particular MWS API section. The operation
     * takes no input.
     * All API sections within the API are required to implement this operation.
     *
     * @param mixed $request array of parameters for GetServiceStatus request or GetServiceStatus object itself
     *
     * @see GetServiceStatusRequest
     * @return GetServiceStatusResponse
     *
     * @throws ClientException
     */
    public function getServiceStatus($request);

    /**
     * List Matching Products
     * ListMatchingProducts can be used to
     * find products that match the given criteria.
     *
     * @param mixed $request array of parameters for ListMatchingProducts request or ListMatchingProducts object itself
     *
     * @see ListMatchingProductsRequest
     * @return ListMatchingProductsResponse
     *
     * @throws ClientException
     */
    public function listMatchingProducts($request);
}
