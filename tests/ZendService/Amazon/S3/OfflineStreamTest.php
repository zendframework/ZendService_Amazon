<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Amazon\S3;

use Zend\Http\Response;
use ZendService\Amazon\S3;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon_S3
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 * @group      Zend_Service_Amazon_S3
 */
class OfflineStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup a fake S3 object with a fake document inside it. Return the document
     * so we can set expectations appropriately.
     *
     * @return string
     */
    protected function setUpS3()
    {
        // Mock the S3 client:
        $s3 = $this->getMockBuilder('ZendService\Amazon\S3\S3')
            ->disableOriginalConstructor()
            ->setMethods(['getInfo', '_makeRequest', '_validBucketName'])
            ->getMock();

        // Create fake data.
        $document = '';
        for ($x = 0; $x < 20; $x++) {
            $document .= pack('C*', ...range(0, 255))
                . pack('C*', ...range(255, 0, -1));
        }
        $docSize = strlen($document);
        $s3->expects($this->once())->method('getInfo')
            ->will($this->returnValue(['size' => $docSize]));

        $callback = function ($a, $b, $c, $headers) use ($document) {
            // Parse values from "Bytes=x-y" string:
            $range = explode('-', explode('=', $headers['Range'])[1]);
            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_206);
            $chunk = substr($document, $range[0], $range[1] - $range[0] + 1);
            $response->setContent($chunk);
            return $response;
        };
        // Set up expectations and simulated response for current chunk:
        $s3->expects($this->any())->method('_makeRequest')
            ->with(
                $this->equalTo('GET'),
                $this->anything(),
                $this->equalTo(null),
                $this->anything()
            )->will($this->returnCallback($callback));

        // Register the mock client so it is called for all test:// URLs:
        $s3->registerAsClient('test');

        return $document;
    }

    /**
     * Test that the stream reader works in combination with fread().
     *
     * @return void
     */
    public function testFread()
    {
        // Set up the test document:
        $document = $this->setUpS3(512);

        // Run the test:
        $stream = new S3\Stream();
        stream_wrapper_register('test', get_class($stream));
        $handle = fopen('test://foo/bar', 'r');
        // Read document in two 500-byte chunks, make sure it is intact:
        $final = '';
        while ($chunk = fgets($handle)) {
            $final .= $chunk;
        }
        fclose($handle);
        $this->assertEquals($document, $final);
    }

    /**
     * Test stream reading to make sure that appropriate byte ranges are requested
     * in HTTP headers and that chunks are reassembled correctly. Obviously, being
     * a mock-based test, this cannot confirm that Amazon responds appropriately
     * to the HTTP request.
     *
     * @return void
     */
    public function testStreamRead()
    {
        // Set up the test document:
        $document = $this->setUpS3();
        $docSize = strlen($document);

        // Run the test:
        $stream = new S3\Stream();
        $stream->stream_open('test://foo/bar', 'r', 0, null);
        // Read document in two 500-byte chunks, make sure it is intact:
        $final = '';
        $chunkSize = 500;
        for ($requested = 0; $requested < $docSize; $requested += $chunkSize) {
            $final .= $stream->stream_read($chunkSize);
        }
        $this->assertEquals($document, $final);
    }
}
