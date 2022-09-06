<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageRestoreCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:restore')
            ->setDescription('Restore the given image')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to restore the image from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organization = $input->getOption('organization');
        $hash = $input->getArgument('hash');

        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }
        $client = $this->clientProvider->getImageClient($organization);

        if (!$client->restoreSourceImage($hash, $organization)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error restoring the given image!',
            ], 'error', true));

            return -1;
        }

        $output->writeln('Image <info>'.$hash.'</info> restored on <comment>'.$organization.'</comment>.');

        return 0;
    }
}
