<?php

namespace RokkaCli\Command;

use RokkaCli\Configuration;
use RokkaCli\EditableConfiguration;
use RokkaCli\Provider\ClientProvider;
use RokkaCli\RokkaApiHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationCreateCommand extends BaseRokkaCliCommand
{
    /**
     * @var EditableConfiguration|Configuration
     */
    private $configuration;

    public function __construct(ClientProvider $clientProvider, RokkaApiHelper $rokkaHelper, Configuration $configuration, $namePrefix = '')
    {
        // Store the configuration before the parent's constructor, as the `->configure()` method is invoked there.
        $this->configuration = $configuration;

        parent::__construct($clientProvider, $rokkaHelper, $namePrefix);
    }

    protected function configure()
    {
        $this
            ->setName('organization:create')
            ->setDescription('Create a new organization')
            ->addArgument('organization-name', InputArgument::REQUIRED, 'The organization name')
            ->addArgument('email', InputArgument::REQUIRED, 'The organization billing email')
            ->addOption('display-name', null, InputOption::VALUE_REQUIRED, 'Specify the display name for the organization', '')
        ;
        if ($this->configuration instanceof EditableConfiguration) {
            $this->addOption(
                'save-as-default',
                null,
                InputOption::VALUE_NONE,
                'Save the registered organization in the local "rokka.yml" setting file (overwrite)'
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organizationName = $input->getArgument('organization-name');
        $email = $input->getArgument('email');
        $displayName = $input->getOption('display-name');

        $client = $this->clientProvider->getUserClient();

        if ($this->rokkaHelper->organizationExists($client, $organizationName)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The "'.$organizationName.'" organization already exists!',
            ], 'error', true));

            return -1;
        }
        $org = $client->createOrganization($organizationName, $email, $displayName);
        $output->writeln('Organization created');

        if ($org && $org->getName() == $organizationName) {
            $this->formatterHelper->outputOrganizationInfo($org, $output);
        }

        $save = $input->hasOption('save-as-default') && $input->getOption('save-as-default');
        if ($save) {
            $conf = new Configuration(
                $this->configuration->getApiUri(),
                $this->configuration->getApiKey(),
                $org->getName()
            );
            $configFile = $this->configuration->getConfigFileName();
            $ret = $this->configuration->updateConfigToFile($configFile, $conf);
            if (false === $ret) {
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
