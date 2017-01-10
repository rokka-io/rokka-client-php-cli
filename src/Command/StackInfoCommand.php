<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StackInfoCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('stack:info')
            ->setDescription('Delete the given Stack from an Organization.')
            ->addArgument('stack-name', InputArgument::REQUIRED, 'The Stack name to load')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to load the Stacks from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $this->configuration->getOrganizationName($input->getOption('organization'));
        $stackName = $input->getArgument('stack-name');

        if (!$this->verifyOrganizationName($organization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($organization, $output)) {
            return -1;
        }

        $client = $this->getImageClient($organization);

        if (!$this->verifyStackExists($stackName, $organization, $output, $client)) {
            return -1;
        }

        $stack = $client->getStack($stackName, $organization);

        $this->outputStackInfo($stack, $output);

        return 0;
    }
}
