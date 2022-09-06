<?php

namespace RokkaCli;

use Symfony\Component\Console\Application;

/**
 * Overwrite some methods to output more accurate help.
 */
class ConsoleApplication extends Application
{
    private ?string $organization;

    /**
     * @param string      $name         The name of the application
     * @param string      $version      The version of the application
     * @param string|null $organization The configured organization, or null if not configured
     */
    public function __construct(string $name, string $version, ?string $organization)
    {
        parent::__construct($name, $version);

        $this->organization = $organization;
    }

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     */
    public function getLongVersion(): string
    {
        $version = ('@package_version@' !== $this->getVersion())
            ? sprintf('%s <info>%s</info>', $this->getName(), $this->getVersion())
            : $this->getName()
        ;

        if ($this->organization) {
            $version .= sprintf(' <info>(configured organization: %s)</info>', $this->organization);
        } else {
            $version .= ' <info>(unconfigured, only limited set of commands available. Missing rokka.yml configuration?)</info>';
        }

        return $version;
    }
}
