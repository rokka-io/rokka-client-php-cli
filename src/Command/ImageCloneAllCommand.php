<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCloneAllCommand extends ImageCloneCommand
{
    protected function configure()
    {
        $this
            ->setName('image:clone-all')
            ->setDescription('Clone all the available Images from the source organization')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
            ->addOption('stack-name', null, InputOption::VALUE_REQUIRED, 'ImageStack to use to download source images', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);
        $orgSource = $this->configuration->getOrganizationName($input->getOption('source-organization'));

        if (!$this->verifyOrganizationName($orgSource, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($orgSource, $output)) {
            return -1;
        }

        $orgDest = $input->getArgument('dest-organization');
        if (!$this->verifyOrganizationName($orgDest, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($orgDest, $output)) {
            return -1;
        }

        $stackName = $input->getOption('stack-name');
        $stopOnError = false;
        $clonedImages = 0;

        // Checking if the stack on source organization exists
        if ($stackName && !$this->verifyStackExists($stackName, $orgSource, $output)) {
            return -1;
        }

        $client = $this->getImageClient($orgSource);

        $limit = 20;
        $images = $client->listSourceImages($limit);
        $output->write('Reading images to be cloned from <info>'.$orgSource.'</info> to <info>'.$orgDest.'</info>');

        while ($images->count() > 0) {
            /** @var SourceImage $image */
            foreach ($images->getSourceImages() as $image) {
                try {
                    $this->cloneImage($image, $orgSource, $orgDest, $stackName, $client, $output);
                    ++$clonedImages;
                } catch (\Exception $e) {
                    $output->writeln('');
                    $output->writeln($this->formatterHelper->formatBlock([
                        'Error: Exception',
                        $e->getMessage(),
                    ], 'error', true));
                    if ($stopOnError) {
                        return -1;
                    }
                }
            }

            $output->write('Reading images to be cloned from <info>'.$orgSource.'</info> to <info>'.$orgDest.'</info>');
            $images = $client->listSourceImages($limit);
        }

        // Avoid further processing if no stacks have been loaded.
        if (0 == $clonedImages) {
            $output->write('No Image found in <info>'.$orgSource.'</info> organization.');

            return 0;
        }

        $output->writeln('');
        $output->writeln('Cloned images: <info>'.$clonedImages.'</info>');

        return 0;
    }
}
