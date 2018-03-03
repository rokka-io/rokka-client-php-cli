<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImageCopyCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:copy')
            ->setDescription('Copy the given image to another organization')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addArgument('destination', InputArgument::REQUIRED, 'The destination organization')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the images from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $input->getOption('organization');
        $hash = $input->getArgument('hash');
        $destination = $input->getArgument('destination');

        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }
        $client = $this->clientProvider->getImageClient($organization);

        if (!$client->copySourceImage($hash, $destination, $organization)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error copying the given image!',
            ], 'error', true));

            return -1;
        }

        $output->writeln('Image <info>'.$hash.'</info> copied from <comment>'.$organization.'</comment> to <comment>'. $destination .'</comment>.');

        return 0;
    }
}
