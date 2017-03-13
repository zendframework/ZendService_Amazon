<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon;

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 */
class Query extends Amazon
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * Search parameters
     *
     * @var array
     */
    protected $_search = [];

    /**
     * Search index
     *
     * @var string
     */
    protected $_searchIndex = null;

    // @codingStandardsIgnoreEnd

    /**
     * Prepares query parameters
     *
     * @param  string $method
     * @param  array  $args
     * @throws Exception\RuntimeException
     * @return Query Provides a fluent interface
     */
    public function __call($method, $args)
    {
        if (strtolower($method) === 'asin') {
            $this->_searchIndex = 'asin';
            $this->_search['ItemId'] = $args[0];
            return $this;
        }

        if (strtolower($method) === 'category') {
            $this->_searchIndex = $args[0];
            $this->_search['SearchIndex'] = $args[0];
        } elseif (isset($this->_search['SearchIndex']) || $this->_searchIndex !== null
            || $this->_searchIndex === 'asin'
        ) {
            $this->_search[$method] = $args[0];
        } else {
            throw new Exception\RuntimeException('You must set a category before setting the search parameters');
        }

        return $this;
    }

    /**
     * Search using the prepared query
     *
     * @return Zend_Service_Amazon_Item|Zend_Service_Amazon_ResultSet
     */
    public function search()
    {
        if ($this->_searchIndex === 'asin') {
            return $this->itemLookup($this->_search['ItemId'], $this->_search);
        }
        return $this->itemSearch($this->_search);
    }
}
