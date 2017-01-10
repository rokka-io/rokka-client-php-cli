<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\Operation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OperationListCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('operation:list')
            ->setDescription('List all available operations from Rokka')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageClient = $this->clientProvider->getImageClient();
        $operations = $imageClient->listOperations();

        $table = new Table($output);

        $headers = ['Command', 'Property', 'Type', 'Default', 'Other'];
        if ($output->isVerbose()) {
            $headers[] = 'Description';
        }
        $table->setHeaders($headers);

        /** @var Operation $operation */
        foreach ($operations->getOperations() as $i => $operation) {
            $table->addRow(['<comment>'.$operation->getName().'</comment>']);

            foreach ($operation->getProperties() as $name => $property) {
                $data = [
                    null,
                    $name.(in_array($name, $operation->getRequired()) ? '*' : ''),
                    $property['type'],
                    isset($property['default']) ? $property['default'] : '',
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
    }

    /**
     * Build a string for the "Other" column for the given property.
     *
     * @param $property
     *
     * @return string
     */
    protected function getPropertySettings($property)
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
