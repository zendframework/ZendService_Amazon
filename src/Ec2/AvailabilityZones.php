<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Ec2;

use ZendService\Amazon;

/**
 * An Amazon EC2 interface to query which Availability Zones your account has access to.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 */
class AvailabilityZones extends AbstractEc2
{
    /**
     * Describes availability zones that are currently available to the account
     * and their states.
     *
     * @param string|array $zoneName Name of an availability zone.
     * @return array An array that contains all the return items.  Keys: zoneName and zoneState.
     */
    public function describe($zoneName = null)
    {
        $params = [];
        $params['Action'] = 'DescribeAvailabilityZones';

        if (is_array($zoneName) && ! empty($zoneName)) {
            foreach ($zoneName as $k => $name) {
                $params['ZoneName.' . ($k + 1)] = $name;
            }
        } elseif ($zoneName) {
            $params['ZoneName.1'] = $zoneName;
        }

        $response = $this->sendRequest($params);

        $xpath  = $response->getXPath();
        $nodes  = $xpath->query('//ec2:item');

        $return = [];
        foreach ($nodes as $k => $node) {
            $item = [];
            $item['zoneName']   = $xpath->evaluate('string(ec2:zoneName/text())', $node);
            $item['zoneState']  = $xpath->evaluate('string(ec2:zoneState/text())', $node);

            $return[] = $item;
            unset($item);
        }

        return $return;
    }
}
