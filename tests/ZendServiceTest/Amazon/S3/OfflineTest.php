<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendServiceTest\Amazon\S3;

use PHPUnit\Framework\TestCase;
use ZendService\Amazon\S3\S3;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon_S3
 * @subpackage UnitTests
 * @group      Zend_Service
 * @group      Zend_Service_Amazon
 * @group      Zend_Service_Amazon_S3
 */
class OfflineTest extends TestCase
{
    public function testThrottle()
    {
        $s3 = new S3();
        $throttleTime = 0.001;  // seconds
        $limit = 5;

        $throttler = function () use ($s3, $throttleTime) {
            return $s3->throttle('microtime', [true], $throttleTime);
        };

        $times = array_map($throttler, range(0, $limit));

        $diffs = array_map(
            function ($a, $b) {
                return $a - $b;
            },
            array_slice($times, 1, count($times)),
            array_slice($times, 0, count($times) - 1)
        );

        array_map(
            [$this, 'assertGreaterThanOrEqual'],
            array_fill(0, $limit, $throttleTime),
            $diffs
        );
    }
}
