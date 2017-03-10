<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendServiceTest\Amazon\Authentication;

use PHPUnit\Framework\TestCase;
use ZendService\Amazon\Authentication;

/**
 * Amazon V1 authentication test case
 *
 * @category   Zend
 * @package    Zend_Service_Amazon_Authentication
 * @subpackage UnitTests
 */
class V1Test extends TestCase
{

    /**
     * @var Authentication\V1
     */
    private $amazon;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->amazon = new Authentication\V1(
            '0PN5J17HBGZHT7JJ3X82',
            'uV3F3YluFJax1cknvbcGwgjvx4QpvB+leU8dUj2o',
            '2007-12-01'
        );
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->amazon = null;
    }

    /**
     * Tests Authentication\V1::generateSignature()
     */
    public function testGenerateDevPaySignature()
    {
        $url = "https://ls.amazonaws.com/";
        $params = [];
        $params['Action'] = "ActivateHostedProduct";
        $params['Timestamp'] = "2009-11-11T13:52:38Z";

        $ret = $this->amazon->generateSignature($url, $params);

        $this->assertEquals('31Q2YlgABM5X3GkYQpGErcL10Xc=', $params['Signature']);
        $this->assertEquals(
            "ActionActivateHostedProductAWSAccessKeyId0PN5J17HBGZHT7JJ3X82Signature"
            . "Version1Timestamp2009-11-11T13:52:38ZVersion2007-12-01",
            $ret
        );
    }
}
