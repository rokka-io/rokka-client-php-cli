<?php

namespace RokkaCli;

use Symfony\Component\Yaml\Yaml;

class EditableConfiguration extends Configuration
{
    /**
     * @return int|bool Bytes written or false if writing failed
     */
    public function updateConfigToFile(string $fileName, Configuration $config): bool|int
    {
        $configArray = [
            'api_key' => $config->getApiKey(),
            'api_uri' => $config->getApiUri(),
            'organization' => $config->getOrganizationName() ?: '',
        ];

        $yml = [];
        if (file_exists($fileName)) {
            $yml = Yaml::parse(file_get_contents($fileName));
        }

        $yml = array_merge($yml, ['rokka_cli' => $configArray]);

        return file_put_contents($fileName, Yaml::dump($yml));
    }

    public function getConfigFileName(): string
    {
        return getcwd().\DIRECTORY_SEPARATOR.'rokka.yml';
    }
}
