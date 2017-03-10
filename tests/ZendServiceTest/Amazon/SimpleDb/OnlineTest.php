<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Amazon\SimpleDb;

use PHPUnit\Framework\TestCase;
use ZendService\Amazon\SimpleDb;
use ZendService\Amazon\SimpleDb\Exception;
use Zend\Http\Client\Adapter\Socket;

/**
 * @category   Zend
 * @package    ZendService\Amazon\SimpleDb
 * @subpackage UnitTests
 */
class OnlineTest extends TestCase
{
    /**
     * Reference to Amazon service consumer object
     *
     * @var ZendService\Amazon\SimpleDb
     */
    protected $amazon;

    /**
     * Socket based HTTP client adapter
     *
     * @var Zend\Http\Client\Adapter\Socket
     */
    protected $httpClientAdapterSocket;

    protected $testDomainNamePrefix;

    protected $testItemNamePrefix;

    protected $testAttributeNamePrefix;

    // Because Amazon uses an eventual consistency model, this test period may
    // help avoid *but not guarantee* false negatives
    protected $testWaitPeriod = 2;

    /**
     * Maximum attempts performed in request()
     *
     * @var int
     */
    protected $testWaitRetries = 3;

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

        $this->amazon = new SimpleDb\SimpleDb(
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'),
            constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_SECRETKEY')
        );

        $this->httpClientAdapterSocket = new Socket();

        $this->amazon->getHttpClient()
                      ->setAdapter($this->httpClientAdapterSocket);

        $this->testDomainNamePrefix = 'TestsZendServiceAmazonSimpleDbDomain';

        $this->testItemNamePrefix = 'TestsZendServiceAmazonSimpleDbItem';

        $this->testAttributeNamePrefix = 'TestsZendServiceAmazonSimpleDbAttribute';

