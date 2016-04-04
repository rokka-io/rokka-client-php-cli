<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageRenderCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:render')
            ->setDescription('Render and download a given image from Rokka.')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash to render.')
            ->addArgument('stack-name', InputArgument::REQUIRED, 'The Stack to to use to get the image.')
            ->addArgument('save-to', InputArgument::OPTIONAL, 'Filename where to save the image, default to `hash.format`, use --pipe to output its contents.')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the image from.')
            ->addOption('pipe', null, InputOption::VALUE_NONE, 'Output the image instead of saving it to a file.')
            ->addOption('get-uri', null, InputOption::VALUE_NONE, 'Just return the URI for the given image.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Render the image in the given format.', 'jpg')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite the destination file, if exists.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);
        $hash = $input->getArgument('hash');
        $saveTo = $input->getArgument('save-to');

        $organization = $this->configuration->getOrganizationName($input->getOption('organization'));
        $stackName = $input->getArgument('stack-name');
        $pipe = $input->getOption('pipe');
        $format = $input->getOption('format');
        $overwrite = $input->getOption('overwrite');
        $getUri = $input->getOption('get-uri');

        if ($pipe) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $saveTo = 'php://stdout';
        }

        if (!$getUri) {
            if (empty($saveTo)) {
                $saveTo = $hash.'.'.$format;
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

        if (null !== $stackName && !$this->verifyStackExists($stackName, $organization, $output)) {
            return -1;
        }

        if (!$this->verifySourceImageExists($hash, $organization, $output, $client)) {
            return -1;
        }

        $url = $client->getSourceImageUri($hash, $stackName, $format, null, $organization);

        if ($getUri) {
            $output->writeln('Rendered Image URI: <info>'.$url->__toString().'</info>');
        } else {
            file_put_contents($saveTo, $url->__toString(), FILE_TEXT);
        }

        return 0;
    }
}
