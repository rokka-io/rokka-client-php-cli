<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationMembershipInfoCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('organization:membership:info')
            ->setDescription('Get membership data from a user and organization')
            ->addArgument('email', InputArgument::REQUIRED, 'The user email')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to add the membership to (default: current organization)')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organizationName = $input->getOption('organization');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        $email = $input->getArgument('email');

        $client = $this->clientProvider->getUserClient();
        $membership = $client->getMembership($organizationName, $email);

        $output->writeln('Membership');
        self::outputOrganizationMembershipInfo($membership, $output);

        return 0;
    }
}
