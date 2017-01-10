<?php

namespace RokkaCli\Command\DynamicMetadata;

use RokkaCli\Command\BaseRokkaCliCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageDynamicMetadataDeleteSubjectAreaCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:delete-subjectarea')
            ->setDescription('Delete the SubjectArea for the given image')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the images from')
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
        if (!$this->verifySourceImageExists($hash, $organization, $output, $client)) {
            return -1;
        }

        $newHash = $client->deleteDynamicMetadata('SubjectArea', $hash);

        $output->writeln('Image DynamicMetadata saved.');

        if ($hash !== $newHash) {
            $output->writeln('New image Hash: <info>'.$newHash.'</info>');
        }

        return 0;
    }
}
