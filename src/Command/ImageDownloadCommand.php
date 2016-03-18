<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageDownloadCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:download')
            ->setDescription('Download the given image from Roka, saves it to the original filename.')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('save-to', null, InputOption::VALUE_REQUIRED, 'Filename where to store the Source Image, use --pipe to output its contents.')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the image from.')
            ->addOption('pipe', null, InputOption::VALUE_NONE, 'Output the image instead of saving it to a file.')
            ->addOption('save-as-hash', null, InputOption::VALUE_NONE, 'Use the hash as the output file name.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing destination images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);
        $hash = $input->getArgument('hash');
        $saveTo = $input->getOption('save-to');
        $overwrite = $input->getOption('overwrite');

        $organization = $this->configuration->getOrganizationName($input->getOption('organization'));
        $pipe = $input->getOption('pipe');
        $saveAsHash = $input->getOption('save-as-hash');

        if ($pipe) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $saveTo = 'php://stdout';
        }

        if (!$this->verifySourceImageHash($hash, $output)) {
            return -1;
        }

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

        $image = $client->getSourceImage($hash, false, $organization);

        // If the save-to filename is empty, compute the destination filename.
        if (empty($saveTo)) {
            if ($saveAsHash) {
                // Using the hash and the image format.
                $saveTo = $hash.'.'.$image->format;
            } else {
                // Using the original image filename.
                $saveTo = $image->name;
            }
        }
        if (file_exists($saveTo)) {
            if (!is_writable($saveTo)) {
                $output->writeln($this->formatterHelper->formatBlock([
                    'Error!',
                    'Write permission denied for destination: <info>'.$saveTo.'</info>!',
                ], 'error', true));

                return -1;
            }
            if (!$overwrite) {
                $output->writeln($this->formatterHelper->formatBlock([
                    'Error!',
                    'Destination file <info>'.$saveTo.'</info> exists. Use --overwrite option replace the existing file.',
                ], 'error', true));

                return -1;
            }
        }

        if (!$this->saveImageContents($client, $image, $saveTo, $output)) {
            return -1;
        }

        return 0;
    }
}
