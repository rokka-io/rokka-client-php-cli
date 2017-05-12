<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCloneCommand extends BaseRokkaCliCommand
{
    /**
     * @param SourceImage $image
     * @param $orgSource
     * @param $orgDest
     * @param $stackName
     * @param Image           $imageClient
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int
     */
    public function cloneImage(SourceImage $image, $orgSource, $orgDest, $stackName, Image $imageClient, OutputInterface $output)
    {
        $output->write('Cloning image: <info>'.$image->name.'</info> (<comment>'.$image->hash.'</comment>) '.$image->size.' bytes');

        $contents = null;

        if ($output->isVerbose()) {
            $output->writeln('');
            $output->write(' - Downloading image from <comment>'.$orgSource.'</comment> ... ');
        }

        if ($stackName) {
            $imageUri = $imageClient->getSourceImageUri($image->hash, $stackName);
            $contents = file_get_contents($imageUri);
        } else {
            $contents = $imageClient->getSourceImageContents($image->hash, $orgSource);
        }

        if (!$contents) {
            throw new \Exception('Can not download image from Source Organization.');
        }
        if ($output->isVerbose()) {
            $output->writeln('<info>done</info>');
        }

        if ($output->isVerbose()) {
            $output->write(' - Uploading image to <comment>'.$orgDest.'</comment> ... ');
        }

        // Uploading image to destination
        $collection = $imageClient->uploadSourceImage($contents, $image->name, $orgDest);

        if ($collection->count() != 1) {
            throw new \Exception('Error while uploading image to destination organization');
        }

        // The original HASH could not be the same as the one we get from uploading the new image, keep the latter one.
        $currentHash = $collection->getSourceImages()[0]->hash;

        if ($output->isVerbose() || $output->isVeryVerbose()) {
            if ($output->isVeryVerbose()) {
                $output->write(' (hash:'.$currentHash.') ');
            }
            $output->writeln('<info>done</info>');
        }

        // Setting Dynamic Metadata
        foreach ($image->dynamicMetadata as $name => $metadata) {
            if ($output->isVerbose()) {
                $output->write(' - Setting Dynamic Metadata <info>'.$name.'</info> to image  ... ');
            }

            $ret = $imageClient->setDynamicMetadata($metadata, $currentHash, $orgDest);
            if (!$ret) {
                throw new \Exception('Error setting DynamicMetadata for image.');
            }

            // Keep applying the metadata to the latest generated hash?
            $currentHash = $ret;

            if ($output->isVerbose() || $output->isVeryVerbose()) {
                if ($output->isVeryVerbose()) {
                    $output->write('(new-hash:'.$currentHash.') ');
                }
                $output->writeln('<info>done</info>');
            }
        }

        if (!$output->isVerbose()) {
            $output->write(' ... ');
            $output->writeln('<info>done</info>');
        }

        return true;
    }

    protected function configure()
    {
        $this
            ->setName('image:clone')
            ->setDescription('Clones an image to another organization')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Image Hash to to copy')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
            ->addOption('stack-name', null, InputOption::VALUE_REQUIRED, 'ImageStack to use to download source images', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orgSource = $input->getOption('source-organization');
        if (!$orgSource = $this->resolveOrganizationName($orgSource, $output)) {
            return -1;
        }

        $orgDest = $input->getArgument('dest-organization');
        if (!$orgDest = $this->resolveOrganizationName($orgDest, $output)) {
            return -1;
        }

        if ($orgSource === $orgDest) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The organizations to clone images between must not be the same: "'.$orgSource.'"!',
            ], 'error', true));

            return -1;
        }

        $client = $this->clientProvider->getImageClient($orgSource);

        $hash = $input->getArgument('hash');
        if (!$this->verifySourceImageExists($hash, $orgSource, $output, $client)) {
            return -1;
        }

        $stackName = $input->getOption('stack-name');
        if ($stackName && !$this->verifyStackExists($stackName, $orgSource, $output)) {
            return -1;
        }

        $image = $client->getSourceImage($hash, $orgSource);

        // Avoid further processing if no stacks have been loaded.
        if (empty($image)) {
            $output->write('Image not found in <info>'.$orgSource.'</info> organization.');

            return 0;
        }

        try {
            $this->cloneImage($image, $orgSource, $orgDest, $stackName, $client, $output);
        } catch (\Exception $e) {
            $output->writeln('');
            $output->writeln($this->formatterHelper->formatBlock([
                'Error: Exception',
                $e->getMessage(),
            ], 'error', true));
        }

        $output->writeln('');

        return 0;
    }
}
