<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationInfoCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName($this->namePrefix.'organization:info')
            ->setDescription('Get the associated information of a the given Organization')
            ->addArgument(
                'organization-name',
                InputArgument::OPTIONAL,
                'The organization to retrieve the details from; if none the organization from your configuration will be used'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organizationName = $input->getArgument('organization-name');
        if (!$organizationName = $this->resolveOrganizationName($organizationName, $output)) {
            return -1;
        }

        $organization = $this->clientProvider->getUserClient()->getOrganization($organizationName);

        $this->formatterHelper->outputOrganizationInfo($organization, $output);

        return 0;
    }
}
