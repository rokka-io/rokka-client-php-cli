<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageInfoCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:info')
            ->setDescription('Get the associated information of a the given image stored on Rokka')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the images from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationName = $input->getOption('organization');
        $hash = $input->getArgument('hash');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        // Getting the client here, and reuse it later.
        $client = $this->clientProvider->getImageClient($organizationName);
        if (!$this->verifySourceImageExists($hash, $organizationName, $output, $client)) {
            return -1;
        }

        $sourceImage = $client->getSourceImage($hash, $organizationName);
        $this->formatterHelper->outputImageInfo($sourceImage, $output);

        return 0;
    }
}
