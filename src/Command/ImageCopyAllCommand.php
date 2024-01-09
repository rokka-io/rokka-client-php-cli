<?php

namespace RokkaCli\Command;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCopyAllCommand extends ImageCopyCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:copy-all')
            ->setDescription('copy all the available Images from the source organization')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
        ;
    }

    /**
     * @throws ClientException
     * @throws GuzzleException
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $limit = 100;
        $images = $client->searchSourceImages([], [], $limit);
        $output->writeln('Reading images to be cloned from <info>'.$orgSource.'</info> to <info>'.$orgDest.'</info>');

        while ($images->count() > 0) {
            /** @var SourceImage $image */
            $hashes = [];
            foreach ($images->getSourceImages() as $image) {
                $hashes[] = $image->hash;
            }

            try {
                $output->writeln('Copying '.\count($hashes).' images from <comment>'.$image->organization.'</comment> to <comment>'.$orgDest.'</comment>');

                $hashes = $this->copyImages($orgDest, $orgSource, $hashes, $client);
                $total = \count($hashes['existing']) + \count($hashes['created']);
                $output->writeln($total.' images copied from <comment>'.$image->organization.'</comment> to <comment>'.$orgDest.'</comment> ('.
                    \count($hashes['existing']).' existing, '.\count($hashes['created']).' newly created).'
                );

                $clonedImages += $total;
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
            $images = $client->searchSourceImages([], [], $limit, $images->getCursor());
        }

        // Avoid further processing if no images have been loaded.
        if (0 == $clonedImages) {
            $output->write('No Image found in <info>'.$orgSource.'</info> organization.');

            return 0;
        }

        $output->writeln('');
        $output->writeln('Cloned images: <info>'.$clonedImages.'</info>');

        return 0;
    }

    /**
     * @param string[] $hashes
     *
     * @throws GuzzleException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function copyImages(string $destOrg, string $sourceOrg, array $hashes, Image $client): array
    {
        $result = $client->copySourceImages($hashes, $destOrg, true, $sourceOrg);
        if (0 === \count($result['existing']) && 0 === \count($result['created'])) {
            throw new \Exception('Some or all images not found on organization '.$sourceOrg.' !');
        }

        return $result;
    }
}
