<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationListCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('organization:list')
            ->setDescription('List all organizations (that you are allowed to see)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new CommandNotFoundException('Not Implemented');
    }
}
