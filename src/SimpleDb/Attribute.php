<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\SimpleDb;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 */
class Attribute
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * @var string
     */
    protected $_itemName;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var array
     */
    protected $_values;

    // @codingStandardsIgnoreEnd

    /**
     * Constructor
     *
     * @param string $itemName
     * @param string $name
     * @param array $values
     */
    public function __construct($itemName, $name, $values)
    {
        $this->_itemName = $itemName;
        $this->_name     = $name;

        if (! is_array($values)) {
            $this->_values = [$values];
        } else {
            $this->_values = $values;
        }
    }

    /**
     * Return the item name to which the attribute belongs
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->_itemName;
    }

    /**
     * Retrieve attribute values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Retrieve the attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Add value
     *
     * @param  mixed $value
     * @return void
     */
    public function addValue($value)
    {
        if (is_array($value)) {
            $this->_values += $value;
        } else {
            $this->_values[] = $value;
        }
    }

    /**
     * @param mixed $values
     */
    public function setValues($values)
    {
        if (! is_array($values)) {
            $values = [$values];
        }
        $this->_values = $values;
    }
}
