<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCopyAllCommand extends ImageCopyCommand
{
    protected function configure()
    {
        $this
            ->setName('image:copy-all')
            ->setDescription('copy all the available Images from the source organization')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
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
                'The organizations to copy images between must not be the same: "'.$orgSource.'"!',
            ], 'error', true));

            return -1;
        }

        $stopOnError = false;
        $clonedImages = 0;

        $client = $this->clientProvider->getImageClient($orgSource);

        $limit = 20;
        $images = $client->searchSourceImages([], [], $limit);
        $output->writeln('Reading images to be cloned from <info>'.$orgSource.'</info> to <info>'.$orgDest.'</info>');

        while ($images->count() > 0) {
            /** @var SourceImage $image */
            foreach ($images->getSourceImages() as $image) {
                try {
                    $this->copyImage($orgDest, $orgSource, $image->hash, $output, $client);
                    $output->writeln('Image  <info>'.$image->name.'</info> ('.$image->hash.') copied from <comment>'.$image->organization.'</comment> to <comment>'.$orgDest.'</comment>.');

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

            $images = $client->searchSourceImages([], [], $limit, $images->getCursor());
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
