<?php
/**
 * @see       https://github.com/zendframework/ZendService_Amazon for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/ZendService_Amazon/blob/master/LICENSE.md New BSD License
 */

namespace ZendService\Amazon\Ec2;

use DOMDocument;
use DOMXPath;
use Zend\Http\Response as HttpResponse;

/**
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 */
class Response
{
    // TODO: Unsuppress standards checking when underscores removed from property names
    // @codingStandardsIgnoreStart

    /**
     * XML namespace used for EC2 responses.
     */
    protected $_xmlNamespace = 'http://ec2.amazonaws.com/doc/2009-04-04/';

    /**
     * The original HTTP response
     *
     * This contains the response body and headers.
     *
     * @var HttpResponse
     */
    private $_httpResponse = null;

    /**
     * The response document object
     *
     * @var DOMDocument
     */
    private $_document = null;

    /**
     * The response XPath
     *
     * @var DOMXPath
     */
    private $_xpath = null;

    // @codingStandardsIgnoreEnd

    /**
     * Creates a new high-level EC2 response object
     *
     * @param HttpResponse $httpResponse the HTTP response.
     */
    public function __construct(HttpResponse $httpResponse)
    {
        $this->_httpResponse = $httpResponse;
    }

    /**
     * Gets the XPath object for this response
     *
     * @return DOMXPath the XPath object for response.
     */
    public function getXPath()
    {
        if ($this->_xpath === null) {
            $document = $this->getDocument();
            if ($document === false) {
                $this->_xpath = false;
            } else {
                $this->_xpath = new DOMXPath($document);
                $this->_xpath->registerNamespace(
                    'ec2',
                    $this->getNamespace()
                );
            }
        }

        return $this->_xpath;
    }

    /**
     * Gets the document object for this response
     *
     * @return DOMDocument the DOM Document for this response.
     */
    public function getDocument()
    {
        try {
            $body = $this->_httpResponse->getBody();
        } catch (\Zend\Http\Exception\ExceptionInterface $e) {
            $body = false;
        }

        if ($this->_document === null) {
            if ($body !== false) {
                // turn off libxml error handling
                $errors = libxml_use_internal_errors();

                $this->_document = new DOMDocument();
                if (! $this->_document->loadXML($body)) {
                    $this->_document = false;
                }

                // reset libxml error handling
                libxml_clear_errors();
                libxml_use_internal_errors($errors);
            } else {
                $this->_document = false;
            }
        }

        return $this->_document;
    }

    /**
     * Return the current set XML Namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_xmlNamespace;
    }

    /**
     * Set a new XML Namespace
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_xmlNamespace = $namespace;
    }
}
