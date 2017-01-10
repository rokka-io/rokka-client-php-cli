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
    protected function configure()
    {
        $this
            ->setName('image:set-subjectarea')
            ->setDescription('Get the SubjectArea for the given image')
            ->addArgument('hash', InputArgument::REQUIRED, 'The Source Image hash')
            ->addArgument('area-x', InputArgument::REQUIRED, 'The SubjectArea start point (X pos)')
            ->addArgument('area-y', InputArgument::REQUIRED, 'The SubjectArea start point (Y pos)')
            ->addArgument('area-width',  InputArgument::OPTIONAL, 'The SubjectArea height', 0)
            ->addArgument('area-height', InputArgument::OPTIONAL, 'The SubjectArea width', 0)
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

        $subjectArea = $this->buildSubjectArea($input);
        $newHash = $client->setDynamicMetadata($subjectArea, $hash);

        $output->writeln('Image DynamicMetadata saved.');

        if ($hash !== $newHash) {
            $output->writeln('New image Hash: <info>'.$newHash.'</info>');
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @return bool|SubjectArea
     */
    protected function buildSubjectArea(InputInterface $input)
    {
        $x = $y = $width = $height = null;

        if (($x = $input->getArgument('area-x')) && $x < 0) {
            return false;
        }
        if (($y = $input->getArgument('area-y')) && $y < 0) {
            return false;
        }
        if (($width = $input->getArgument('area-width')) && $width < 0) {
            return false;
        }
        if (($height = $input->getArgument('area-height')) && $height < 0) {
            return false;
        }

        return new SubjectArea($x, $y, $width, $height);
    }
}
