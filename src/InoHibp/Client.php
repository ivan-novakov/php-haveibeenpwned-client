<?php

namespace InoHibp;

use Zend\Http;
use Zend\Json\Json;


/**
 * Constructs and sends requests to the haveibeenpwned.com site, then evaluates and processes the responses.
 */
class Client
{

    /**
     * @var Http\Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $endpointUrl;


    /**
     * Constructor.
     * 
     * @param string $endpointUrl
     * @param Http\Client $httpClient
     */
    public function __construct($endpointUrl, Http\Client $httpClient)
    {
        $this->setEndpointUrl($endpointUrl);
        $this->setHttpClient($httpClient);
    }


    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }


    /**
     * @param string $endpointUrl
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
    }


    /**
     * @return Http\Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }


    /**
     * @param Http\Client $httpClient
     */
    public function setHttpClient(Http\Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }


    /**
     * Checks if the email "has been pwned". If the email has not been pwned, null is returned. Otherwise,
     * the names of the sites, where the email has been pwned, is returned as an array.
     * 
     * @param string $email The email address to be checked.
     * @throws Exception\TransportException When a HTTP client error/exception occurs.
     * @throws Exception\InvalidEmailException When the email address does not comply with an acceptable email format.
     * @throws Exception\UnexpectedResponseException When there is an unexpected response status code (different
     * from 200, 400 and 404).
     * @throws Exception\InvalidResponseFormatException When the response cannot be decoded as JSON.
     * @return array|null
     */
    public function checkEmail($email)
    {
        $httpRequest = $this->createRequest($email);
        
        try {
            $response = $this->getHttpClient()->send($httpRequest);
        } catch (\Exception $e) {
            throw new Exception\TransportException(sprintf("HTTP client exception: [%s] %s", get_class($e), $e->getMessage()), null, $e);
        }
        
        $statusCode = $response->getStatusCode();
        
        if (200 === $statusCode) {
            return $this->parseResponseBody($response->getBody());
        }
        
        if (404 === $statusCode) {
            return null;
        }
        
        if (400 === $statusCode) {
            throw new Exception\InvalidEmailException(sprintf("Invalid email format '%s'", $email));
        }
        
        throw new Exception\UnexpectedResponseException(sprintf("Unexpected response - status code '%d'", $statusCode));
    }


    /**
     * Creates a HTTP request.
     * 
     * @param string $email
     * @param string $endpointUrl
     * @return Http\Request
     */
    public function createRequest($email, $endpointUrl = null)
    {
        if (null === $endpointUrl) {
            $endpointUrl = $this->getEndpointUrl();
        }
        
        $request = new Http\Request();
        $request->setUri($this->constructRequestUri($email, $endpointUrl));
        $request->setMethod(Http\Request::METHOD_GET);
        
        return $request;
    }


    /**
     * Constructs the request URL based on the endpoint URL and the provided email.
     * 
     * @param string $email
     * @param string $endpointUrl
     * @return string
     */
    protected function constructRequestUri($email, $endpointUrl)
    {
        if ($endpointUrl[strlen($endpointUrl) - 1] != '/') {
            $endpointUrl .= '/';
        }
        
        return sprintf("%s%s", $endpointUrl, urlencode($email));
    }


    /**
     * Parses the JSON value and returns an array.
     * 
     * @param string $body
     * @throws Exception\InvalidResponseFormatException
     * @return array
     */
    protected function parseResponseBody($body)
    {
        try {
            return Json::decode($body, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            throw new Exception\InvalidResponseFormatException(sprintf("Error decoding JSON: [%s] %s", get_class($e), $e->getMessage()), null, $e);
        }
    }
}