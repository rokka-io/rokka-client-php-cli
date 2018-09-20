<?php

namespace RokkaCli;

class Configuration
{
    private $apiUri;

    private $organization;

    private $apiKey;

    public function __construct($apiUri, $apiKey, $organization)
    {
        if (4 === \func_num_args()) {
            @trigger_error(sprintf('The $apiSecret argument to the configuration has been removed in version 1.5, adjust how you instantiate the configuration.', __METHOD__), E_USER_DEPRECATED);
            // if old sig (with $apiSecret as 3rd arg) was used
            $organization = func_get_arg(3);
        }

        $this->apiUri = $apiUri;
        $this->apiKey = $apiKey;
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
     * Kept for bc reasons, in case someone uses that.
     *
     * @deprecated 2.0.0
     *
     * @return string
     */
    public function getApiSecret()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}
