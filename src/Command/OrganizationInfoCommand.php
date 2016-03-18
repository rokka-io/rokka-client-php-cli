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
            ->setName('organization:info')
            ->setDescription('Get the associated information of a the given Organization')
            ->addArgument('organization', InputArgument::OPTIONAL, 'The organization to retrieve the details from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);
        $organization = $this->configuration->getOrganizationName($input->getArgument('organization'));

        if (!$this->verifyOrganizationName($organization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($organization, $output)) {
            return -1;
        }

        $organization = $this->getUserClient()->getOrganization($organization);

        self::outputOrganizationInfo($organization, $output);

        return 0;
    }
}
