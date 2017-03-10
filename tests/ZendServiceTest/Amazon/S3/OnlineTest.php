<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Amazon\S3;

use PHPUnit\Framework\TestCase;
use ZendService\Amazon\S3;
use ZendService\Amazon\S3\Exception\InvalidArgumentException;
use ZendService\Amazon\S3\Exception\RuntimeException;
use Zend\Http\Response;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon_S3
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 * @group      Zend_Service_Amazon_S3
 */
class OnlineTest extends TestCase
{
    /**
     * Reference to Amazon service consumer object
     *
     * @var Zend_Service_Amazon_S3
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
        $this->amazon = new S3\S3(
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'),
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_SECRETKEY')
        );
        $this->nosuchbucket = "nonexistingbucketnamewhichnobodyshoulduse";
        $this->httpClientAdapterSocket = new \Zend\Http\Client\Adapter\Socket();

        $this->bucket = constant('TESTS_ZEND_SERVICE_AMAZON_S3_BUCKET');

        $this->amazon->getHttpClient()
                      ->setAdapter($this->httpClientAdapterSocket);
    }

    /**
     * Test creating bucket
     *
     * @return void
     */
    public function testCreateBucket()
    {
        $this->amazon->createBucket($this->bucket);
        $this->assertTrue($this->amazon->isBucketAvailable($this->bucket));
        $list = $this->amazon->getBuckets();
        $this->assertContains($this->bucket, $list);
    }

    /**
     * Test creating bucket with location
     * ZF-6728
     *
     */
    public function testCreateBucketEU()
    {
        // make sure that we use different bucket,
        // as sometimes delete operation fails to propagate in one zone before you
        // attempt to recreate it in another
        $this->bucket = $this->bucket . 'eu';
        $this->amazon->createBucket($this->bucket, 'EU');
        $this->assertTrue($this->amazon->isBucketAvailable($this->bucket));
        $list = $this->amazon->getBuckets();
        $this->assertContains($this->bucket, $list);
    }

    /**
     * Test bucket availability
     */
    public function testIsBucketAvailable()
    {
        $this->assertFalse(
            $this->amazon->isBucketAvailable($this->bucket),
            "Bucket should not be available before it's created."
        );
        $this->assertTrue(
            $this->amazon->createBucket($this->bucket),
            "Creating bucket should work."
        );
        $this->assertTrue(
            $this->amazon->isBucketAvailable($this->bucket),
            "Bucket should now be available."
        );

        $this->assertFalse(
            $this->amazon->isBucketAvailable(uniqid('zftest.nonexisting-', true)),
            "That bucket really shouldn't exist."
        );
    }

    /**
     * Test creating object
     *
     * @return void
     */
    public function testCreateObject()
    {
        $this->amazon->createBucket($this->bucket);
        $this->amazon->putObject($this->bucket."/zftest", "testdata");
        $this->assertEquals("testdata", $this->amazon->getObject($this->bucket."/zftest"));
    }

    /**
     * Get object using streaming and temp files
     *
     */
    public function testGetObjectStream()
    {
        $this->amazon->createBucket($this->bucket);
        $this->amazon->putObject($this->bucket."/zftest", "testdata");
        $response = $this->amazon->getObjectStream($this->bucket."/zftest");

        $this->assertTrue($response instanceof Response\Stream, 'The test did not return stream response');
        $this->assertTrue(is_resource($response->getStream()), 'Request does not contain stream!');

        $stream_name = $response->getStreamName();

        $stream_read = stream_get_contents($response->getStream());
        $file_read = file_get_contents($stream_name);

        $this->assertEquals("testdata", $stream_read, 'Downloaded stream does not seem to match!');
        $this->assertEquals("testdata", $file_read, 'Downloaded file does not seem to match!');
    }

    /**
     * Get object using streaming and specific files
     *
     */
    public function testGetObjectStreamNamed()
    {
        $this->amazon->createBucket($this->bucket);
        $this->amazon->putObject($this->bucket."/zftest", "testdata");
        $outfile = tempnam(sys_get_temp_dir(), "output");

        $response = $this->amazon->getObjectStream($this->bucket."/zftest", $outfile);

        $this->assertTrue($response instanceof Response\Stream, 'The test did not return stream response');
        $this->assertTrue(is_resource($response->getStream()), 'Request does not contain stream!');

        $this->assertEquals($outfile, $response->getStreamName());

        $stream_read = stream_get_contents($response->getStream());
        $file_read = file_get_contents($outfile);

        $this->assertEquals("testdata", $stream_read, 'Downloaded stream does not seem to match!');
        $this->assertEquals("testdata", $file_read, 'Downloaded file does not seem to match!');
    }

