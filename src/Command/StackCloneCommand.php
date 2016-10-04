<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\Stack;
use RokkaCli\RokkaLibrary;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StackCloneCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('stack:clone')
            ->setDescription('Clones a Stack')
            ->addArgument('stack-name', InputArgument::REQUIRED, 'The Stack to be cloned')
            ->addArgument('dest-stack-name', InputArgument::REQUIRED, 'Clone the given the given stack with a new name')

            ->addOption('dest-organization', null, InputOption::VALUE_REQUIRED, 'The destination organization to copy the Stack to (default: current organization)')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing stack on destination organization')
            ->addOption('source-organization', null, InputOption::VALUE_REQUIRED, 'The source organization to copy Stack from (default: current organization)', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->displayWarningOverridenAPI($output);

        $sourceOrganization = $this->configuration->getOrganizationName($input->getOption('source-organization'));
        if (!$this->verifyOrganizationName($sourceOrganization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($sourceOrganization, $output)) {
            return -1;
        }

        $destOrganization = $this->configuration->getOrganizationName($input->getOption('dest-organization'));

        if ($sourceOrganization !== $destOrganization && !$this->verifyOrganizationName($destOrganization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($destOrganization, $output)) {
            return -1;
        }

        $stackName = $input->getArgument('stack-name');
        $destStackName = $input->getArgument('dest-stack-name');
        $overwrite = $input->getOption('overwrite');

        $skipped = $cloned = $errors = 0;
        $client = $this->getImageClient($sourceOrganization);

        if (!$this->verifyStackExists($stackName, $sourceOrganization, $output)) {
            return -1;
        }
        $stack = $client->getStack($stackName);

        try {
            $output->write('Cloning stack: <info>'.$stack->getName().'</info> to ');
            if ($sourceOrganization !== $destOrganization) {
                $output->write('<comment>'.$destOrganization.'</comment>/');
            }
            $output->write('<info>'.$destStackName.'</info>');
            $output->write(' ... ');

            if ($this->cloneStack($stack, $destOrganization, $destStackName, $overwrite)) {
                $output->writeln('<info>done</info>');
            } else {
                $output->writeln('<error>Error</error>');
            }
        } catch (\LogicException $ex) {
            $output->writeln('<comment>Skipped</comment> (stack already exists)');
            ++$skipped;
        } catch (\ErrorException $ex) {
            $output->writeln('<error>'.$ex->getMessage().'</error>');
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

    /**
     * @param Stack  $stack
     * @param string $destOrganization
     * @param string $destStackName
     * @param bool   $overwrite
     *
     * @return bool
     *
     * @throws \ErrorException|\LogicException
     */
    protected function cloneStack(Stack $stack, $destOrganization, $destStackName = null, $overwrite = false)
    {
        $destStackName = $destStackName ? $destStackName : $stack->getName();

        if (RokkaLibrary::stackExists($this->getImageClient(), $destStackName, $destOrganization)) {
            if ($overwrite) {
                if (!$this->getImageClient()->deleteStack($destStackName, $destOrganization)) {
                    throw new \ErrorException('Stack can not be removed from "'.$destOrganization.'" organization.');
                }
            } else {
                throw new \LogicException('Stack already exists on "'.$destOrganization.'" organization.');
            }
        }

        $operations = $stack->getStackOperations();
        $ret = $this->getImageClient()->createStack(
            $destStackName ? $destStackName : $stack->getName(),
            $operations,
            $destOrganization
        );

        return $ret instanceof Stack;
    }
}
