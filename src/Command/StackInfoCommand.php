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
        $organization = $input->getOption('organization');
        $stackName = $input->getArgument('stack-name');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($organization);

        if (!$this->verifyStackExists($stackName, $organization, $output, $client)) {
            return -1;
        }

        $stack = $client->getStack($stackName, $organization);

        $this->outputStackInfo($stack, $output);

        return 0;
    }
}
