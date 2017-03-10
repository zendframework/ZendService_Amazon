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

use PHPUnit\Framework\TestCase;
use ZendService\Amazon\Ec2;
use ZendService\Amazon\Ec2\Exception\RuntimeException;

/**
 * ZendService\Amazon\Ec2 test case.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 * @group      Zend_Service_Amazon_Ec2
 */
class Ec2Test extends TestCase
{

    /**
     * @var ZendService\Amazon\Ec2
     */
    private $ec2Instance;

    public function testFactoryReturnsKeyPairObject()
    {
        $object = Ec2\Ec2::factory('keypair', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Keypair', $object);
    }

    public function testFactoryReturnsElasticIpObject()
    {
        $object = Ec2\Ec2::factory('elasticip', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Elasticip', $object);
    }

    public function testFactoryReturnsEbsObject()
    {
        $object = Ec2\Ec2::factory('ebs', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Ebs', $object);
    }

    public function testFactoryReturnsAvailabilityZonesObject()
    {
        $object = Ec2\Ec2::factory('availabilityzones', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\AvailabilityZones', $object);
    }

    public function testFactoryReturnImageObject()
    {
        $object = Ec2\Ec2::factory('image', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Image', $object);
    }

    public function testFactoryReturnsInstanceObject()
    {
        $object = Ec2\Ec2::factory('instance', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Instance', $object);
    }

    public function testFactoryReturnsSecurityGroupsObject()
    {
        $object = Ec2\Ec2::factory('security', 'access_key', 'secret_access_key');
        $this->assertInstanceOf('ZendService\Amazon\Ec2\Securitygroups', $object);
    }

    public function testFactoryWillFailInvalidSection()
    {
        $this->expectException(RuntimeException::class);
        $object = Ec2\Ec2::factory('avaavaavailabilityzones', 'access_key', 'secret_access_key');
    }
}
