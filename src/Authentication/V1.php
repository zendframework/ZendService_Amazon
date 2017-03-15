<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Authentication;

use Zend\Crypt\Hmac;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Authentication
 */
class V1 extends AbstractAuthentication
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * Signature Version
     */
    protected $_signatureVersion = '1';

    /**
     * Signature Encoding Method
     */
    protected $_signatureMethod = 'HmacSHA256';

    // @codingStandardsIgnoreEnd

    /**
     * Generate the required attributes for the signature
     * @param string $url
     * @param array $parameters
     * @return string
     */
    public function generateSignature($url, array &$parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_accessKey;
        $parameters['SignatureVersion'] = $this->_signatureVersion;
        $parameters['Version']          = $this->_apiVersion;
        if (! isset($parameters['Timestamp'])) {
            $parameters['Timestamp']    = gmdate('Y-m-d\TH:i:s\Z', time() + 10);
        }

        $data = $this->_signParameters($url, $parameters);

        return $data;
    }

    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * Computes the RFC 2104-compliant HMAC signature for request parameters
     *
     * This implements the Amazon Web Services signature, as per the following
     * specification:
     *
     * 1. Sort all request parameters (including <tt>SignatureVersion</tt> and
     *    excluding <tt>Signature</tt>, the value of which is being created),
     *    ignoring case.
     *
     * 2. Iterate over the sorted list and append the parameter name (in its
     *    original case) and then its value. Do not URL-encode the parameter
     *    values before constructing this string. Do not use any separator
     *    characters when appending strings.
     *
     * @param string $url Queue URL
     * @param array $parameters the parameters for which to get the signature.
     *
     * @return string the signed data.
     * @deprecated Underscore should be removed from method name
     */
    protected function _signParameters($url, array &$parameters)
    {
        $data = '';

        uksort($parameters, 'strcasecmp');
        unset($parameters['Signature']);

        foreach ($parameters as $key => $value) {
            $data .= $key . $value;
        }

        $hmac = Hmac::compute($this->_secretKey, 'SHA1', $data, Hmac::OUTPUT_BINARY);

        $parameters['Signature'] = base64_encode($hmac);

        return $data;
    }
    // @codingStandardsIgnoreEnd
}
