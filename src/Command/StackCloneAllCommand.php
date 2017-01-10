<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StackCloneAllCommand extends StackCloneCommand
{
    protected function configure()
    {
        $this
            ->setName('stack:clone-all')
            ->setDescription('Clone available Stacks between organizations')

            ->addArgument('dest-organization', InputArgument::REQUIRED, 'The destination organization to copy Stacks to')

            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy Stack from', null)
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing stack on destination organization')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceOrganization = $input->getOption('source-organization');
        if (!$sourceOrganization = $this->resolveOrganizationName($sourceOrganization, $output)) {
            return -1;
        }

        $destOrganization = $input->getArgument('dest-organization');
        if (!$destOrganization = $this->resolveOrganizationName($destOrganization, $output)) {
            return -1;
        }

        if ($sourceOrganization == $destOrganization) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'The organizations to clone all stacks must not be the same: "'.$sourceOrganization.'"!',
            ], 'error', true));

            return -1;
        }

        $overwrite = $input->getOption('overwrite');

        $client = $this->clientProvider->getImageClient($sourceOrganization);
        $skipped = $cloned = $errors = 0;

        $list = $client->listStacks();
        $stacks = $list->getStacks();

        // Avoid further processing if no stacks have been loaded.
        if (empty($stacks)) {
            $output->write('No Stacks found in <info>'.$sourceOrganization.'</info> organization.');

            return 0;
        }

        foreach ($stacks as $stack) {
            try {
                $output->write('Cloning stack: <info>'.$stack->getName().'</info> to <info>'.$destOrganization.'</info> ... ');

                if ($this->cloneStack($stack, $destOrganization, null, $overwrite)) {
                    $output->writeln('<info>done</info>');
                    ++$cloned;
                } else {
                    $output->writeln('<error>Error</error>');
                }
            } catch (\LogicException $ex) {
                $output->writeln('<comment>Skipped</comment> (stack already exists)');
                ++$skipped;
            } catch (\ErrorException $ex) {
                $output->writeln('<error>Error</error>');
                $output->writeln('<error>'.$ex->getMessage().'</error>');
            }
        }

        if ($output->isVerbose()) {
            $output->writeln('Cloned Stacks: '.$cloned);
            if ($skipped > 0) {
                $output->writeln('Skipped Stacks: '.$skipped.', use <comment>--override</comment> to force stack overwriting.');
            }
        }

        $output->writeln('');

        return 0;
    }
}
