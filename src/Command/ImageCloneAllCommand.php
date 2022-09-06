<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCloneAllCommand extends ImageCloneCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:clone-all')
            ->setDescription('Deprecated! Use image:copy-all instead.')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
            ->addOption('stack-name', null, InputOption::VALUE_REQUIRED, 'ImageStack to use to download source images', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->formatterHelper->formatBlock([
            'Deprecated!',
            'This command is deprecated. Please use image:copy-all instead.',
        ], 'comment', true));

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

        $stackName = $input->getOption('stack-name');
        $stopOnError = false;
        $clonedImages = 0;

        // Checking if the stack on source organization exists
        if ($stackName && !$this->verifyStackExists($stackName, $orgSource, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($orgSource);

        $limit = 20;
        $images = $client->listSourceImages($limit);
        $output->write('Reading images to be cloned from <info>'.$orgSource.'</info> to <info>'.$orgDest.'</info>');

        while ($images->count() > 0) {
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
        if (0 === $clonedImages) {
            $output->write('No Image found in <info>'.$orgSource.'</info> organization.');

            return 0;
        }

        $output->writeln('');
        $output->writeln('Cloned images: <info>'.$clonedImages.'</info>');

        return 0;
    }
}
