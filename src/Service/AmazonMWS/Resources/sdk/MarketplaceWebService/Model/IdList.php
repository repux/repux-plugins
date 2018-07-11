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
 * IdList
 *
 * Properties:
 * <ul>
 *
 * <li>Id: string</li>
 *
 * </ul>
 */
class IdList extends ClientModel
{
    /**
     * Construct new IdList
     *
     * @param mixed $data DOMElement or Associative Array to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>Id: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = [
            'Id' => ['FieldValue' => [], 'FieldType' => ['string']],
        ];
        parent::__construct($data);
    }

    /**
     * Gets the value of the Id .
     *
     * @return array of string Id
     */
    public function getId()
    {
        return $this->fields['Id']['FieldValue'];
    }

    /**
     * Sets the value of the Id.
     *
     * @param string or an array of string Id
     *
     * @return this instance
     */
    public function setId($id)
    {
        if (!$this->isNumericArray($id)) {
            $id = [$id];
        }
        $this->fields['Id']['FieldValue'] = $id;

        return $this;
    }

    /**
     * Sets single or multiple values of Id list via variable number of arguments.
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withId($id1, $id2)</code>
     *
     * @param string $stringArgs one or more Id
     *
     * @return IdList  instance
     */
    public function withId($stringArgs)
    {
        foreach (func_get_args() as $id) {
            $this->fields['Id']['FieldValue'][] = $id;
        }

        return $this;
    }

    /**
     * Checks if Id list is non-empty
     *
     * @return bool true if Id list is non-empty
     */
    public function isSetId()
    {
        return count($this->fields['Id']['FieldValue']) > 0;
    }
}
