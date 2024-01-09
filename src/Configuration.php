<?php

namespace RokkaCli;

class Configuration
{
    private string $apiUri;

    private ?string $apiKey;

    private ?string $organization;

    public function __construct(string $apiUri, ?string $apiKey, ?string $organization)
    {
        if (4 === \func_num_args()) {
            @trigger_error('The $apiSecret argument to the Configuration constructor has been removed in version 1.5, adjust how you instantiate the configuration.', \E_USER_DEPRECATED);
            // if old sig (with $apiSecret as 3rd arg) was used
            $organization = func_get_arg(3);
        }

        $this->apiUri = $apiUri;
        $this->apiKey = $apiKey;
        $this->organization = $organization;
    }

    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organization;
    }

    /**
     * Kept for bc reasons, in case someone uses that.
     *
     * @deprecated will be removed in 2.0.0 of the cli
     */
    public function getApiSecret(): string
    {
        return '';
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
