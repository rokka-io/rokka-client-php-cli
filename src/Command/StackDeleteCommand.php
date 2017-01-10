<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class StackDeleteCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('stack:delete')
            ->setDescription('Delete the given Stack from an Organization.')
            ->addArgument('stack-name', InputArgument::REQUIRED, 'The Stack name to delete')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the Stacks from')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Confirm the deletion of the stack')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $input->getOption('organization');
        $stackName = $input->getArgument('stack-name');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        if (!$this->verifyStackExists($stackName, $organization, $output)) {
            return -1;
        }

        $confirm = $input->getOption('yes');
        if (!$confirm) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Continue with removing <info>'.$stackName.'</info> from <info>'.$organization.'</info>? [y/n] ', false);
            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        $client = $this->clientProvider->getImageClient($organization);

        if (!$client->deleteStack($stackName, $organization)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error removing the given Stack!',
            ], 'error', true));

            return -1;
        }

        $output->writeln('Stack <info>'.$stackName.'</info> removed from <info>'.$organization.'</info>.');

        return 0;
    }
}
