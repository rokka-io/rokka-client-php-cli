<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationListCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('organization:list')
            ->setDescription('List all the organizations for the current user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new CommandNotFoundException('Not Implemented');
    }
}
