<?php

namespace RokkaCli;

use GuzzleHttp\Client;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;
use Rokka\Client\User;
use Symfony\Component\Yaml\Yaml;

class RokkaLibrary
{
    /**
     * @param User $client
     * @param $organizationName
     *
     * @return Organization|null
     */
    public static function getOrganization(User $client, $organizationName)
    {
        try {
            $org = $client->getOrganization($organizationName);

            if ($org instanceof Organization) {
                return $org;
            }
        } catch (\Exception $e) {
            //
        }

        return;
    }

    /**
     * @param Image $client
     * @param $hash
     * @param $organizationName
     *
     * @return SourceImage|void
     */
    public static function getSourceImage(Image $client, $hash, $organizationName)
    {
        try {
            $sourceImage = $client->getSourceImage($hash, false, $organizationName);

            if ($sourceImage instanceof SourceImage) {
                return $sourceImage;
            }
        } catch (\Exception $e) {
            //
        }

        return;
    }

    /**
     * @param Image $client
     * @param $hash
     * @param $organizationName
     * @param null   $stackName
     * @param string $format
     *
     * @return string
     */
    public static function getSourceImageContents(Image $client, $hash, $organizationName, $stackName = null, $format = 'jpg')
    {
        if (!$stackName) {
            return $client->getSourceImageContents($hash, $organizationName);
        } else {
            $uri = $client->getSourceImageUri($hash, $stackName, $format, null, $organizationName);
            $resp = (new Client())->get($uri);

            return $resp->getBody()->getContents();
        }
    }

    /**
     * @param User $client
     * @param $organizationName
     *
     * @return bool
     */
    public static function organizationExists(User $client, $organizationName)
    {
        try {
            $org = $client->getOrganization($organizationName);

            return $org->getName() == $organizationName;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify that the given Stack esists.
     *
     * @param Image $client
     * @param $stackName
     * @param string $organizationName
     *
     * @return bool
     */
    public static function stackExists(Image $client, $stackName, $organizationName = '')
    {
        try {
            $stack = $client->getStack($stackName, $organizationName);

            return $stack->getName() == $stackName;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate the given Source Image Hash.
     *
     * @param string $hash
     *
     * @return bool
     */
    public static function validateImageHash($hash)
    {
        return preg_match('/^[a-f0-9]{40}$/', $hash) == 1;
    }

    /**
     * @param $fileName
     * @param Configuration $config
     *
     * @return int|bool
     */
    public static function updateConfigToFile($fileName, Configuration $config)
    {
        $configArray = [
            'api_key' => $config->getApiKey(),
            'api_secret' => $config->getApiSecret(),
            'api_uri' => $config->getApiUri(),
            'organization' => $config->getOrganizationName() ? $config->getOrganizationName() : '',
        ];

        $yml = [];
        if (file_exists($fileName)) {
            $yml = Yaml::parse(file_get_contents($fileName));
        }

        $yml = array_merge($yml, ['rokka_cli' => $configArray]);

        return file_put_contents($fileName, Yaml::dump($yml));
    }

    /**
     * Build a string representation for the StackOperation's options attribute.
     *
     * @param array $settings
     *
     * @return string
     */
    public static function formatStackOperationOptions(array $settings, $associative = false)
    {
        $data = [];

        if (!$associative) {
            $fixed_settings = [];
            foreach ($settings as $name => $value) {
                $fixed_settings[] = array(
                    'name' => $name,
                    'value' => $value,
                );
            }

            $settings = $fixed_settings;
        }

        foreach ($settings as $setting) {
            $data[] = $setting['name'].':'.$setting['value'];
        }

        return implode(' | ', $data);
    }
}
