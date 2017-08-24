<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageDownloadCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName($this->namePrefix.'image:download')
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
        $hash = $input->getArgument('hash');
        $saveTo = $input->getOption('save-to');
        $overwrite = $input->getOption('overwrite');

        $organization = $input->getOption('organization');
        $pipe = $input->getOption('pipe');
        $saveAsHash = $input->getOption('save-as-hash');

        if ($pipe) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $saveTo = 'php://stdout';
        }

        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($organization);

        if (!$this->verifySourceImageExists($hash, $organization, $output, $client)) {
            return -1;
        }

        $image = $client->getSourceImage($hash, $organization);

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

    /**
     * Downloads and saves an image from Rokka.
     *
     * @param Image           $client    The Image client to use
     * @param SourceImage     $image     The image to download
     * @param string          $saveTo    The destination stream to save the image to
     * @param OutputInterface $output    The output interface to display messages
     * @param null            $stackName The stack name to use, leave empty to use the source image on Rokka
     * @param string          $format    The file format to retrieve the image if using a Stack
     *
     * @return bool the status of the operation, True if the image has been saved correctly, false otherwise
     */
    private function saveImageContents(Image $client, SourceImage $image, $saveTo, OutputInterface $output, $stackName = null, $format = 'jpg')
    {
        if (!$stackName) {
            $output->writeln('Getting source image contents for <info>'.$image->hash.'</info> from <comment>'.$image->organization.'</comment>');
        } else {
            $output->writeln('Rendering image  <info>'.$image->hash.'</info> from <comment>'.$image->organization.'</comment> on stack <info>'.$stackName.'</info>');
        }

        $contents = $this->rokkaHelper->getSourceImageContents($client, $image->hash, $image->organization, $stackName, $format);
        if (!$contents) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error getting image contents from Rokka.io!',
            ], 'error', true));

            return false;
        }

        // TODO: verify if target file exists, ask for permission to overwrite unless --force
        $ret = file_put_contents($saveTo, $contents, FILE_BINARY);
        if (false == $ret) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error writing image contents to <info>'.$saveTo.'</info>!',
            ], 'error', true));

            return false;
        }

        $output->writeln('Image saved to <info>'.$saveTo.'</info>');

        return true;
    }
}
