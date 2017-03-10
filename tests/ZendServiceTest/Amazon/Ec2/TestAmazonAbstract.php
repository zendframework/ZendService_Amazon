<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Amazon\Ec2;

class TestAmazonAbstract extends \ZendService\Amazon\Ec2\AbstractEc2
{

    public function returnRegion()
    {
        return $this->_region;
    }

    public function testSign($params)
    {
        return $this->signParameters($params);
    }
}
