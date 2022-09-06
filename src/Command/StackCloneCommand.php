<?php

namespace RokkaCli\Command;

use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Core\Stack;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StackCloneCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceOrganization = $input->getOption('source-organization');
        if (!$sourceOrganization = $this->resolveOrganizationName($sourceOrganization, $output)) {
            return -1;
        }

        $destOrganization = $input->getOption('dest-organization');
        if (!$destOrganization = $this->resolveOrganizationName($destOrganization, $output)) {
            return -1;
        }

        $stackName = $input->getArgument('stack-name');
        $destStackName = $input->getArgument('dest-stack-name');
        $overwrite = $input->getOption('overwrite');

        $skipped = $cloned = $errors = 0;
        $client = $this->clientProvider->getImageClient($sourceOrganization);

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
     * @throws \ErrorException If we fail to overwrite the stack
     * @throws \LogicException If the target stack already exists and we did not set to overwrite
     * @throws GuzzleException
     */
    protected function cloneStack(Stack $stack, string $destOrganization, string $destStackName = null, $overwrite = false): bool
    {
        $destImageClient = $this->clientProvider->getImageClient($destOrganization);
        $destStackName = $destStackName ?: $stack->getName();

        if ($this->rokkaHelper->stackExists($destImageClient, $destStackName, $destOrganization)) {
            if (!$overwrite) {
                throw new \LogicException('Stack already exists on "'.$destOrganization.'" organization.');
            }
            if (!$destImageClient->deleteStack($destStackName, $destOrganization)) {
                throw new \ErrorException('Stack can not be removed from "'.$destOrganization.'" organization.');
            }
        }

        $stack->setName($destStackName);
        $stack->setOrganization($destOrganization);
        $ret = $destImageClient->saveStack($stack, ['overwrite' => $overwrite]);

        return $ret instanceof Stack;
    }
}
