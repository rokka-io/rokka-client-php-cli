<?php

namespace RokkaCli\Command;

use RokkaCli\Configuration;
use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationCreateCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('organization:create')
            ->setDescription('Create a new organization')
            ->addArgument('name', InputArgument::REQUIRED, 'The organization name')
            ->addArgument('email', InputArgument::REQUIRED, 'The organization billing email')
            ->addOption('display-name', null, InputOption::VALUE_REQUIRED, 'Specify the display name for the organization', '')
            ->addOption('save-as-default', null, InputOption::VALUE_NONE, 'Save the registered organization in the local .rokka.yml setting file (overwrite)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);

        $name = $input->getArgument('name');
        $email = $input->getArgument('email');
        $displayName = $input->getOption('display-name');

        $client = $this->getUserClient();

        $org = RokkaLibrary::getOrganization($client, $name);

        if ($org) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The "'.$name.'" organization already exists!',
            ], 'error', true));

            return -1;
        } else {
            $org = $client->createOrganization($name, $email, $displayName);
            $output->writeln('Organization created');
        }

        if ($org && $org->getName() == $name) {
            self::outputOrganizationInfo($org, $output);
        }

        $save = $input->getOption('save-as-default');
        if ($save) {
            $configFile = getcwd().DIRECTORY_SEPARATOR.'rokka.yml';

            $conf = new Configuration(
                $this->configuration->getApiUri(),
                $this->configuration->getApiKey(),
                $this->configuration->getApiSecret(),
                $org->getName()
            );

            $ret = RokkaLibrary::updateConfigToFile($configFile, $conf);
            if ($ret === false) {
                $output->writeln($this->formatterHelper->formatBlock([
                    'Error!',
                    'Error saving new configuration to "'.$configFile."''",
                ], 'error', true));

                return -1;
            }
            $output->writeln('');
            $output->writeln('  Configuration written to <info>'.$configFile.'</info>');
        }

        $output->writeln('');

        return 0;
    }
}
