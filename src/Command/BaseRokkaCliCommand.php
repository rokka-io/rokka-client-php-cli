<?php

namespace RokkaCli\Command;

use Rokka\Client\Base;
use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\Membership;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\Stack;
use Rokka\Client\User;
use Rokka\Client\Factory;
use Rokka\Client\Image;
use RokkaCli\Configuration;
use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseRokkaCliCommand extends Command
{
    /** @var Configuration */
    protected $configuration;

    /** @var Image */
    protected static $imageClient = null;

    /** @var User */
    protected static $userClient = null;

    /** @var FormatterHelper */
    protected $formatterHelper = null;

    public function __construct(Configuration $configuration)
    {
        // Saving the default configuration to be used later.
        $this->configuration = $configuration;
        $this->formatterHelper = new FormatterHelper();
        parent::__construct();
    }

    /**
     * @param $reset
     *
     * @return \Rokka\Client\User
     */
    protected function getUserClient($reset = false)
    {
        if (!static::$userClient || $reset) {
            static::$userClient = Factory::getUserClient(
                $this->configuration->getApiUri()
            );

            static::$userClient->setCredentials(
                $this->configuration->getApiKey(),
                $this->configuration->getApiSecret()
            );
        }

        return static::$userClient;
    }

    /**
     * @param $organization
     * @param $reset
     *
     * @return \Rokka\Client\Image
     */
    protected function getImageClient($organization = null, $reset = false)
    {
        if (!$organization) {
            $organization = $this->configuration->getOrganizationName();
        }

        if (!static::$imageClient || $reset) {
            static::$imageClient = Factory::getImageClient(
                $organization,
                $this->configuration->getApiKey(),
                $this->configuration->getApiSecret(),
                $this->configuration->getApiUri()
            );
        }

        return static::$imageClient;
    }

    /**
     * @param OutputInterface $output
     */
    protected function displayWarningOverridenAPI(OutputInterface $output)
    {
        if ($this->isDefaultRokkaAPIUri()) {
            return;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Warning!',
            'Rokka API Uri has been overridden, API calls are performed to "'.$this->configuration->getApiUri().'".',
        ], 'comment', true));
    }

    /**
     * @return bool
     */
    protected function isDefaultRokkaAPIUri()
    {
        return $this->configuration->getApiUri() == Base::DEFAULT_API_BASE_URL;
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
        $client = $client ? $client : $this->getImageClient($organization);

        if (!$stackName || !RokkaLibrary::stackExists($client, $stackName, $organization)) {
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
     * @param $organization
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function verifyOrganizationName($organization, OutputInterface $output)
    {
        if ($organization) {
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The organization "'.$organization.'" is not valid!',
        ], 'error', true));

        return false;
    }

    /**
     * @param $organization
     * @param OutputInterface $output
     * @param User|null       $client
     *
     * @return bool
     */
    public function verifyOrganizationExists($organization, OutputInterface $output, User $client = null)
    {
        if (!$organization) {
            return false;
        }

        $client = $client ? $client : $this->getUserClient();

        if (RokkaLibrary::organizationExists($client, $organization)) {
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The organization "'.$organization.'" does not exists!',
        ], 'error', true));

        return false;
    }

    /**
     * Verify that the given Hash is valid, output the error message if needed.
     *
     * @param $hash
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function verifySourceImageHash($hash, OutputInterface $output)
    {
        if (RokkaLibrary::validateImageHash($hash)) {
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The Image HASH "'.$hash.'" is not valid!',
        ], 'error', true));

        return false;
    }

    /**
     * Verify that the given Source image exists, output the error message if needed.
     *
     * @param $hash
     * @param $organization
     * @param OutputInterface $output
     * @param Image           $client
     *
     * @return bool
     */
    public function verifySourceImageExists($hash, $organization, OutputInterface $output, Image $client = null)
    {
        if (!$this->verifySourceImageHash($hash, $output)) {
            return false;
        }

        $client = $client ? $client : $this->getImageClient($organization);
        $image = RokkaLibrary::getSourceImage($client, $hash, $organization);

        if ($image instanceof SourceImage && $image->hash == $hash) {
            return true;
        }

        $output->writeln($this->formatterHelper->formatBlock([
            'Error!',
            'The SourceImage "'.$hash.'" has not been found on Organization "'.$organization.'"',
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
                $output->writeln(RokkaLibrary::formatStackOperationOptions($operation->options));
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

    /**
     * Downloads and saves an image from Rokka.
     *
     * @param Image           $client    The Image client to use
     * @param SourceImage     $image     The image to download
     * @param string          $saveTo    The destination stream to save the image to
     * @param OutputInterface $output    The output interface to display messages
     * @param null            $stackName The stack name to use, leave empty to use the source image on Rokka
     * @param string          $format    The file format to retrieve the image if using a Stack
     *
     * @return bool The status of the operation, True if the image has been saved correctly, false otherwise.
     */
    protected function saveImageContents(Image $client, SourceImage $image, $saveTo, OutputInterface $output, $stackName = null, $format = 'jpg')
    {
        if (!$stackName) {
            $output->writeln('Getting source image contents for <info>'.$image->hash.'</info> from <comment>'.$image->organization.'</comment>');
        } else {
            $output->writeln('Rendering image  <info>'.$image->hash.'</info> from <comment>'.$image->organization.'</comment> on stack <info>'.$stackName.'</info>');
        }

        $contents = RokkaLibrary::getSourceImageContents($client, $image->hash, $image->organization, $stackName, $format);
        if (!$contents) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error getting image contents from Rokka.io!',
            ], 'error', true));

            return false;
        }

        $ret = file_put_contents($saveTo, $contents, FILE_BINARY);
        if (false == $ret) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error writing image contents to <info>'.$saveTo.'</info>!',
            ], 'error', true));

            return false;
        }

        $output->writeln('Image saved to <info>'.$saveTo.'</info>');

        return true;
    }
}
