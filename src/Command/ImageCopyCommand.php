<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageCopyCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:copy')
            ->setDescription('Copy the given image to another organization')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy images to')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy images from', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $sourceOrg = $input->getOption('source-organization');
        $destOrg = $input->getArgument('dest-organization');
        $hash = $input->getArgument('hash');

        if (!$sourceOrg = $this->resolveOrganizationName($sourceOrg, $output)) {
            return -1;
        }

        if (!$destOrg = $this->resolveOrganizationName($destOrg, $output)) {
            return -1;
        }

        if ($sourceOrg === $destOrg) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The organizations to copy images between must not be the same: "'.$sourceOrg.'"!',
            ], 'error', true));

            return -1;
        }

        $client = $this->clientProvider->getImageClient($sourceOrg);

        try {
            $this->copyImage($destOrg, $sourceOrg, $hash, $output, $client);
            $output->writeln('Image <info>' . $hash . '</info> copied from <comment>' . $sourceOrg . '</comment> to <comment>' . $destOrg . '</comment>.');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('');
            $output->writeln($this->formatterHelper->formatBlock([
                'Error: Exception',
                $e->getMessage(),
            ], 'error', true));
            return -1;
        }
    }

    protected function copyImage($destOrg, $sourceOrg, $hash, OutputInterface $output, $client)
    {
        if (!$client->copySourceImage($hash, $destOrg, $sourceOrg)) {
            throw new \Exception('Image with hash '.$hash. ' not found on organization '.$sourceOrg .' !');
        }

    }
}
