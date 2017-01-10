<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageListCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:list')
            ->setDescription('List all images stored in an organization')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to list images from')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit the number of images', 20)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $input->getOption('organization');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($organization);
        $limit = $input->getOption('limit');
        $images = $client->listSourceImages($limit);

        $table = new Table($output);
        $table->setHeaders(array('Name', 'HASH', 'Organization', 'Created', 'Size'));

        /** @var SourceImage $image */
        foreach ($images->getSourceImages() as $key => $image) {
            $table->addRow([
                $image->name,
                $image->hash,
                $image->organization,
                $image->created->format('Y-m-d H:i:s'),
                $image->size,
            ]);
        }

        $table->render();

        return 0;
    }
}
