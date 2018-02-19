<?php

namespace RokkaCli\Provider;

use Rokka\Client\Base;
use Rokka\Client\Factory;
use Rokka\Client\Image;
use Rokka\Client\User;
use RokkaCli\Configuration;

class ClientProvider
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Image[] Index is the organization name
     */
    private $imageClient = [];

    /**
     * @var User
     */
    private $userClient = null;

    /**
     * If a client is not specified, a new one is set up from the configuration.
     *
     * @param Configuration $configuration
     * @param User|null     $userClient    The user client to use
     * @param Image|null    $imageClient   The image client to use for the organization specified in the configuration
     */
    public function __construct(Configuration $configuration, User $userClient = null, Image $imageClient = null)
    {
        $this->configuration = $configuration;
        $this->userClient = $userClient;
        if ($imageClient) {
            $this->imageClient[$configuration->getOrganizationName()] = $imageClient;
        }
    }

    /**
     * @return bool whether this client is configured to work against the default rokka API URI
     */
    public function isDefaultApiUri()
    {
        return Base::DEFAULT_API_BASE_URL === $this->configuration->getApiUri();
    }

    /**
     * @return string The rokka API URI
     */
    public function getApiUri()
    {
        return $this->configuration->getApiUri();
    }

    /**
     * @return User
     */
    public function getUserClient()
    {
        if (!$this->userClient) {
            $this->userClient = Factory::getUserClient(
                $this->configuration->getApiUri()
            );

            $this->userClient->setCredentials(
                $this->configuration->getApiKey()
            );
        }

        return $this->userClient;
    }

    /**
     * @param $organization
     *
     * @return Image
     */
    public function getImageClient($organization = null)
    {
        if (!$organization) {
            $organization = $this->configuration->getOrganizationName();
        }

        if (!array_key_exists($organization, $this->imageClient)) {
            $this->imageClient[$organization] = Factory::getImageClient(
                $organization,
                $this->configuration->getApiKey(),
                $this->configuration->getApiUri()
            );
        }

        return $this->imageClient[$organization];
    }
}
