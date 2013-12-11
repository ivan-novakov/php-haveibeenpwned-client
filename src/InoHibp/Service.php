<?php

namespace InoHibp;

use Zend\Http;


/**
 * The service encapsulates the client and provides some "user-friendliness".
 */
class Service
{

    const OPT_ENDPOINT_URL = 'endpoint_url';

    const OPT_SSL_ENDPOINT_URL = 'ssl_endpoint_url';

    const OPT_CA_FILE = 'ca_file';

    const OPT_USE_SSL = 'use_ssl';

    const OPT_HTTP_CLIENT_USER_AGENT = 'http_client_user_agent';

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $defaultOptions;


    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setDefaultOptions($this->initDefaultOptions());
        $this->setOptions($options);
    }


    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $options = $options + $this->getDefaultOptions();
        $this->options = $options;
    }


    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }


    /**
     * @param array $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }


    /**
     * @return Client
     */
    public function getClient()
    {
        if (! $this->client instanceof Client) {
            $this->client = $this->createClient();
        }
        return $this->client;
    }


    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }


    /**
     * Checks if the provided email has been pwned.
     * 
     * @param string $email
     * @return array|null
     */
    public function checkEmail($email)
    {
        return $this->getClient()->checkEmail($email);
    }


    /**
     * Creates a client object.
     * 
     * @return Client
     */
    protected function createClient()
    {
        return new Client($this->getEndpointUrl(), $this->createHttpClient());
    }


    /**
     * Creates a HTTP client object.
     * 
     * @param string $useSsl
     * @return Http\Client
     */
    protected function createHttpClient($useSsl = null)
    {
        if (null === $useSsl) {
            $useSsl = $this->useSsl();
        }
        
        $httpClient = new \Zend\Http\Client(null, array(
            'useragent' => $this->getOption(self::OPT_HTTP_CLIENT_USER_AGENT)
        ));
        
        if ($useSsl) {
            $adapter = new Http\Client\Adapter\Curl();
            $adapter->setOptions(array(
                'curloptions' => array(
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_CAINFO => $this->getCaFile()
                )
            ));
            $httpClient->setAdapter($adapter);
        }
        
        return $httpClient;
    }


    /**
     * Returns the remote service's endpoint URL.
     * 
     * @return string
     */
    protected function getEndpointUrl()
    {
        if ($this->getOption(self::OPT_USE_SSL)) {
            return $this->getOption(self::OPT_SSL_ENDPOINT_URL);
        }
        
        return $this->getOption(self::OPT_ENDPOINT_URL);
    }


    /**
     * Returns the path of the valid CA file.
     * 
     * @throws Exception\InvalidCaFileException
     * @return string
     */
    protected function getCaFile()
    {
        $caFile = $this->getOption(self::OPT_CA_FILE);
        if (! is_file($caFile) || ! is_readable($caFile)) {
            throw new Exception\InvalidCaFileException(sprintf("Cannot read CA file '%s'", $caFile));
        }
        
        return $caFile;
    }


    /**
     * Returns true, if SSL should be used.
     * 
     * @return boolean
     */
    protected function useSsl()
    {
        return (boolean) $this->getOption(self::OPT_USE_SSL);
    }


    /**
     * Returns the required option value.
     * 
     * @param string $name
     * @return mixed|null
     */
    protected function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        
        return null;
    }


    /**
     * Initializes and returns the default options.
     * 
     * @return array boolean
     */
    protected function initDefaultOptions()
    {
        return array(
            self::OPT_ENDPOINT_URL => 'http://haveibeenpwned.com/api/breachedaccount/',
            self::OPT_SSL_ENDPOINT_URL => 'https://haveibeenpwned.com/api/breachedaccount/',
            self::OPT_CA_FILE => __DIR__ . '/../../ssl/ca-bundle.pem',
            self::OPT_USE_SSL => false,
            self::OPT_HTTP_CLIENT_USER_AGENT => 'PHP HaveIBeenPwned Client (https://github.com/ivan-novakov/php-haveibeenpwned-client)'
        );
    }
}