<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OperationListCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('operation:list')
            ->setDescription('List all available operations from Rokka')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imageClient = $this->clientProvider->getImageClient();
        $operations = $imageClient->listOperations();

        $table = new Table($output);

        $headers = ['Command', 'Property', 'Type', 'Default', 'Other'];
        if ($output->isVerbose()) {
            $headers[] = 'Description';
        }
        $table->setHeaders($headers);

        foreach ($operations->getOperations() as $i => $operation) {
            $table->addRow(['<comment>'.$operation->getName().'</comment>']);

            foreach ($operation->getProperties() as $name => $property) {
                $data = [
                    null,
                    $name.(\in_array($name, $operation->getRequired(), true) ? '*' : ''),
                    $property['type'],
                    $property['default'] ?? '',
                    $this->getPropertySettings($property),
                ];
                if ($output->isVerbose() && !empty($property['description'])) {
                    $data[] = $property['description'];
                }

                $table->addRow($data);
            }

            if ($i < $operations->count() - 1) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();

        return 0;
    }

    /**
     * Build a string for the "Other" column for the given property.
     */
    private function getPropertySettings($property): string
    {
        $data = [];
        $settings = [
            'minimum' => 'min',
            'maximum' => 'max',
        ];
        foreach ($settings as $setting => $name) {
            if (isset($property[$setting])) {
                $data[] = $name.':'.$property[$setting];
            }
        }

        return implode(' | ', $data);
    }
}
