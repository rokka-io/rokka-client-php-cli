<?php

namespace RokkaCli\Command\DynamicMetadata;

use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use RokkaCli\Command\BaseRokkaCliCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageDynamicMetadataSetSubjectAreaCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:set-subjectarea')
            ->setDescription('Set the SubjectArea for the given image')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addArgument('area-x', InputArgument::REQUIRED, 'The SubjectArea start point (X pos)')
            ->addArgument('area-y', InputArgument::REQUIRED, 'The SubjectArea start point (Y pos)')
            ->addArgument('area-width', InputArgument::OPTIONAL, 'The SubjectArea height', 1)
            ->addArgument('area-height', InputArgument::OPTIONAL, 'The SubjectArea width', 1)
            ->addOption('organization-name', null, InputOption::VALUE_REQUIRED, 'The organization to retrieve the images from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationName = $input->getOption('organization-name');
        $hash = $input->getArgument('hash');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($organizationName);
        if (!$this->verifySourceImageExists($hash, $organizationName, $output, $client)) {
            return -1;
        }

        $subjectArea = $this->buildSubjectArea($input);

        if (!$subjectArea) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error building the SubjectArea metadata, please check the provided values',
            ], 'error', true));
        }

        $newHash = $client->setDynamicMetadata($subjectArea, $hash);

        $output->writeln('Image DynamicMetadata saved: added/updated SubjectArea.');

        if ($hash !== $newHash) {
            $output->writeln('New image Hash: <info>'.$newHash.'</info>');
        }

        return 0;
    }

    /**
     * @return bool|SubjectArea
     */
    protected function buildSubjectArea(InputInterface $input)
    {
        $x = $input->getArgument('area-x');
        $y = $input->getArgument('area-y');
        $width = $input->getArgument('area-width');
        $height = $input->getArgument('area-height');

        return new SubjectArea($x, $y, $width, $height);
    }
}
