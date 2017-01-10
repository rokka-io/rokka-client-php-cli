<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImageDeleteCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:delete')
            ->setDescription('Remove the given image from Rokka')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the images from')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Confirm the deletion of the image')
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

        $client = $this->getImageClient($organization);
        if (!$this->verifySourceImageExists($hash, $organization, $output, $client)) {
            return -1;
        }

        $confirm = $input->getOption('yes');
        if (!$confirm) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Continue with removing <info>'.$hash.'</info> from <comment>'.$organization.'</comment>? [y/n] ', false);
            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        if (!$client->deleteSourceImage($hash, $organization)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error removing the given image!',
            ], 'error', true));

            return -1;
        }

        $output->writeln('Image <info>'.$hash.'</info> removed from <comment>'.$organization.'</comment>.');

        return 0;
    }
}
