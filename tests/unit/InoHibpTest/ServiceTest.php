<?php

namespace InoHibpTest;

use InoHibp\Service;


class ServiceTest extends \PHPUnit_Framework_TestCase
{


    public function testSetOptions()
    {
        $defaultOptions = array(
            'opt1' => 'value1',
            'opt2' => 'value2',
            'opt3' => 'value3'
        );
        $options = array(
            'opt2' => 'value2-1',
            'opt4' => 'value4'
        );
        $expectedOptions = array(
            'opt1' => 'value1',
            'opt2' => 'value2-1',
            'opt3' => 'value3',
            'opt4' => 'value4'
        );
        
        $service = new Service();
        $service->setDefaultOptions($defaultOptions);
        $service->setOptions($options);
        
        $this->assertEquals($expectedOptions, $service->getOptions());
    }


    public function testGetImplicitHttpClient()
    {
        $service = new Service(array(
            Service::OPT_ENDPOINT_URL => 'http://test'
        ));
        $this->assertInstanceOf('Zend\Http\Client', $service->getClient()
            ->getHttpClient());
    }


    public function testGetImplicitHttpsClientWithMissingCaFile()
    {
        $this->setExpectedException('InoHibp\Exception\InvalidCaFileException');
        
        $service = new Service(array(
            Service::OPT_USE_SSL => true,
            Service::OPT_CA_FILE => '/non/existent/ca/file'
        ));
        $service->getClient()->getHttpClient();
    }


    public function testGetImplicitHttpsClient()
    {
        $service = new Service(array(
            Service::OPT_USE_SSL => true,
            Service::OPT_CA_FILE => INOHIBP_TESTS_DATA_DIR . '/ca-bundle.pem'
        ));
        $httpClient = $service->getClient()->getHttpClient();
        
        $this->assertInstanceOf('Zend\Http\Client', $httpClient);
        $this->assertInstanceOf('Zend\Http\Client\Adapter\Curl', $httpClient->getAdapter());
    }


    public function testCheckEmail()
    {
        $email = 'foo@bar.com';
        $response = array(
            'site1',
            'site2'
        );
        
        $client = $this->getMockBuilder('InoHibp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('checkEmail')
            ->with($email)
            ->will($this->returnValue($response));
        
        $service = new Service();
        $service->setClient($client);
        
        $this->assertSame($response, $service->checkEmail($email));
    }
}