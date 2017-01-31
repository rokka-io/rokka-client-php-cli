<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\StackOperation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;


class StackCreateCommand extends BaseRokkaCliCommand
{
    protected $collectedData = null;

    protected function configure()
    {
        $this->setName('stack:create');
        $this->setDescription('Create a new Stack');
        $this->addArgument('stack-name', InputArgument::REQUIRED, 'The name of the stack to create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stackName = $input->getArgument('stack-name');
        $imageClient = $this->clientProvider->getImageClient();
        if ($this->rokkaHelper->stackExists($imageClient, $stackName)) {
            $output->writeln($this->formatterHelper->formatBlock([
                'Error!',
                'Error creating new stack "'.$stackName.'": Stack already exists',
            ], 'error', true));
            return -1;
        }

        $this->collectedData['name'] = $stackName;
        $this->collectedData['operations'] = [];
        $this->displayResume($output);

        $moreOperation = new ConfirmationQuestion("\nDo you add one more operation? (y/n) ");
        while(count($this->collectedData['operations']) == 0 || $this->getHelper('question')->ask($input, $output, $moreOperation)) {
            $output->write('', true);
            $this->askForOperation($input, $output);
            $this->displayResume($output);
        }

        $this->displayResume($output);
        $confirm = new ConfirmationQuestion( "\nDo you really want to create the stack? (y/n) ");
        if ($this->getHelper('question')->ask($input, $output, $confirm)) {
            $stack = $imageClient->createStack($this->collectedData['name'], $this->collectedData['operations']);
            if ($stack) {
                $output->writeln('Stack <info>'.$stack->getName().'</info> Created');
            }

        }
    }

    /**
     * @param $input  InputInterface
     * @param $output OutputInterface
     */
    private function askForOperation(InputInterface $input, OutputInterface $output)
    {
        static $operations;
        if (!$operations) {
            $imageClient = $this->clientProvider->getImageClient();
            $operations = [];
            foreach($imageClient->listOperations()->getOperations() as $op){
                $operations[$op->name] = $op;
            };
        }

        $question = new ChoiceQuestion('Please select an operation:', array_keys($operations));
        $question->setErrorMessage('Operation [%s] is invalid.');
        $operationName = $this->getHelper('question')->ask($input, $output, $question);
        $operation = $operations[$operationName];

        $options = [];
        foreach ($operation->getProperties() as $optionName => $property) {
            if (!in_array($optionName, $operation->getRequired())) {
                if (!$this->getHelper('question')->ask($input, $output, new ConfirmationQuestion("\nDo you want to use the option [$optionName]? (y/n) "))) {
                    continue;
                }
            }
            $options[$optionName] = $this->askForOption($optionName, $property['type'], $input, $output);
        }
        $this->collectedData['operations'][$operationName] = new StackOperation($operationName, $options);
    }

    /**
     * @param $propertyName
     * @param $propertyType
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    private function askForOption($propertyName, $propertyType, InputInterface $input, OutputInterface $output) {
        $question = new Question("\nChoose $propertyName, type [$propertyType]: ");
        $data = null;
        while(true){
            $data = $this->getHelper('question')->ask($input, $output, $question);
            if ($propertyType === 'integer' || $propertyType == 'number') {
                if ($data !== '0' && (int)$data === 0) {
                    $output->write("<error>Invalid $propertyType value [$data]</error>");
                    continue;
                }
                $data = (int) $data;
            }
            if ($propertyType == 'bool' || $propertyType == 'boolean') {
                if ($data !== 'false' && $data !== 'true' && $data !== '1' && $data !== '0') {
                    $output->write("<error>Boolean expected choose 0 or 1</error>");
                    continue;
                }
                $data = (bool) $data;
            }
            break;
        }
        return $data;
    }

    /**
     * Display a summary of the stack that will be created.
     *
     * @param $output OutputInterface
     */
    protected function displayResume(OutputInterface $output){
        $output->write("Creation of a new stack [<info>{$this->collectedData['name']}</info>]", true);
        foreach($this->collectedData['operations'] as $name => $operation) {
            $output->write(" * Operation [<info>$name</info>] with ".json_encode($operation->options), true);
        }
    }

}
