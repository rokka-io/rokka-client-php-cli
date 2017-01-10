<?php

namespace RokkaCli\Command;

use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageInfoCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:info')
            ->setDescription('Get the associated information of a the given image stored on Rokka')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the images from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $this->configuration->getOrganizationName($input->getOption('organization'));
        $hash = $input->getArgument('hash');

        if (!$this->verifyOrganizationName($organization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($organization, $output)) {
            return -1;
        }

        // Getting th client here, and reuse it later.
        $client = $this->getImageClient($organization);
        if (!$this->verifySourceImageExists($hash, $organization, $output, $client)) {
            return -1;
        }

        $sourceImage = RokkaLibrary::getSourceImage($client, $hash, $organization);
        self::outputImageInfo($sourceImage, $output);

        return 0;
    }
}
