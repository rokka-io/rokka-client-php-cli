<?php

namespace RokkaCli\Provider;

use Rokka\Client\Base;
use Rokka\Client\Factory;
use Rokka\Client\Image;
use Rokka\Client\User;
use RokkaCli\Configuration;

class ClientProvider
{
    protected Configuration $configuration;

    /**
     * @var Image[] Index is the organization name
     */
    private array $imageClient = [];

    private ?User $userClient = null;

    /**
     * If a client is not specified, a new one is set up from the configuration.
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
    public function isDefaultApiUri(): bool
    {
        return Base::DEFAULT_API_BASE_URL === $this->configuration->getApiUri();
    }

    public function getApiUri(): string
    {
        return $this->configuration->getApiUri();
    }

    /**
     * @throws \RuntimeException
     */
    public function getUserClient(): ?User
    {
        if (!$this->userClient) {
            $this->userClient = Factory::getUserClient(
                null, $this->configuration->getApiKey(), [Factory::API_BASE_URL => $this->configuration->getApiUri()]
            );
        }

        return $this->userClient;
    }

    /**
     * @throws \RuntimeException
     */
    public function getImageClient(?string $organization = null): Image
    {
        if (!$organization) {
            $organization = $this->configuration->getOrganizationName();
        }

        if (!array_key_exists($organization, $this->imageClient)) {
            $this->imageClient[$organization] = Factory::getImageClient(
                $organization,
                $this->configuration->getApiKey(),
                [Factory::API_BASE_URL => $this->configuration->getApiUri()]
            );
        }

        return $this->imageClient[$organization];
    }
}
