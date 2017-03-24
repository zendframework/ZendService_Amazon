<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendServiceTest\Amazon;

use PHPUnit\Framework\TestCase;
use ZendService\Amazon;
use ZendService\Amazon\Exception\ExceptionInterface;
use ZendService\Amazon\Exception\InvalidArgumentException;
use Zend\Http\Client\Adapter\Test as HttpClientAdapter;

/**
 * Test helper
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 */
class OfflineTest extends TestCase
{
    /**
     * Reference to Amazon service consumer object
     *
     * @var Amazon\Amazon
     */
    protected $amazon;

    /**
     * HTTP client adapter for testing
     *
     * @var HttpClientAdapter
     */
    protected $httpClientTestAdapter;

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp()
    {
        $this->amazon = new Amazon\Amazon(constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'));

        $this->httpClientTestAdapter = new HttpClientAdapter();
    }

    /**
     * Ensures that __construct() throws an exception when given an invalid country code
     *
     * @return void
     */
    public function testConstructExceptionCountryCodeInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown country code: oops');
        $amazon = new Amazon\Amazon(constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'), 'oops');
    }

    /**
     * @group ZF-2056
     */
    public function testMozardSearchFromFile()
    {
        $xml = file_get_contents(__DIR__."/_files/mozart_result.xml");
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $mozartTracks = [
            'B00005A8JZ' => '29',
            'B0000058HV' => '25',
            'B000BLI3K2' => '500',
            'B00004X0QF' => '9',
            'B000004194' => '19',
            'B00000I9M0' => '9',
            'B000004166' => '20',
            'B00002DEH1' => '58',
            'B0000041EV' => '12',
            'B00004SA87' => '42',
        ];

        $result = new Amazon\ResultSet($dom);

        foreach ($result as $item) {
            $trackCount = $mozartTracks[$item->ASIN];
            $this->assertEquals($trackCount, count($item->Tracks));
        }
    }

    /**
     * @group ZF-2749
     */
    public function testSimilarProductConstructorMissingAttributeDoesNotThrowNotice()
    {
        $dom = new \DOMDocument();
        $asin = $dom->createElement("ASIN", "TEST");
        $product = $dom->createElement("product");
        $product->appendChild($asin);

        $similarproduct = new Amazon\SimilarProduct($product);
        $this->assertTrue(is_object($similarproduct));
    }

    /**
     * @group ZF-7251
     */
    public function testFullOffersFromFile()
    {
        $xml = file_get_contents(__DIR__."/_files/offers_with_names.xml");
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $dataExpected = [
            '0439774098' => [
                'offers' => [
                    'A79CLRHOQ3NF4' => [
                        'name'  => 'PLEXSUPPLY',
                        'price' => '5153'
                    ],
                    'A2K9NS8DSVOE2W' => [
                        'name'  => 'nangsuer',
                        'price' => '5153'
                    ],
                    'A31EVTLIC13ORD' => [
                        'name'  => 'Wizard of Math',
                        'price' => '7599'
                    ],
                    'A3SKJE188CW5XG' => [
                        'name'  => 'ReStockIt',
                        'price' => '5299'
                    ],
                    'A1729W3053T57N' => [
                        'name'  => 'The Price Pros',
                        'price' => '5487'
                    ],
                    'A29PHU0KPCGV8S' => [
                        'name'  => 'TheFactoryDepot',
                        'price' => '5821'
                    ],
                    'AIHRRFGW11GJ8' => [
                        'name'  => 'Design Tec Office Products',
                        'price' => '5987'
                    ],
                    'A27OK403WRHSGI' => [
                        'name'  => 'Kaplan Early Learning Company',
                        'price' => '7595'
                    ],
                    'A25DVOZOPBFMAN' => [
                        'name'  => 'Deerso',
                        'price' => '7599'
                    ],
                    'A6IFKC796Y64H' => [
                        'name'  => 'The Education Station Inc',
                        'price' => '7599'
                    ],
                ],
            ],
            'B00000194U' => [
                'offers' => [
                    'A3UOG6723G7MG0' => [
                        'name'  => 'Efunctional',
                        'price' => '480'
                    ],
                    'A3SNNXCKUIW1O2' => [
                        'name'  => 'Universal Mania',
                        'price' => '531'
                    ],
                    'A18ACDNYOEMMOL' => [
                        'name'  => 'ApexSuppliers',
                        'price' => '589'
                    ],
                    'A2NYACAJP9I1IY' => [
                        'name'  => 'GizmosForLife',
                        'price' => '608'
                    ],
                    'A1729W3053T57N' => [
                        'name'  => 'The Price Pros',
                        'price' => '628'
                    ],
                    'A29PHU0KPCGV8S' => [
                        'name'  => 'TheFactoryDepot',
                        'price' => '638'
                    ],
                    'A3Q3IAIX1CLBMZ' => [
                        'name'  => 'ElectroGalaxy',
                        'price' => '697'
                    ],
                    'A1PC5XI7QQLW5G' => [
                        'name'  => 'Long Trading Company',
                        'price' => '860'
                    ],
                    'A2R0FX412W1BDT' => [
                        'name'  => 'Beach Audio',
                        'price' => '896'
                    ],
                    'AKJJGJ0JKT8F1' => [
                        'name'  => 'Buy.com',
                        'price' => '899'
                    ],
                ],
            ],
        ];

        $result = new Amazon\ResultSet($dom);

        foreach ($result as $item) {
            $data = $dataExpected[$item->ASIN];
            foreach ($item->Offers->Offers as $offer) {
                $this->assertEquals($data['offers'][$offer->MerchantId]['name'], $offer->MerchantName);
                $this->assertEquals($data['offers'][$offer->MerchantId]['price'], $offer->Price);
            }
        }
    }

    public function dataSignatureEncryption()
    {
        return [
            [
                'http://webservices.amazon.com',
                [
                    'Service' => 'AWSECommerceService',
                    'AWSAccessKeyId' => '00000000000000000000',
                    'Operation' => 'ItemLookup',
                    'ItemId' => '0679722769',
                    'ResponseGroup' => 'ItemAttributes,Offers,Images,Reviews',
                    'Version' => '2009-01-06',
                    'Timestamp' => '2009-01-01T12:00:00Z',
                ],
                "GET\n".
                "webservices.amazon.com\n".
                "/onca/xml\n".
                "AWSAccessKeyId=00000000000000000000&ItemId=0679722769&Operation=I".
                "temLookup&ResponseGroup=ItemAttributes%2COffers%2CImages%2CReview".
                "s&Service=AWSECommerceService&Timestamp=2009-01-01T12%3A00%3A00Z&".
                "Version=2009-01-06",
                'Nace%2BU3Az4OhN7tISqgs1vdLBHBEijWcBeCqL5xN9xg%3D'
            ],
            [
                'http://ecs.amazonaws.co.uk',
                [
                    'Service' => 'AWSECommerceService',
                    'AWSAccessKeyId' => '00000000000000000000',
                    'Operation' => 'ItemSearch',
                    'Actor' => 'Johnny Depp',
                    'ResponseGroup' => 'ItemAttributes,Offers,Images,Reviews,Variations',
                    'Version' => '2009-01-01',
                    'SearchIndex' => 'DVD',
                    'Sort' => 'salesrank',
                    'AssociateTag' => 'mytag-20',
                    'Timestamp' => '2009-01-01T12:00:00Z',
                ],
                "GET\n".
                "ecs.amazonaws.co.uk\n".
                "/onca/xml\n".
                "AWSAccessKeyId=00000000000000000000&Actor=Johnny%20Depp&Associate".
                "Tag=mytag-20&Operation=ItemSearch&ResponseGroup=ItemAttributes%2C".
                "Offers%2CImages%2CReviews%2CVariations&SearchIndex=DVD&Service=AW".
                "SECommerceService&Sort=salesrank&Timestamp=2009-01-01T12%3A00%3A0".
                "0Z&Version=2009-01-01",
                'TuM6E5L9u%2FuNqOX09ET03BXVmHLVFfJIna5cxXuHxiU%3D',
            ],
        ];
    }

    /**
     * Checking if signature Encryption due on August 15th for Amazon Webservice API is working correctly.
     *
     * @dataProvider dataSignatureEncryption
     * @group ZF-7033
     */
    public function testSignatureEncryption($baseUri, $params, $expectedStringToSign, $expectedSignature)
    {
        $this->assertEquals(
            $expectedStringToSign,
            Amazon\Amazon::buildRawSignature($baseUri, $params)
        );

        $this->assertEquals(
            $expectedSignature,
            rawurlencode(Amazon\Amazon::computeSignature(
                $baseUri,
                '1234567890',
                $params
            ))
        );
    }

    /**
     * Testing if Amazon service component can handle return values where the
     * item-list is not empty
     *
     * @group ZF-9547
     */
    public function testAmazonComponentHandlesValidBookResults()
    {
        $xml = file_get_contents(__DIR__."/_files/amazon-response-valid.xml");
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $result = new Amazon\ResultSet($dom);

        $currentItem = null;

        $currentItem = $result->current();

        $this->assertInstanceOf('ZendService\Amazon\Item', $currentItem);
        $this->assertEquals('0754512673', $currentItem->ASIN);
    }

    /**
     * Testing if Amazon service component can handle return values where the
     * item-list is empty (no results found)
     *
     * @group ZF-9547
     */
    public function testAmazonComponentHandlesEmptyBookResults()
    {
        $xml = file_get_contents(__DIR__."/_files/amazon-response-invalid.xml");
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $result = new Amazon\ResultSet($dom);

        $this->expectException(ExceptionInterface::class);
        $result->current();
    }

    /**
     * NOTICE error does not occur even if RequestThrottled error happen in totalResults method.
     */
    public function testNoticeErrorDoesNotHappenInTotalResults()
    {
        $xml = file_get_contents(__DIR__ . '/_files/amazon-response-request-throttled-error.xml');
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $result = new Amazon\ResultSet($dom);
        $this->assertEquals(0, $result->totalResults());
    }

    /**
     * NOTICE error does not occur even if RequestThrottled error happen in totalPages method.
     */
    public function testNoticeErrorDoesNotHappenInTotalPages()
    {
        $xml = file_get_contents(__DIR__ . '/_files/amazon-response-request-throttled-error.xml');
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $result = new Amazon\ResultSet($dom);

        $this->assertEquals(0, $result->totalPages());
    }

    /**
     * Get a mock REST client for testing an expected URL.
     *
     * @param string $expectedUrl URL to check for.
     *
     * @return
     */
    protected function getMockRestClient($expectedUrl)
    {
        // We have to do a lot of mocking to avoid causing errors, but the only
        // part of this that is significant to the tests below is the expectation
        // set up on the $restClient's setUri method.
        $httpClient = $this->getMockBuilder('Zend\Http\Client')->getMock();
        $response = $this->getMockBuilder('Zend\Http\Response')->setMethods(['getBody'])->getMock();
        $response->expects($this->any())->method('getBody')->will($this->returnValue('<foo />'));
        $restClient = $this->getMockBuilder('ZendRest\Client\RestClient')
            ->disableOriginalConstructor()->setMethods(['setUri', 'getHttpClient', 'restGet'])->getMock();
        $restClient->expects($this->once())->method('setUri')
            ->with($this->equalTo($expectedUrl));
        $restClient->expects($this->any())->method('getHttpClient')->will($this->returnValue($httpClient));
        $restClient->expects($this->any())->method('restGet')->will($this->returnValue($response));
        return $restClient;
    }

    /**
     * Test that default URL is selected appropriately.
     */
    public function testDefaultUrl()
    {
        $amazon = new Amazon\Amazon(constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'));
        $amazon->setRestClient($this->getMockRestClient('http://webservices.amazon.com'))->itemLookup('foo');
    }

    /**
     * Test that secure URLs are selected appropriately.
     */
    public function testSecureUrls()
    {
        $urls = [
            'BR' => 'https://webservices.amazon.com.br',
            'CA' => 'https://webservices.amazon.ca',
            'CN' => 'https://webservices.amazon.cn',
            'DE' => 'https://webservices.amazon.de',
            'ES' => 'https://webservices.amazon.es',
            'FR' => 'https://webservices.amazon.fr',
            'IN' => 'https://webservices.amazon.in',
            'IT' => 'https://webservices.amazon.it',
            'JP' => 'https://webservices.amazon.co.jp',
            'MX' => 'https://webservices.amazon.com.mx',
            'UK' => 'https://webservices.amazon.co.uk',
            'US' => 'https://webservices.amazon.com',
        ];
        foreach ($urls as $country => $expected) {
            $amazon = new Amazon\Amazon(
                constant('TESTS_ZEND_SERVICE_AMAZON_ONLINE_ACCESSKEYID'),
                $country,
                null,
                null,
                true
            );
            $amazon->setRestClient($this->getMockRestClient($expected))->itemLookup('foo');
        }
    }
}
