<?php

namespace InoHibpTest;

use InoHibp\Client;


class ClientTest extends \PHPUnit_Framework_TestCase
{


    public function testConstructor()
    {
        $url = 'http://test';
        $httpClient = $this->createHttpClientMock();
        
        $client = $this->createClient($url, $httpClient);
        
        $this->assertSame($url, $client->getEndpointUrl());
        $this->assertSame($httpClient, $client->getHttpClient());
    }


    public function testCreateRequestWithoutTrailingSlash()
    {
        $url = 'http://test';
        $email = 'foo@bar.com';
        $expectedUrl = $url . '/' . urlencode($email);
        
        $client = $this->createClient($url);
        
        $request = $client->createRequest($email);
        
        $this->assertInstanceOf('Zend\Http\Request', $request);
        $this->assertSame(\Zend\Http\Request::METHOD_GET, $request->getMethod());
        $this->assertSame($expectedUrl, $request->getUriString());
    }


    public function testCreateRequestWithTrailingSlash()
    {
        $url = 'http://test/';
        $email = 'foo@bar.com';
        $expectedUrl = $url . urlencode($email);
        
        $client = $this->createClient($url);
        
        $request = $client->createRequest($email);
        
        $this->assertInstanceOf('Zend\Http\Request', $request);
        $this->assertSame(\Zend\Http\Request::METHOD_GET, $request->getMethod());
        $this->assertSame($expectedUrl, $request->getUriString());
    }


    public function testCheckEmailWithTransportException()
    {
        $this->setExpectedException('InoHibp\Exception\TransportException');
        
        $url = 'http://test';
        $email = 'foo@bar.com';
        
        $httpRequest = $this->createHttpRequestMock();
        $httpClient = $this->createHttpClientMock($httpRequest, null, new \Exception());
        
        $client = $this->createClientMock($url, $httpClient);
        $client->expects($this->once())
            ->method('createRequest')
            ->with($email)
            ->will($this->returnValue($httpRequest));
        
        $client->checkEmail($email);
    }


    public function testCheckEmailWithInvalidEmail()
    {
        $this->setExpectedException('InoHibp\Exception\InvalidEmailException');
        
        $statusCode = 400;
        $email = 'foo.bar.com';
        
        $httpRequest = $this->createHttpRequestMock();
        $httpResponse = $this->createHttpResponseMock($statusCode);
        $httpClient = $this->createHttpClientMock($httpRequest, $httpResponse);
        
        $client = $this->createClientWithStubbedCreateRequest($email, $httpRequest);
        $client->setHttpClient($httpClient);
        
        $client->checkEmail($email);
    }


    public function testCheckEmailWithUnexpectedResponse()
    {
        $this->setExpectedException('InoHibp\Exception\UnexpectedResponseException');
        
        $statusCode = 500;
        $email = 'foo.bar.com';
        
        $httpRequest = $this->createHttpRequestMock();
        $httpResponse = $this->createHttpResponseMock($statusCode);
        $httpClient = $this->createHttpClientMock($httpRequest, $httpResponse);
        
        $client = $this->createClientWithStubbedCreateRequest($email, $httpRequest);
        $client->setHttpClient($httpClient);
        
        $client->checkEmail($email);
    }


    public function testCheckEmailWithInvalidResponseFormat()
    {
        $this->setExpectedException('InoHibp\Exception\InvalidResponseFormatException');
        
        $statusCode = 200;
        $email = 'foo.bar.com';
        
        $httpRequest = $this->createHttpRequestMock();
        $httpResponse = $this->createHttpResponseMock($statusCode, 'invalid response format');
        $httpClient = $this->createHttpClientMock($httpRequest, $httpResponse);
        
        $client = $this->createClientWithStubbedCreateRequest($email, $httpRequest);
        $client->setHttpClient($httpClient);
        
        $client->checkEmail($email);
    }


    public function testCheckEmailPwned()
    {
        $statusCode = 200;
        $email = 'foo.bar.com';
        
        $pwnedSites = array(
            'foo',
            'bar',
            'baz'
        );
        $body = \Zend\Json\Json::encode($pwnedSites);
        
        $httpRequest = $this->createHttpRequestMock();
        $httpResponse = $this->createHttpResponseMock($statusCode, $body);
        $httpClient = $this->createHttpClientMock($httpRequest, $httpResponse);
        
        $client = $this->createClientWithStubbedCreateRequest($email, $httpRequest);
        $client->setHttpClient($httpClient);
        
        $this->assertEquals($pwnedSites, $client->checkEmail($email));
    }


    public function testCheckEmailNotPwned()
    {
        $statusCode = 404;
        $email = 'foo.bar.com';
        
        $httpRequest = $this->createHttpRequestMock();
        $httpResponse = $this->createHttpResponseMock($statusCode);
        $httpClient = $this->createHttpClientMock($httpRequest, $httpResponse);
        
        $client = $this->createClientWithStubbedCreateRequest($email, $httpRequest);
        $client->setHttpClient($httpClient);
        
        $this->assertNull($client->checkEmail($email));
    }
    
    /*
     * 
     */
    protected function createClient($url, $httpClient = null)
    {
        if (null === $httpClient) {
            $httpClient = $this->createHttpClientMock();
        }
        
        return new Client($url, $httpClient);
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createClientMock($url, $httpClient)
    {
        $client = $this->getMockBuilder('InoHibp\Client')
            ->setMethods(array(
            'createRequest'
        ))
            ->setConstructorArgs(array(
            $url,
            $httpClient
        ))
            ->getMock();
        
        return $client;
    }


    protected function createClientWithStubbedCreateRequest($email, $httpRequest)
    {
        $client = $this->getMockBuilder('InoHibp\Client')
            ->setMethods(array(
            'createRequest'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        
        $client->expects($this->once())
            ->method('createRequest')
            ->with($email)
            ->will($this->returnValue($httpRequest));
        
        return $client;
    }


    protected function createHttpClientMock($httpRequest = null, $httpResponse = null, $exception = null)
    {
        $httpClient = $this->getMockBuilder('Zend\Http\Client')->getMock();
        if ($httpRequest) {
            if ($httpResponse) {
                $httpClient->expects($this->once())
                    ->method('send')
                    ->with($httpRequest)
                    ->will($this->returnValue($httpResponse));
            } elseif ($exception) {
                $httpClient->expects($this->once())
                    ->method('send')
                    ->with($httpRequest)
                    ->will($this->throwException($exception));
            }
        }
        
        return $httpClient;
    }


    protected function createHttpResponseMock($statusCode = null, $body = null)
    {
        $httpResponse = $this->getMockBuilder('Zend\Http\Response')->getMock();
        if ($statusCode) {
            $httpResponse->expects($this->once())
                ->method('getStatusCode')
                ->will($this->returnValue($statusCode));
            if ($body) {
                $httpResponse->expects($this->once())
                    ->method('getBody')
                    ->will($this->returnValue($body));
            }
        }
        
        return $httpResponse;
    }


    protected function createHttpRequestMock()
    {
        $httpRequest = $this->getMock('Zend\Http\Request');
        return $httpRequest;
    }
}