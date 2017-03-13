<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Ec2;

/**
 * Allows you to interface with the reserved instances on Amazon Ec2
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 */
class ReservedInstance extends AbstractEc2
{
    /**
     * Describes Reserved Instances that you purchased.
     *
     * @param string|array $instanceId IDs of the Reserved Instance to describe.
     * @return array
     */
    public function describeInstances($instanceId)
    {
        $params = [];
        $params['Action'] = 'DescribeReservedInstances';

        if (is_array($instanceId) && ! empty($instanceId)) {
            foreach ($instanceId as $k => $name) {
                $params['ReservedInstancesId.' . ($k + 1)] = $name;
            }
        } elseif ($instanceId) {
            $params['ReservedInstancesId.1'] = $instanceId;
        }

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $items = $xpath->query('//ec2:reservedInstancesSet/ec2:item');

        $return = [];
        foreach ($items as $item) {
            $i = [];
            $i['reservedInstancesId'] = $xpath->evaluate('string(ec2:reservedInstancesId/text())', $item);
            $i['instanceType'] = $xpath->evaluate('string(ec2:instanceType/text())', $item);
            $i['availabilityZone'] = $xpath->evaluate('string(ec2:availabilityZone/text())', $item);
            $i['duration'] = $xpath->evaluate('string(ec2:duration/text())', $item);
            $i['fixedPrice'] = $xpath->evaluate('string(ec2:fixedPrice/text())', $item);
            $i['usagePrice'] = $xpath->evaluate('string(ec2:usagePrice/text())', $item);
            $i['productDescription'] = $xpath->evaluate('string(ec2:productDescription/text())', $item);
            $i['instanceCount'] = $xpath->evaluate('string(ec2:instanceCount/text())', $item);
            $i['state'] = $xpath->evaluate('string(ec2:state/text())', $item);

            $return[] = $i;
            unset($i);
        }

        return $return;
    }

    /**
     * Describes Reserved Instance offerings that are available for purchase.
     * With Amazon EC2 Reserved Instances, you purchase the right to launch Amazon
     * EC2 instances for a period of time (without getting insufficient capacity
     * errors) and pay a lower usage rate for the actual time used.
     *
     * @return array
     */
    public function describeOfferings()
    {
        $params = [];
        $params['Action'] = 'DescribeReservedInstancesOfferings';

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $items = $xpath->query('//ec2:reservedInstancesOfferingsSet/ec2:item');

        $return = [];
        foreach ($items as $item) {
            $i = [];
            $i['reservedInstancesOfferingId'] = $xpath
                ->evaluate('string(ec2:reservedInstancesOfferingId/text())', $item);
            $i['instanceType'] = $xpath->evaluate('string(ec2:instanceType/text())', $item);
            $i['availabilityZone'] = $xpath->evaluate('string(ec2:availabilityZone/text())', $item);
            $i['duration'] = $xpath->evaluate('string(ec2:duration/text())', $item);
            $i['fixedPrice'] = $xpath->evaluate('string(ec2:fixedPrice/text())', $item);
            $i['usagePrice'] = $xpath->evaluate('string(ec2:usagePrice/text())', $item);
            $i['productDescription'] = $xpath->evaluate('string(ec2:productDescription/text())', $item);

            $return[] = $i;
            unset($i);
        }

        return $return;
    }

    /**
     * Purchases a Reserved Instance for use with your account. With Amazon EC2
     * Reserved Instances, you purchase the right to launch Amazon EC2 instances
     * for a period of time (without getting insufficient capacity errors) and
     * pay a lower usage rate for the actual time used.
     *
     * @param string $offeringId     The offering ID of the Reserved Instance to purchase
     * @param integer $instanceCount The number of Reserved Instances to purchase.
     * @return string The ID of the purchased Reserved Instances.
     */
    public function purchaseOffering($offeringId, $instanceCount = 1)
    {
        $params = [];
        $params['Action'] = 'PurchaseReservedInstancesOffering';
        $params['OfferingId.1'] = $offeringId;
        $params['instanceCount.1'] = intval($instanceCount);

        $response = $this->sendRequest($params);

        $xpath = $response->getXPath();
        $reservedInstancesId = $xpath->evaluate('string(//ec2:reservedInstancesId/text())');

        return $reservedInstancesId;
    }
}
