<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendServiceTest\Amazon\S3;

use PHPUnit\Framework\TestCase;
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
class OfflineStreamTest extends TestCase
{
    /**
     * Setup a fake S3 object with a fake document inside it. Return the document
     * so we can set expectations appropriately.
     *
     * @param int $fileSizeTimes256 Data size in multiples of 256 bytes.
     * @param $rangeConstraint PHPUnit constraint for Range header value
     *
     * @return string
     */
    protected function setUpS3($fileSizeTimes256 = 10, $rangeConstraint = null)
    {
        // Mock the S3 client:
        $s3 = $this->getMockBuilder('ZendService\Amazon\S3\S3')
            ->disableOriginalConstructor()
            ->setMethods(['getInfo', '_makeRequest', '_validBucketName'])
            ->getMock();

        // Simulate a 500-byte file
        $file = str_repeat(
            pack('C*', ...range(0, 255)) . pack('C*', ...range(255, 0, -1)),
            $fileSizeTimes256
        );
        $fileSize = strlen($file);
        $s3->expects($this->once())->method('getInfo')
            ->will($this->returnValue(['size' => $fileSize]));

        $callback = function ($a, $b, $c, $headers) use ($file, $rangeConstraint) {
            if ($rangeConstraint) {
                $rangeConstraint = $this->logicalAnd(
                    $this->matches('bytes=%d-%d'),
                    $rangeConstraint
                );
            } else {
                $rangeConstraint = $this->matches('bytes=%d-%d');
            }
            $this->assertThat(
                $headers['Range'],
                $rangeConstraint
            );
            $range = explode('-', substr($headers['Range'], 6));
            $startPos = (int) $range[0];
            $endPos = (int) $range[1];
            $this->assertGreaterThanOrEqual(
                (int)$startPos,
                (int)$endPos,
                'Range header end position must be greater than or equal to start position'
            );
            $length = $endPos - $startPos + 1;

            // Simulate an appropriately-sized response to the current request
            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_206);
            $chunk = substr($file, (int) $startPos, $length);
            $response->setContent($chunk);
            return $response;
        };


        $s3->expects($this->any())->method('_makeRequest')
            ->with(
                $this->equalTo('GET'),
                $this->anything(),
                $this->equalTo(null),
                $this->arrayHasKey('Range')
            )->will($this->returnCallback($callback));

        // Register the mock client so it is called for all test:// URLs:
        $s3->registerAsClient('test');

        return $file;
    }

    /**
     * Test that the stream reader works in combination with fread().
     *
     * @return void
     */
    public function testFread()
    {
        // Set up the test document:
        $document = $this->setUpS3(20);

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
        // don't use equals, we don't want binary data output into terminal
        $this->assertTrue(
            $document === $final,
            'Resulting data does not match original'
        );
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
        $document = $this->setUpS3(20);

        // Run the test:
        $stream = new S3\Stream();
        $stream->stream_open('test://foo/bar', 'r', 0, null);
        // Read document in 500-byte chunks, make sure it is intact:
        $final = '';
        while ($chunk = $stream->stream_read(500)) {
            $final .= $chunk;
        }
        // don't use equals, we don't want binary data output into terminal
        $this->assertTrue(
            $document === $final,
            'Resulting data does not match original'
        );
    }

    /**
     * Test correct range constraint on first chunk.
     *
     * @return void
     */
    public function testRangeConstraint()
    {
        // Set up the test document:
        $document = $this->setUpS3(20, $this->equalTo('bytes=0-499'));

        // Run the test:
        $stream = new S3\Stream();
        $stream->stream_open('test://foo/bar', 'r', 0, null);
        // Read first 500-byte chunk:
        $chunk = $stream->stream_read(500);
        // don't use equals, we don't want binary data output into terminal
        $this->assertTrue(
            substr($document, 0, 500) === $chunk,
            'Resulting data does not match original'
        );
    }
}
