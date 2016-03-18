<?php

namespace RokkaCli;

class Configuration
{
    protected $apiUri;
    protected $organization;
    protected $apiSecret;
    protected $apiKey;

    public function __construct($apiUri, $apiKey, $apiSecret, $organization)
    {
        $this->apiUri = $apiUri;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getApiUri()
    {
        return $this->apiUri;
    }

    /**
     * @param string $organizationName
     *
     * @return mixed
     */
    public function getOrganizationName($organizationName = null)
    {
        return $organizationName ? $organizationName : $this->organization;
    }

    /**
     * @return mixed
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
