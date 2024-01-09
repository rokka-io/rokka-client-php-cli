<?php

namespace RokkaCli\Command;

use Rokka\Client\Image;
use RokkaCli\Provider\ClientProvider;
use RokkaCli\RokkaApiHelper;
use RokkaCli\RokkaFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseRokkaCliCommand extends Command
{
    /**
     * @var ClientProvider
     */
    protected $clientProvider;

    /**
     * @var RokkaApiHelper
     */
    protected $rokkaHelper;

    /**
     * @var RokkaFormatter
     */
    protected $formatterHelper;

    /**
     * Prefix for command names. E.g. "rokka:".
     *
     * @var string
     */
    private $namePrefix;

    public function __construct(ClientProvider $clientProvider, RokkaApiHelper $rokkaHelper, $namePrefix = '')
    {
        $this->clientProvider = $clientProvider;
        $this->rokkaHelper = $rokkaHelper;
        $this->formatterHelper = new RokkaFormatter();
        $this->namePrefix = $namePrefix;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten to prepend the name prefix to all command names.
     */
    public function setName(string $name): static
    {
        return parent::setName($this->namePrefix.$name);
    }

    /**
     * Ensures that the given Stack exists for the input Organization.
     */
    public function verifyStackExists(string $stackName, string $organization, OutputInterface $output, Image $client = null): bool
    {
        if (!$client) {
            $client = $this->clientProvider->getImageClient($organization);
        }

        if (!$stackName || !$this->rokkaHelper->stackExists($client, $stackName, $organization)) {
            $output->writeln(
                $this->formatterHelper->formatBlock([
                    'Error!',
                    'Stack "'.$stackName.'"  does not exist on "'.$organization.'" organization!',
                ], 'error', true)
            );

            return false;
        }

        return true;
    }

    /**
     * Get a valid organization name.
     *
     * If an organization name is provided, make sure it is valid and actually exists in the API.
     * Otherwise return the default organization name.
     *
     * @param string          $organizationName The organization name or an empty value if none is specified
     * @param OutputInterface $output           Console to write information for the user
     *
     * @return string|bool the organization name to use or false if the provided name is not valid
     */
    public function resolveOrganizationName(string $organizationName, OutputInterface $output): bool|string
    {
        if (!$organizationName) {
            $organizationName = $this->rokkaHelper->getDefaultOrganizationName();
        }

        if (!$this->rokkaHelper->validateOrganizationName($organizationName)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The organization name "'.$organizationName.'" is not valid!',
            ], 'error', true));

            return false;
        }

        $client = $this->clientProvider->getUserClient();
        if ($this->rokkaHelper->organizationExists($client, $organizationName)) {
            return $organizationName;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The organization "'.$organizationName.'" does not exists!',
        ], 'error', true));

        return false;
    }

    /**
     * Verify that the given Source image exists, output the error message if needed.
     */
    public function verifySourceImageExists(string $hash, string $organizationName, OutputInterface $output, Image $client): bool
    {
        if (!$this->rokkaHelper->validateImageHash($hash)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The Image HASH "'.$hash.'" is not valid!',
            ], 'error', true));

            return false;
        }

        if ($this->rokkaHelper->imageExists($client, $hash, $organizationName)) {
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The SourceImage "'.$hash.'" has not been found in Organization "'.$organizationName.'"',
        ], 'error', true));

        return false;
    }

    public function verifyLocalImageExists(string $fileName, OutputInterface $output): bool
    {
        if (!file_exists($fileName)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The image "'.$fileName.'" does not exist!',
            ], 'error', true));

            return false;
        }

        return true;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($this->clientProvider->isDefaultApiUri()) {
            return;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Warning!',
            'Rokka API Uri has been overridden, API calls are performed to "'.$this->clientProvider->getApiUri().'".',
        ], 'comment', true));
    }
}
