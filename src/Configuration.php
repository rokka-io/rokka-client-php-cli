<?php

namespace RokkaCli;

class Configuration
{
    private $apiUri;
    private $organization;
    private $apiSecret;
    private $apiKey;

    public function __construct($apiUri, $apiKey, $apiSecret, $organization)
    {
        $this->apiUri = $apiUri;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->organization = $organization;
    }

    /**
     * @return string
     */
    public function getApiUri()
    {
        return $this->apiUri;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
