<?php

namespace RokkaCli\Command;

use Rokka\Client\Core\SourceImage;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageListCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:list')
            ->setDescription('List all images stored in an organization')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to list images from')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit the number of images', 20)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Offset of images to display', null)
            ->addOption('search', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Search option', [])
            ->addOption('sort', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Sorting option', [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organization = $input->getOption('organization');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $client = $this->clientProvider->getImageClient($organization);
        $limit = $input->getOption('limit');
        $offset = $input->getOption('offset');
        $search = $this->buildSearchParameter($input->getOption('search'));
        $sorts = $this->buildSortParameter($input->getOption('sort'));

        $images = $client->searchSourceImages($search, $sorts, $limit, $offset);

        $table = new Table($output);
        $table->setHeaders(['Name', 'HASH', 'Organization', 'Created', 'Size']);

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

    /**
     * Builds the "search" parameter as required bu the Rokka client.
     *
     * @param array $searchFilters
     *
     * @return array
     */
    protected function buildSearchParameter(array $searchFilters)
    {
        $search = [];
        foreach ($searchFilters as $filter) {
            $parts = explode(' ', trim($filter), 2);

            if (empty($parts) || 2 !== \count($parts)) {
                continue;
            }
            $search[$parts[0]] = $parts[1];
        }

        return $search;
    }

    /**
     * Builds the "sort" parameter as required bu the Rokka client.
     *
     * @param array $sorts
     *
     * @return array
     */
    protected function buildSortParameter(array $sorts)
    {
        $sorting = [];
        foreach ($sorts as $sort) {
            $parts = explode(' ', trim($sort), 2);

            if (empty($parts)) {
                continue;
            }

            if (1 === \count($parts)) {
                $sorting[$parts[0]] = true;
            } else {
                $sorting[$parts[0]] = $parts[1];
            }
        }

        return $sorting;
    }
}
