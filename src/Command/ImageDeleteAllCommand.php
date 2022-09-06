<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImageDeleteAllCommand extends BaseRokkaCliCommand
{
    protected function configure(): void
    {
        $this
            ->setName('image:delete-all')
            ->setDescription('Remove all images of an organization from Rokka')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the images from')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Confirm the deletion of all images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $organization = $input->getOption('organization');
        if (!$organization = $this->resolveOrganizationName($organization, $output)) {
            return -1;
        }

        $confirm = $input->getOption('yes');
        if (!$confirm) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Continue with removing all images from <info>'.$organization.'</info>? [y/n] ', false);
            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        $client = $this->clientProvider->getImageClient($organization);

        $limit = 20;
        $images = $client->searchSourceImages([], [], $limit);
        $skipped = [];
        $stopOnError = false;

        // Skipped images must be not considered in the counting here
        while ($images->count() - \count($skipped) > 0) {
            foreach ($images->getSourceImages() as $image) {
                try {
                    if (!array_key_exists($image->hash, $skipped)) {
                        if (!$client->deleteSourceImage($image->hash, $organization)) {
                            $output->writeln($this->formatterHelper->formatBlock([
                                'Error!',
                                'Error while removing the image:'.$image->hash,
                            ], 'error', true));
                            $skipped[$image->hash] = true;
                        }
                        $output->writeln('Image <info>'.$image->name.'</info> ('.$image->shortHash.') removed from <info>'.$organization.'</info>.');
                    }
                } catch (\Exception $e) {
                    $output->writeln('');
                    $output->writeln($this->formatterHelper->formatBlock([
                        'Error: Exception',
                        $e->getMessage(),
                    ], 'error', true));
                    $skipped[$image->hash] = true;
                    if ($stopOnError) {
                        return -1;
                    }
                }
            }

            $images = $client->searchSourceImages([], [], $limit, $images->getCursor());
        }

        return 0;
    }
}
