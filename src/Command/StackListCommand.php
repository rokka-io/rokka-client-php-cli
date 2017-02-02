<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StackListCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('stack:list')
            ->setDescription('List all available Stacks from Rokka')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to list Stacks from')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit the number of Stacks to retrieve', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $input->getOption('organization');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $limit = $input->getOption('limit');

        $imageClient = $this->clientProvider->getImageClient();
        $stacks = $imageClient->listStacks($limit, null, $organization);
        $table = new Table($output);

        $headers = ['Stack', 'Created', 'Operations', 'Settings'];
        $table->setHeaders($headers);

        foreach ($stacks->getStacks() as $i => $stack) {
            $table->addRow([
                '<comment>'.$stack->getName().'</comment>',
                $stack->created->format('Y-m-d H:i:s'),
            ]);

            foreach ($stack->getStackOperations() as $operation) {
                $data = [
                    null, null,
                    $operation->name,
                    $this->formatterHelper->formatStackOperationOptions($operation->options),
                ];

                $table->addRow($data);
            }

            if ($i < $stacks->count() - 1) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();

        return 0;
    }
}
