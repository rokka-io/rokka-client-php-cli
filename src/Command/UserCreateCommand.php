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

class UserCreateCommand extends BaseRokkaCliCommand
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
            ->setName('user:create')
            ->setDescription('Create a new user on Rokka')
            ->addArgument('email', InputArgument::REQUIRED, 'User eMail')
        ;

        if ($this->configuration instanceof EditableConfiguration) {
            $this->addOption(
                'save-as-default',
                null,
                InputOption::VALUE_NONE,
                'Save the user created in the local rokka.yml setting file (overwrite)'
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $user = $this->clientProvider->getUserClient()->createUser($email);

        $output->writeln('User successfully created:');
        $this->formatterHelper->outputUserInfo($user, $output);

        $save = $input->hasOption('save-as-default') && $input->getOption('save-as-default');
        if ($save) {
            $conf = new Configuration(
                $this->configuration->getApiUri(),
                $user->apiKey,
                $user->apiSecret,
                $this->configuration->getOrganizationName()
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
