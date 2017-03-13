<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Ec2;

/**
 * An Amazon EC2 interface to query which Regions your account has access to.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 */
class Region extends AbstractEc2
{

    /**
     * Describes availability zones that are currently available to the account
     * and their states.
     *
     * @param string|array $region Name of an region.
     * @return array An array that contains all the return items.  Keys: regionName and regionUrl.
     */
    public function describe($region = null)
    {
        $params = [];
        $params['Action'] = 'DescribeRegions';

        if (is_array($region) && ! empty($region)) {
            foreach ($region as $k => $name) {
                $params['Region.' . ($k + 1)] = $name;
            }
        } elseif ($region) {
            $params['Region.1'] = $region;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes  = $xpath->query('//ec2:item');

        $return = [];
        foreach ($nodes as $k => $node) {
            $item = [];
            $item['regionName']   = $xpath->evaluate('string(ec2:regionName/text())', $node);
            $item['regionUrl']  = $xpath->evaluate('string(ec2:regionUrl/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }
}
