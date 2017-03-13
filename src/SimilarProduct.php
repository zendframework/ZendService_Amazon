<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon;

use DOMElement;
use DOMText;
use DOMXPath;

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 */
class SimilarProduct
{
    /**
     * @var string
     */
    public $ASIN;

    /**
     * @var string
     */
    public $Title;

    /**
     * Assigns values to properties relevant to SimilarProduct
     *
     * @param  DOMElement $dom
     */
    public function __construct(DOMElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/' . Amazon::getVersion());
        foreach (['ASIN', 'Title'] as $el) {
            $text = $xpath->query("./az:$el/text()", $dom)->item(0);
            if ($text instanceof DOMText) {
                $this->$el = (string)$text->data;
            }
        }
    }
}
