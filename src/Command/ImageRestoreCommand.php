<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImageRestoreCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:restore')
            ->setDescription('Restore the given image from Rokka')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the images from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        $output->writeln('Image <info>'.$hash.'</info> restored from <comment>'.$organization.'</comment>.');

        return 0;
    }
}
