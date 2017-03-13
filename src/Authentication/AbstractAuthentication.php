<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Authentication;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Authentication
 */
abstract class AbstractAuthentication
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * @var string
     */
    protected $_accessKey;

    /**
     * @var string
     */
    protected $_secretKey;

    /**
     * @var string
     */
    protected $_apiVersion;

    // @codingStandardsIgnoreEnd

    /**
     * Constructor
     *
     * @param  string $accessKey
     * @param  string $secretKey
     * @param  string $apiVersion
     */
    public function __construct($accessKey, $secretKey, $apiVersion)
    {
        $this->setAccessKey($accessKey);
        $this->setSecretKey($secretKey);
        $this->setApiVersion($apiVersion);
    }

    /**
     * Set access key
     *
     * @param  string $accessKey
     * @return void
     */
    public function setAccessKey($accessKey)
    {
        $this->_accessKey = $accessKey;
    }

    /**
     * Set secret key
     *
     * @param  string $secretKey
     * @return void
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey = $secretKey;
    }

    /**
     * Set API version
     *
     * @param  string $apiVersion
     * @return void
     */
    public function setApiVersion($apiVersion)
    {
        $this->_apiVersion = $apiVersion;
    }
}
