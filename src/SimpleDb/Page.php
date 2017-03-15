<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\SimpleDb;

/**
 * The Custom Exception class that allows you to have access to the AWS Error Code.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 */
class Page
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /** @var string Page data */
    protected $_data;

    /** @var string|null Token identifying page */
    protected $_token;

    // @codingStandardsIgnoreEnd

    /**
     * Constructor
     *
     * @param string $data
     * @param string $token
     */
    public function __construct($data, $token = null)
    {
        $this->_data  = $data;
        $this->_token = $token;
    }

    /**
     * Retrieve page data
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Retrieve token
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Determine whether this is the last page of data
     *
     * @return bool
     */
    public function isLast()
    {
        return (null === $this->_token);
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return "Page with token: " . $this->_token
             . "\n and data: " . $this->_data;
    }
}
