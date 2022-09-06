<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationMembershipAddCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('organization:membership:add')
            ->setDescription('Add a new membership to an organization')
            ->addArgument('user_id', InputArgument::REQUIRED, 'The user id')
            ->addArgument('roles', InputArgument::REQUIRED, 'The roles for this membership (read, write, upload, admin). Comma seperated')

            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to add the membership to (default: current organization)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organizationName = $input->getOption('organization');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        $roles = explode(',', $input->getArgument('roles'));
        $user_id = $input->getArgument('user_id');

        $client = $this->clientProvider->getUserClient();

        $membership = $client->createMembership($user_id, $roles, $organizationName);

        $output->writeln('Membership');
        $this->formatterHelper->outputOrganizationMembershipInfo($membership, $output);

        return 0;
    }
}
