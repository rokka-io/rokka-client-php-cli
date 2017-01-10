<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\Membership;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\Stack;
use Rokka\Client\Image;
use RokkaCli\Configuration;
use RokkaCli\Provider\ClientProvider;
use RokkaCli\RokkaHelper;
use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

abstract class BaseRokkaCliCommand extends Command
{
    /**
     * @var ClientProvider
     */
    protected $clientProvider;

    /**
     * @var RokkaHelper
     */
    protected $rokkaHelper;

    /**
     * @var FormatterHelper
     */
    protected $formatterHelper = null;

    public function __construct(ClientProvider $clientProvider, RokkaHelper $rokkaHelper)
    {
        $this->clientProvider = $clientProvider;
        $this->rokkaHelper = $rokkaHelper;
        $this->formatterHelper = new FormatterHelper();
        parent::__construct();
    }

    /**
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($this->rokkaHelper->isDefaultApiUri()) {
            return;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Warning!',
            'Rokka API Uri has been overridden, API calls are performed to "'.$this->rokkaHelper->getApiUri().'".',
        ], 'comment', true));
    }

    /**
     * Ensures that the given Stack exists for the input Organization.
     *
     * @param $stackName
     * @param $organization
     * @param OutputInterface $output
     * @param Image           $client
     *
     * @return bool
     */
    public function verifyStackExists($stackName, $organization, OutputInterface $output, Image $client = null)
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
     * @return string|bool The organization name to use or false if the provided name is not valid.
     */
    public function resolveOrganizationName($organizationName, OutputInterface $output)
    {
        if (!$organizationName) {
            return $this->rokkaHelper->getDefaultOrganizationName();
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
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The organization "'.$organizationName.'" does not exists!',
        ], 'error', true));

        return false;
    }

    /**
     * Verify that the given Source image exists, output the error message if needed.
     *
     * @param string          $hash
     * @param string          $organizationName
     * @param OutputInterface $output
     * @param Image           $client
     *
     * @return bool
     */
    public function verifySourceImageExists($hash, $organizationName, OutputInterface $output, Image $client)
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

    /**
     * @param string          $fileName
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function verifyLocalImageExists($fileName, OutputInterface $output)
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


    /**
     * @param string $fileName
     * @param Configuration $config
     *
     * @return int|bool
     */
    protected function updateConfigToFile($fileName, Configuration $config)
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
     * @param SourceImage     $sourceImage
     * @param OutputInterface $output
     */
    public static function outputImageInfo(SourceImage $sourceImage, OutputInterface $output)
    {
        $output->writeln([
            '  Hash: <info>'.$sourceImage->hash.'</info>',
            '  Organization: <info>'.$sourceImage->organization.'</info>',
            '  Name: <info>'.$sourceImage->name.'</info>',
            '  Size: <info>'.$sourceImage->size.'</info>',
            '  Format: <info>'.$sourceImage->format.'</info>',
            '  Created: <info>'.$sourceImage->created->format('Y-m-d H:i:s').'</info>',
            '  Dimensions: <info>'.$sourceImage->width.'x'.$sourceImage->height.'</info>',
        ]);

        if ($output->isVerbose()) {
            $output->writeln('  BinaryHash: <info>'.$sourceImage->binaryHash.'</info>');
        }

        if (!empty($sourceImage->dynamicMetadata)) {
            if (!$output->isVerbose()) {
                $metaNames = array_keys($sourceImage->dynamicMetadata);
                $output->writeln('  DynamicMetadatas ('.count($metaNames).'): '.implode(', ', $metaNames));
            } else {
                $output->writeln('  DynamicMetadatas:');
                /** @var DynamicMetadataInterface $meta */
                foreach ($sourceImage->dynamicMetadata as $name => $meta) {
                    $output->writeln('     - <info>'.$name.'</info> '.self::getDynamicMetadataInfo($meta));
                }
            }
        }
    }

    /**
     * @param Organization    $org
     * @param OutputInterface $output
     */
    public static function outputOrganizationInfo(Organization $org, OutputInterface $output)
    {
        $output->writeln('  ID: <info>'.$org->getId().'</info>');
        $output->writeln('  Name: <info>'.$org->getName().'</info>');
        $output->writeln('  Display Name: <info>'.$org->getDisplayName().'</info>');
        $output->writeln('  Billing eMail: <info>'.$org->getBillingEmail().'</info>');
    }

    /**
     * @param Membership      $membership
     * @param OutputInterface $output
     */
    public static function outputOrganizationMembershipInfo(Membership $membership, OutputInterface $output)
    {
        $output->writeln('  ID: <info>'.$membership->userId.'</info>');
        $output->writeln('  Role: <info>'.$membership->role.'</info>');
        $output->writeln('  Active: <info>'.($membership->active ? 'True' : 'False').'</info>');
    }

    /**
     * @param Stack           $stack
     * @param OutputInterface $output
     */
    public static function outputStackInfo(Stack $stack, OutputInterface $output)
    {
        $output->writeln('  Name: <info>'.$stack->getName().'</info>');
        $output->writeln('  Created: <info>'.$stack->getCreated()->format('Y-m-d H:i:s').'</info>');

        $operations = $stack->getStackOperations();
        if (!empty($operations)) {
            $output->writeln('  Operations:');

            foreach ($stack->getStackOperations() as $operation) {
                $output->write('    '.$operation->name.': ');
                $output->writeln($this->rokkaHelper->formatStackOperationOptions($operation->options));
            }
        }

        $options = $stack->getStackOptions();
        if (!empty($options)) {
            $output->writeln('  Options:');
            foreach ($stack->getStackOptions() as $name => $value) {
                $output->write('    '.$name.': ');
                $output->writeln('<info>'.$value.'</info>');
            }
        }
    }

    /**
     * @param DynamicMetadataInterface $metadata
     *
     * @return string
     */
    protected static function getDynamicMetadataInfo(DynamicMetadataInterface $metadata)
    {
        $info = null;
        switch ($metadata->getName()) {
            case 'SubjectArea':
                $data = [];
                /* @var SubjectArea $metadata */
                foreach (['x', 'y', 'width', 'height'] as $property) {
                    $data[] = $property.':'.$metadata->$property;
                }
                $info = implode('|', $data);
                break;
        }

        return $info;
    }
}
