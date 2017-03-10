<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Amazon\Sqs;

use PHPUnit\Framework\TestCase;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 * @group      Zend_Service_Amazon_Sqs
 */
class OnlineTest extends TestCase
{
    /**
     * Reference to Amazon service consumer object
     *
     * @var Zend_Service_Amazon_Sqs
     */
    protected $amazon;

    /**
     * Socket based HTTP client adapter
     *
     * @var Zend_Http_Client_Adapter_Socket
     */
    protected $httpClientAdapterSocket;

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp()
    {
        if (! constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ENABLED')) {
            $this->markTestSkipped('Zend_Service_Amazon online tests are not enabled');
        }
        $this->amazon = new \ZendService\Amazon\Sqs\Sqs(
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'),
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_SECRETKEY')
        );

        $this->queue_name = constant('TESTS_ZEND_SERVICE_AMAZON_SQS_QUEUE');

        $this->httpClientAdapterSocket = new \Zend\Http\Client\Adapter\Socket();

        $this->amazon->getHttpClient()
                      ->setAdapter($this->httpClientAdapterSocket);
    }

    /**
     * Test SQS methods
     *
     * @return void
     */
    public function testSqs()
    {
        $queue_url = $this->amazon->create($this->queue_name, 45);
        $timeout = $this->amazon->getAttribute($queue_url, 'VisibilityTimeout');
        $this->assertEquals(45, $timeout, 'VisibilityTimeout attribute is not 45');

        $test_msg = 'this is a test';
        $this->amazon->send($queue_url, $test_msg);

        $messages = $this->amazon->receive($queue_url);

        foreach ($messages as $message) {
            $this->assertEquals($test_msg, $message['body']);
        }

        foreach ($messages as $message) {
            $result = $this->amazon->deleteMessage($queue_url, $message['handle']);
            $this->assertTrue($result, 'Message was not deleted');
        }

        $count = $this->amazon->count($queue_url);
        $this->assertEquals(0, $count);

        $this->amazon->delete($queue_url);
    }

    /**
     * Tear down the test case
     *
     * @return void
     */
    public function tearDown()
    {
        if (! constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ENABLED')) {
            return;
        }
        unset($this->amazon);
    }
}