        $this->wait();
    }

    /**
     * Wrapper around remote calls to retry, apply wait, etc.
     *
     * @param string $method SimpleDB method name
     * @param array $args Method argument list
     * @return void
     */
    public function request($method, $args = [])
    {
        $response = null;
        for ($try = 1; $try <= $this->testWaitRetries; $try++) {
            try {
                $this->wait();
                $response = call_user_func_array([$this->amazon, $method], $args);
                break;
            } catch (Zend_Service_Amazon_SimpleDb_Exception $e) {
                // Only retry after throtte-related error
                if (false === strpos($e->getMessage(), 'currently unavailable')) {
                    throw $e;
                }
            }
        }
        return $response;
    }

    public function testGetAttributes()
    {
        $domainName = $this->testDomainNamePrefix . '_testGetAttributes';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $itemName = $this->testItemNamePrefix . '_testGetAttributes';
            $attributeName1 = $this->testAttributeNamePrefix . '_testGetAttributes1';
            $attributeName2 = $this->testAttributeNamePrefix . '_testGetAttributes2';
            $attributeValue1 = 'value1';
            $attributeValue2 = 'value2';
            $attributes = [
                $attributeName1 => new SimpleDb\Attribute($itemName, $attributeName1, $attributeValue1),
                $attributeName2 => new SimpleDb\Attribute($itemName, $attributeName2, $attributeValue2)
            ];

            // Now that everything's set up, test it
            $this->request('putAttributes', [$domainName, $itemName, $attributes]);

            // One attribute
            $results = $this->request('getAttributes', [$domainName, $itemName, $attributeName1]);
            $this->assertEquals(1, count($results));
            $attribute = current($results);
            $this->assertEquals($attributeName1, $attribute->getName());
            $this->assertEquals($attributeValue1, current($attribute->getValues()));

            // Multiple attributes
            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(2, count($results));
            $this->assertTrue(array_key_exists($attributeName1, $results));
            $this->assertTrue(array_key_exists($attributeName2, $results));
            $this->assertEquals($attributeValue1, current($results[$attributeName1]->getValues()));
            $this->assertEquals($attributeValue2, current($results[$attributeName2]->getValues()));

            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    public function testPutAttributes()
    {
        $domainName = $this->testDomainNamePrefix . '_testPutAttributes';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $itemName = $this->testItemNamePrefix . '_testPutAttributes';
            $attributeName1 = $this->testAttributeNamePrefix . '_testPutAttributes1';
            $attributeName2 = $this->testAttributeNamePrefix . '_testPutAttributes2';
            $attributeValue1 = 'value1';
            $attributeValue2 = 'value2';
            $attributes = [
                $attributeName1 => new SimpleDb\Attribute($itemName, $attributeName1, $attributeValue1),
                $attributeName2 => new SimpleDb\Attribute($itemName, $attributeName2, $attributeValue2)
            ];

            // Now that everything's set up, test it
            $this->request('putAttributes', [$domainName, $itemName, $attributes]);

            // Multiple attributes
            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(2, count($results));
            $this->assertTrue(array_key_exists($attributeName1, $results));
            $this->assertTrue(array_key_exists($attributeName2, $results));
            $this->assertEquals($attributes[$attributeName1], $results[$attributeName1]);
            $this->assertEquals($attributes[$attributeName2], $results[$attributeName2]);
            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    public function testBatchPutAttributes()
    {
        $domainName = $this->testDomainNamePrefix . '_testBatchPutAttributes';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $itemName1 = $this->testItemNamePrefix . '_testBatchPutAttributes1';
            $itemName2 = $this->testItemNamePrefix . '_testBatchPutAttributes2';
            $attributeName1 = $this->testAttributeNamePrefix . '_testBatchPutAttributes1';
            $attributeName2 = $this->testAttributeNamePrefix . '_testBatchPutAttributes2';
            $attributeName3 = $this->testAttributeNamePrefix . '_testBatchPutAttributes3';
            $attributeName4 = $this->testAttributeNamePrefix . '_testBatchPutAttributes4';
            $attributeValue1 = 'value1';
            $attributeValue2 = 'value2';
            $attributeValue3 = 'value3';
            $attributeValue4 = 'value4';
            $attributeValue5 = 'value5';
            $items = [
                $itemName1 => [
                    $attributeName1 => new SimpleDb\Attribute($itemName1, $attributeName1, $attributeValue1),
                    $attributeName2 => new SimpleDb\Attribute($itemName1, $attributeName2, $attributeValue2)],
                $itemName2 => [
                    $attributeName3 => new SimpleDb\Attribute($itemName2, $attributeName3, $attributeValue3),
                    $attributeName4 => new SimpleDb\Attribute(
                        $itemName2,
                        $attributeName4,
                        [$attributeValue4, $attributeValue5]
                    )]
                ];


            $replace = [
                $itemName1 => [
                    $attributeName1 => false,
                    $attributeName2 => false
                ],
                $itemName2 => [
                    $attributeName3 => false,
                    $attributeName4 => false
                ]
            ];

            $this->assertEquals([], $this->request('getAttributes', [$domainName, $itemName1]));
            $this->request('batchPutAttributes', [$items, $domainName, $replace]);

            $result = $this->request('getAttributes', [$domainName, $itemName1, $attributeName1]);

            $this->assertTrue(array_key_exists($attributeName1, $result));
            $this->assertEquals($attributeName1, $result[$attributeName1]->getName());
            $this->assertEquals($attributeValue1, current($result[$attributeName1]->getValues()));
            $result = $this->request('getAttributes', [$domainName, $itemName2, $attributeName4]);
            $this->assertTrue(array_key_exists($attributeName4, $result));
            $this->assertEquals(2, count($result[$attributeName4]->getValues()));
            $result = $this->request('getAttributes', [$domainName, $itemName2]);
            $this->assertEquals(2, count($result));
            $this->assertTrue(array_key_exists($attributeName3, $result));
            $this->assertEquals($attributeName3, $result[$attributeName3]->getName());
            $this->assertEquals(1, count($result[$attributeName3]));
            $this->assertEquals($attributeValue3, current($result[$attributeName3]->getValues()));
            $this->assertTrue(array_key_exists($attributeName4, $result));
            $this->assertEquals($attributeName4, $result[$attributeName4]->getName());
            $this->assertEquals(2, count($result[$attributeName4]->getValues()));
            $this->assertEquals([$attributeValue4, $attributeValue5], $result[$attributeName4]->getValues());

            // Test replace
            $newAttributeValue1 = 'newValue1';
            $newAttributeValue4 = 'newValue4';
            $items[$itemName1][$attributeName1]->setValues([$newAttributeValue1]);
            $items[$itemName2][$attributeName4]->setValues([$newAttributeValue4]);

            $this->request('batchPutAttributes', [$items, $domainName, $replace]);

            $result = $this->request('getAttributes', [$domainName, $itemName1, $attributeName1]);
            $this->assertEquals([$newAttributeValue1, $attributeValue1], $result[$attributeName1]->getValues());

            $result = $this->request('getAttributes', [$domainName, $itemName2, $attributeName4]);
            $this->assertEquals(
                [$newAttributeValue4, $attributeValue4, $attributeValue5],
                $result[$attributeName4]->getValues()
            );

            $replace[$itemName1][$attributeName1] = true;
            $replace[$itemName2][$attributeName4] = true;

            $this->request('batchPutAttributes', [$items, $domainName, $replace]);

            $result = $this->request('getAttributes', [$domainName, $itemName1, $attributeName1]);
            $this->assertEquals($items[$itemName1][$attributeName1], $result[$attributeName1]);

            $result = $this->request('getAttributes', [$domainName, $itemName2, $attributeName4]);
            $this->assertEquals($items[$itemName2][$attributeName4], $result[$attributeName4]);
            $this->assertEquals($items[$itemName1], $this->request('getAttributes', [$domainName, $itemName1]));

            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    public function testDeleteAttributes()
    {
        $domainName = $this->testDomainNamePrefix . '_testDeleteAttributes';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $itemName = $this->testItemNamePrefix . '_testDeleteAttributes';
            $attributeName1 = $this->testAttributeNamePrefix . '_testDeleteAttributes1';
            $attributeName2 = $this->testAttributeNamePrefix . '_testDeleteAttributes2';
            $attributeName3 = $this->testAttributeNamePrefix . '_testDeleteAttributes3';
            $attributeName4 = $this->testAttributeNamePrefix . '_testDeleteAttributes4';
            $attributeValue1 = 'value1';
            $attributeValue2 = 'value2';
            $attributeValue3 = 'value3';
            $attributeValue4 = 'value4';
            $attributes = [
                new SimpleDb\Attribute($itemName, $attributeName1, $attributeValue1),
                new SimpleDb\Attribute($itemName, $attributeName2, $attributeValue2),
                new SimpleDb\Attribute($itemName, $attributeName3, $attributeValue3),
                new SimpleDb\Attribute($itemName, $attributeName4, $attributeValue4)
            ];

            // Now that everything's set up, test it
            $this->request('putAttributes', [$domainName, $itemName, $attributes]);

            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(4, count($results));
            $this->assertTrue(array_key_exists($attributeName1, $results));
            $this->assertTrue(array_key_exists($attributeName2, $results));
            $this->assertTrue(array_key_exists($attributeName3, $results));
            $this->assertTrue(array_key_exists($attributeName4, $results));
            $this->assertEquals($attributeValue1, current($results[$attributeName1]->getValues()));
            $this->assertEquals($attributeValue2, current($results[$attributeName2]->getValues()));
            $this->assertEquals($attributeValue3, current($results[$attributeName3]->getValues()));
            $this->assertEquals($attributeValue4, current($results[$attributeName4]->getValues()));

            $this->request('deleteAttributes', [$domainName, $itemName, [$attributes[0]]]);

            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(3, count($results));
            $this->assertTrue(array_key_exists($attributeName2, $results));
            $this->assertTrue(array_key_exists($attributeName3, $results));
            $this->assertTrue(array_key_exists($attributeName4, $results));
            $this->assertEquals($attributeValue2, current($results[$attributeName2]->getValues()));
            $this->assertEquals($attributeValue3, current($results[$attributeName3]->getValues()));
            $this->assertEquals($attributeValue4, current($results[$attributeName4]->getValues()));

            $this->request('deleteAttributes', [$domainName, $itemName, [$attributes[1], $attributes[2]]]);

            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(1, count($results));
            $this->assertTrue(array_key_exists($attributeName4, $results));
            $this->assertEquals($attributeValue4, current($results[$attributeName4]->getValues()));


            $this->request('deleteAttributes', [$domainName, $itemName, [$attributes[3]]]);

            $results = $this->request('getAttributes', [$domainName, $itemName]);
            $this->assertEquals(0, count($results));

            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    /**
     *
     * @param $maxNumberOfDomains Integer between 1 and 100
     * @param $nextToken          Integer between 1 and 100
     * @return array              0 or more domain names
     */
    public function testListDomains()
    {
        $domainName = null;
        try {
            // Create some domains
            for ($i = 1; $i <= 3; $i++) {
                $domainName = $this->testDomainNamePrefix . '_testListDomains' . $i;
                $this->request('deleteDomain', [$domainName]);
                $this->request('createDomain', [$domainName]);
            }

            $page = $this->request('listDomains', [3]);
            $this->assertEquals(3, count($page->getData()));
            // Amazon returns an empty page as the last page :/
            $isLast = $page->isLast();
            if (! $isLast) {
                // The old isLast() assertTrue failed in full suite runs. Token often
                // decodes to 'TestsZendServiceAmazonSimpleDbDomain_testPutAttributes'
                // which no longer exists. Instead of a plain assertTrue, which seemed
                // to pass only in single-case runs, we'll make sure the token's
                // presence is worth a negative.
                $token = $page->getToken();
                if ($token) {
                    $tokenDomainName = base64_decode($token);
                    if (false !== strpos($tokenDomainName, $this->testDomainNamePrefix)) {
                        try {
                            $this->request('domainMetadata', [$tokenDomainName]);
                            $this->fail('listDomains call with 3 domain maximum did not return last page');
                        } catch (Exception $e) {
                            $this->assertContains('The specified domain does not exist', $e->getMessage());
                        }
                    }
                }
            }
            $this->assertEquals(1, count($this->request('listDomains', [1, $page->getToken()])));

            $page = $this->request('listDomains', [4]);
            $this->assertEquals(3, count($page->getData()));
            $this->assertTrue($page->isLast());

            $page = $this->request('listDomains', [2]);
            $this->assertEquals(2, count($page->getData()));
            $this->assertFalse($page->isLast());

            $nextPage = $this->request('listDomains', [100, $page->getToken()]);
            $this->assertEquals(1, count($nextPage->getData()));
            // Amazon returns an empty page as the last page :/
            $this->assertTrue($nextPage->isLast());

            // Delete the domains
            for ($i = 1; $i <= 3; $i++) {
                $domainName = $this->testDomainNamePrefix . '_testListDomains' . $i;
                $this->request('deleteDomain', [$domainName]);
            }
        } catch (Exception $e) {
            // Delete the domains
            for ($i = 1; $i <= 3; $i++) {
                $domainName = $this->testDomainNamePrefix . '_testListDomains' . $i;
                $this->request('deleteDomain', [$domainName]);
            }
            throw $e;
        }
    }

    /**
     * @param $domainName string Name of the domain for which metadata will be requested
     * @return array Key/value array of metadatum names and values.
     */
    public function testDomainMetadata()
    {
        $domainName = $this->testDomainNamePrefix . '_testDomainMetadata';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $metadata = $this->request('domainMetadata', [$domainName]);
            $this->assertTrue(is_array($metadata));
            $this->assertGreaterThan(0, count($metadata));
            $this->assertTrue(array_key_exists('ItemCount', $metadata));
            $this->assertEquals(0, (int)$metadata['ItemCount']);
            $this->assertTrue(array_key_exists('ItemNamesSizeBytes', $metadata));
            $this->assertEquals(0, (int)$metadata['ItemNamesSizeBytes']);
            $this->assertTrue(array_key_exists('AttributeNameCount', $metadata));
            $this->assertEquals(0, (int)$metadata['AttributeNameCount']);
            $this->assertTrue(array_key_exists('AttributeValueCount', $metadata));
            $this->assertEquals(0, (int)$metadata['AttributeValueCount']);
            $this->assertTrue(array_key_exists('AttributeNamesSizeBytes', $metadata));
            $this->assertEquals(0, (int)$metadata['AttributeNamesSizeBytes']);
            $this->assertTrue(array_key_exists('AttributeValuesSizeBytes', $metadata));
            $this->assertEquals(0, (int)$metadata['AttributeValuesSizeBytes']);
            $this->assertTrue(array_key_exists('Timestamp', $metadata));

            // Delete the domain
            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    /**
     *
     * @param $domainName   string  Valid domain name of the domain to create
     * @return              boolean True if successful, false if not
     */
    public function testCreateDomain()
    {
        $domainName = $this->testDomainNamePrefix . '_testCreateDomain';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $domainListPage = $this->request('listDomains');
            $this->assertContains($domainName, $domainListPage->getData());
            // Delete the domain
            $this->request('deleteDomain', [$domainName]);
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    public function testDeleteDomain()
    {
        $domainName = $this->testDomainNamePrefix . '_testDeleteDomain';
        $this->request('deleteDomain', [$domainName]);
        $this->request('createDomain', [$domainName]);
        try {
            $domainListPage = $this->request('listDomains');
            $this->assertContains($domainName, $domainListPage->getData());
            $this->assertNull($domainListPage->getToken());
            // Delete the domain
            $this->request('deleteDomain', [$domainName]);
            $domainListPage = $this->request('listDomains');
            $this->assertNotContains($domainName, $domainListPage->getData());
        } catch (Exception $e) {
            $this->request('deleteDomain', [$domainName]);
            throw $e;
        }
    }

    private function wait()
    {
        sleep($this->testWaitPeriod);
    }

    /**
     * Tear down the test case
     *
     * @return void
     */
    public function tearDown()
    {

        // $this->request('deleteDomain', array($this->testDomainNamePrefix));
        // Delete domains

        unset($this->amazon);
    }
}
