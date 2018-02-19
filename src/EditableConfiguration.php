<?php

namespace RokkaCli;

use Symfony\Component\Yaml\Yaml;

class EditableConfiguration extends Configuration
{
    /**
     * @param string        $fileName
     * @param Configuration $config
     *
     * @return int|bool Bytes written or false if writing failed
     */
    public function updateConfigToFile($fileName, Configuration $config)
    {
        $configArray = [
            'api_key' => $config->getApiKey(),
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

    public function getConfigFileName()
    {
        return getcwd().DIRECTORY_SEPARATOR.'rokka.yml';
    }
}