    /**
     * Test getting info
     *
     * @return void
     */
    public function testGetInfo()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $info = $this->amazon->getInfo($this->bucket."/zftest");
        $this->assertEquals('"'.md5($data).'"', $info["etag"]);
        $this->assertEquals(strlen($data), $info["size"]);

        $this->amazon->putObject($this->bucket."/zftest.jpg", $data, null);
        $info = $this->amazon->getInfo($this->bucket."/zftest.jpg");
        $this->assertEquals('image/jpeg', $info["type"]);
    }

    public function testNoBucket()
    {
        $this->assertFalse($this->amazon->putObject($this->nosuchbucket."/zftest", "testdata"));
        $this->assertFalse($this->amazon->getObject($this->nosuchbucket."/zftest"));
        $this->assertFalse($this->amazon->getObjectsByBucket($this->nosuchbucket));
    }

    public function testNoObject()
    {
        $this->amazon->createBucket($this->bucket);
        $this->assertFalse($this->amazon->getObject($this->bucket."/zftest-no-such-object/in/there"));
        $this->assertFalse($this->amazon->getInfo($this->bucket."/zftest-no-such-object/in/there"));
    }

    public function testOverwriteObject()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $info = $this->amazon->getInfo($this->bucket."/zftest");
        $this->assertEquals('"'.md5($data).'"', $info["etag"]);
        $this->assertEquals(strlen($data), $info["size"]);

        $data = "testdata with some other data";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $info = $this->amazon->getInfo($this->bucket."/zftest");
        $this->assertEquals('"'.md5($data).'"', $info["etag"]);
        $this->assertEquals(strlen($data), $info["size"]);
    }

    public function testRemoveObject()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $this->amazon->removeObject($this->bucket."/zftest", $data);
        $this->assertFalse($this->amazon->getObject($this->bucket."/zftest"));
        $this->assertFalse($this->amazon->getInfo($this->bucket."/zftest"));
    }

    public function testRemoveBucket()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $this->amazon->cleanBucket($this->bucket);
        $this->amazon->removeBucket($this->bucket);

        // otherwise amazon sends cached data
        sleep(2);
        $this->assertFalse($this->amazon->isObjectAvailable($this->bucket."/zftest"), "Object shouldn't be available.");
        $this->assertFalse($this->amazon->getObjectsByBucket($this->bucket), "Bucket should be empty.");
        $this->assertFalse($this->amazon->isBucketAvailable($this->bucket), "Bucket shouldn't be available.");
        $list = $this->amazon->getBuckets();
        $this->assertNotContains($this->bucket, $list);
    }

    protected function fileTest($filename, $object, $type, $exp_type, $stream = false)
    {
        if ($stream) {
            $this->amazon->putFile($filename, $object, [S3\S3::S3_CONTENT_TYPE_HEADER => $type]);
        } else {
            $this->amazon->putFileStream($filename, $object, [S3\S3::S3_CONTENT_TYPE_HEADER => $type]);
        }

        $data = file_get_contents($filename);

        $this->assertTrue($this->amazon->isObjectAvailable($object));

        $info = $this->amazon->getInfo($object);

        $this->assertEquals('"'.md5_file($filename).'"', $info["etag"]);
        $this->assertEquals(filesize($filename), $info["size"]);
        $this->assertEquals($exp_type, $info["type"]);

        $fdata = $this->amazon->getObject($object);
        $this->assertEquals($data, $fdata);
    }

    public function testPutFile()
    {
        $filedir = __DIR__."/_files/";
        $this->amazon->createBucket($this->bucket);

        $this->fileTest($filedir."testdata", $this->bucket."/zftestfile", null, 'binary/octet-stream');
        $this->fileTest($filedir."testdata", $this->bucket."/zftestfile2", 'text/plain', 'text/plain');
        $this->fileTest($filedir."testdata.html", $this->bucket."/zftestfile3", null, 'text/html');
        $this->fileTest($filedir."testdata.html", $this->bucket."/zftestfile3.html", 'text/plain', 'text/plain');
    }

    public function testPutFileStream()
    {
        $filedir = __DIR__."/_files/";
        $this->amazon->createBucket($this->bucket);

        $this->fileTest($filedir."testdata", $this->bucket."/zftestfile", null, 'binary/octet-stream', true);
        $this->fileTest($filedir."testdata", $this->bucket."/zftestfile2", 'text/plain', 'text/plain', true);
        $this->fileTest($filedir."testdata.html", $this->bucket."/zftestfile3", null, 'text/html', true);
        $this->fileTest($filedir."testdata.html", $this->bucket."/zftestfile3.html", 'text/plain', 'text/plain', true);
    }

    /**
     * Since exception post-condition is tested as well,
     * two tests are created for a given exception
     * @see testPutNoFile
     */
    public function testPutNoFileException()
    {
        $filedir = __DIR__."/_files/";

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read file ' . $filedir."nosuchfile");

        $this->amazon->putFile($filedir."nosuchfile", $this->bucket."/zftestfile");
    }

    /**
     * Since exception post-condition is tested as well,
     * two tests are created for a given exception
     * @see testPutNoFileException
     */
    public function testPutNoFile()
    {
        $filedir = __DIR__."/_files/";
        try {
            $this->amazon->putFile($filedir."nosuchfile", $this->bucket."/zftestfile");
        } catch (S3\Exception\RuntimeException $e) {
            $this->assertFalse($this->amazon->isObjectAvailable($this->bucket."/zftestfile"));
            return;
        }
        $this->fail('Expected exception not thrown');
    }

    /**
     * @depends testCreateBucket
     * @depends testCreateObject
     */
    public function testCopyObject()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $info1 = $this->amazon->getInfo($this->bucket."/zftest");

        $this->amazon->copyObject($this->bucket."/zftest", $this->bucket."/zftest2");
        $this->assertTrue($this->amazon->isObjectAvailable($this->bucket."/zftest"));
        $this->assertTrue($this->amazon->isObjectAvailable($this->bucket."/zftest2"));
        $info2 = $this->amazon->getInfo($this->bucket."/zftest2");

        $this->assertEquals($info1['etag'], $info2['etag']);
    }

    /**
     * @depends testCopyObject
     * @depends testRemoveObject
     */
    public function testMoveObject()
    {
        $this->amazon->createBucket($this->bucket);
        $data = "testdata";

        $this->amazon->putObject($this->bucket."/zftest", $data);
        $info1 = $this->amazon->getInfo($this->bucket."/zftest");

        $this->amazon->moveObject($this->bucket."/zftest", $this->bucket."/zftest2");
        $this->assertFalse($this->amazon->isObjectAvailable($this->bucket."/zftest"));
        $this->assertTrue($this->amazon->isObjectAvailable($this->bucket."/zftest2"));
        $info2 = $this->amazon->getInfo($this->bucket."/zftest2");

        $this->assertEquals($info1['etag'], $info2['etag']);
    }

    public function testObjectEncoding()
    {
        $this->amazon->createBucket($this->bucket);

        $this->amazon->putObject($this->bucket."/this is a 100% test", "testdata");
        $this->assertEquals("testdata", $this->amazon->getObject($this->bucket."/this is a 100% test"));

        $this->amazon->putObject($this->bucket."/это тоже тест!", "testdata123");
        $this->assertEquals("testdata123", $this->amazon->getObject($this->bucket."/это тоже тест!"));
    }

    public function testCreateBucketWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "VERY.BAD.NAME" contains invalid characters');
        $this->amazon->createBucket("VERY.BAD.NAME");
    }

    public function testBucketAvailabilityWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "VERY.BAD.NAME" contains invalid characters');
        $this->amazon->isBucketAvailable("VERY.BAD.NAME");
    }

    public function testPutObjectWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "VERY.BAD.NAME" contains invalid characters');
        $this->amazon->putObject("VERY.BAD.NAME/And It Gets Worse", "testdata");
    }

    public function testGetObjectWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "VERY.BAD.NAME" contains invalid characters');
        $this->amazon->getObject("VERY.BAD.NAME/And It Gets Worse");
    }

    public function testGetInfoWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "VERY.BAD.NAME" contains invalid characters');
        $this->amazon->getInfo("VERY.BAD.NAME/And It Gets Worse");
    }

    public function testSetEndpointWithBadName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid endpoint supplied');
        $this->amazon->setEndpoint("//");
    }

    public function testBucketNameIsTooShort()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Bucket name "%s" must be between 3 and 255 characters long', 'xx'));
        $this->amazon->createBucket('xx');
    }

    public function testBucketNameIsTooLong()
    {
        $bucketName = str_repeat('x', 256);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Bucket name "%s" must be between 3 and 255 characters long', $bucketName)
        );
        $this->amazon->createBucket($bucketName);
    }

    public function testAcl()
    {
        $this->amazon->createBucket($this->bucket);
        $filedir = __DIR__."/_files/";

        $this->amazon->putFile($filedir."testdata.html", $this->bucket."/zftestfile.html");
        $this->amazon->putFile(
            $filedir."testdata.html",
            $this->bucket."/zftestfile2.html",
            [S3\S3::S3_ACL_HEADER => S3\S3::S3_ACL_PUBLIC_READ]
        );

        $url = 'http://' . S3\S3::S3_ENDPOINT."/".$this->bucket."/zftestfile.html";
        $data = @file_get_contents($url);
        $this->assertFalse($data);

        $url = 'http://' . S3\S3::S3_ENDPOINT."/".$this->bucket."/zftestfile2.html";
        $data = @file_get_contents($url);
        $this->assertEquals(file_get_contents($filedir."testdata.html"), $data);
    }

    /**
     * Test bucket name with /'s and encoding
     *
     * ZF-6855
     */
    public function testObjectPath()
    {
        $this->amazon->createBucket($this->bucket);
        $filedir = __DIR__."/_files/";
        $this->amazon->putFile(
            $filedir."testdata.html",
            $this->bucket."/subdir/dir with spaces/zftestfile.html",
            [S3\S3::S3_ACL_HEADER => S3\S3::S3_ACL_PUBLIC_READ]
        );
        $url = 'http://' . S3\S3::S3_ENDPOINT."/".$this->bucket."/subdir/dir%20with%20spaces/zftestfile.html";
        $data = @file_get_contents($url);
        $this->assertEquals(file_get_contents($filedir."testdata.html"), $data);
    }

    /**
     * Test creating object with https
     *
     * ZF-7029
     */
    public function testCreateObjectSSL()
    {
        $endpoint = $this->amazon->getEndpoint();
        $this->amazon->setEndpoint('https://s3.amazonaws.com');
        $this->assertEquals('https://s3.amazonaws.com', $this->amazon->getEndpoint()->toString());
        $this->amazon->createBucket($this->bucket);
        $this->amazon->putObject($this->bucket."/zftest", "testdata");
        $this->assertEquals("testdata", $this->amazon->getObject($this->bucket."/zftest"));
        $this->amazon->setEndpoint($endpoint);
    }

    /**
     * Test creating bucket with IP
     *
     * ZF-6686
     */
    public function testBucketIPMaskException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket name "127.0.0.1" cannot be an IP address');
        $this->amazon->createBucket("127.0.0.1");
    }

    /**
     * Test creating bucket with IP
     *
     * ZF-6686
     */
    public function testBucketIPMaskPostCondition()
    {
        try {
            $this->amazon->createBucket("127.0.0.1");
        } catch (S3\Exception\InvalidArgumentException $e) {
            $this->amazon->createBucket("123-456-789-123");
            $this->assertTrue($this->amazon->isBucketAvailable("123-456-789-123"));
            $this->amazon->removeBucket("123-456-789-123");
            return;
        }
        $this->fail("Failed to throw expected exception");
    }

    /**
     * @group ZF-7773
     */
    public function testGetObjectsByBucketParams()
    {
        $this->amazon->createBucket("testgetobjectparams1");
        $this->amazon->putObject("testgetobjectparams1/zftest1", "testdata");
        $this->amazon->putObject("testgetobjectparams1/zftest2", "testdata");

        $list = $this->amazon->getObjectsByBucket("testgetobjectparams1", ['max-keys' => 1]);
        $this->assertEquals(1, count($list));

        $this->amazon->removeObject("testgetobjectparams1/zftest1", "testdata");
        $this->amazon->removeObject("testgetobjectparams1/zftest2", "testdata");
        $this->amazon->removeBucket("testgetobjectparams1");
    }

    public function testCommonPrefixes()
    {
        $this->amazon->createBucket($this->bucket);
        $this->amazon->putObject($this->bucket . '/test-folder/test1', 'test');
        $this->amazon->putObject($this->bucket . '/test-folder/test2-folder/', 'test');
        $this->amazon->putObject($this->bucket . '/test-folder/test3-folder/', '');

        $params = [
            'prefix' => 'test-folder/',
            'delimiter' => '/'
        ];
        $response = $this->amazon->getObjectsAndPrefixesByBucket($this->bucket, $params);

        $this->assertEquals($response['objects'][0], 'test-folder/test1');
        $this->assertEquals($response['prefixes'][0], 'test-folder/test2-folder/');
    }

    public function tearDown()
    {
        if (! constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ENABLED')) {
            return;
        }
        unset($this->amazon->debug);
        $this->amazon->cleanBucket($this->bucket);
        $this->amazon->removeBucket($this->bucket);
    }
}
