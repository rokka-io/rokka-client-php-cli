<?php

namespace RokkaCli\Command;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationMembershipInfoCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('organization:membership:info')
            ->setDescription('Get membership data from a user and organization')
            ->addArgument('user_id', InputArgument::REQUIRED, 'The user id')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to add the membership to (default: current organization)')
        ;
    }

    /**
     * @throws \RuntimeException
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationName = $input->getOption('organization');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        $userid = $input->getArgument('user_id');

        $client = $this->clientProvider->getUserClient();
        $membership = $client->getMembership($userid, $organizationName);

        $output->writeln('Membership');
        $this->formatterHelper->outputOrganizationMembershipInfo($membership, $output);

        return 0;
    }
}
