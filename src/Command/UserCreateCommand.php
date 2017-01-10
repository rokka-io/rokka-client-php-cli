<?php

namespace RokkaCli\Command;

use RokkaCli\Configuration;
use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreateCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a new user on Rokka')
            ->addArgument('email', InputArgument::REQUIRED, 'User eMail')
            ->addOption('save-as-default', null, InputOption::VALUE_NONE, 'Save the registered user in the local .rokka.yml setting file (overwrite)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $user = $this->clientProvider->getUserClient()->createUser($email);

        $output->writeln('User successfully created:');
        $output->writeln('  ID: <info>'.$user->getId().'</info>');
        $output->writeln('  eMail: <info>'.$user->getEmail().'</info>');
        $output->writeln('  API-Key: <info>'.$user->getApiKey().'</info>');
        $output->writeln('  API-Secret: <info>'.$user->getApiSecret().'</info>');

        $save = $input->getOption('save-as-default');
        if ($save) {
            $configFile = getcwd().DIRECTORY_SEPARATOR.'rokka.yml';

            $conf = new Configuration(
                $this->configuration->getApiUri(),
                $user->apiKey,
                $user->apiSecret,
                $this->configuration->getOrganizationName()
            );

            $ret = $this->updateConfigToFile($configFile, $conf);
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
