<?php

namespace RokkaCli;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;
use Rokka\Client\User;

/**
 * Utility functions to work with the rokka API.
 */
class RokkaApiHelper
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getDefaultOrganizationName()
    {
        if (!$this->configuration->getOrganizationName()) {
            throw new \Exception('Missing configuration');
        }

        return $this->configuration->getOrganizationName();
    }

    /**
     * Validate that $organizationName is a valid name for an organization.
     *
     * @param string $organizationName
     *
     * @return bool
     */
    public function validateOrganizationName($organizationName)
    {
        return is_string($organizationName) && '' !== $organizationName;
    }

    /**
     * Checks if the specified organization is visible to the current user.
     *
     * @param User   $client
     * @param string $organizationName
     *
     * @return bool
     */
    public function organizationExists(User $client, $organizationName)
    {
        try {
            $org = $client->getOrganization($organizationName);

            return $org->getName() == $organizationName;
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Checks if the specified stack exists in the organization.
     *
     * @param $stackName
     * @param $organizationName
     * @param Image $client
     *
     * @return bool
     */
    public function stackExists(Image $client, $stackName, $organizationName = '')
    {
        try {
            $stack = $client->getStack($stackName, $organizationName);

            return $stack->getName() == $stackName;
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Sanity check if an image hash looks valid.
     *
     * @param string $hash
     *
     * @return bool
     */
    public function validateImageHash($hash)
    {
        return 1 == preg_match('/^[a-f0-9]{40}$/', $hash);
    }

    /**
     * Checks if the specified image exists in the organization.
     *
     * @param Image  $client
     * @param string $hash
     * @param string $organizationName
     *
     * @return bool
     */
    public function imageExists(Image $client, $hash, $organizationName = '')
    {
        try {
            $sourceImage = $client->getSourceImage($hash, $organizationName);

            return $sourceImage instanceof SourceImage && $sourceImage->hash === $hash;
        } catch (ClientException $e) {
            if (404 === $e->getCode()) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Download an image.
     *
     * @param Image  $client
     * @param string $hash
     * @param string $organizationName
     * @param string $stackName        Optional, if not specified the unmodified source is downloaded
     * @param string $format           Defaults to jpg
     *
     * @return string The binary data for the image
     */
    public function getSourceImageContents(Image $client, $hash, $organizationName, $stackName = null, $format = 'jpg')
    {
        if (!$stackName) {
            return $client->getSourceImageContents($hash, $organizationName);
        }
        $uri = $client->getSourceImageUri($hash, $stackName, $format, null, $organizationName);
        $resp = (new Client())->get($uri);

        return $resp->getBody()->getContents();
    }
}
