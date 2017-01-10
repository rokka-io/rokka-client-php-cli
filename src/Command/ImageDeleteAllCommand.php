<?php

namespace RokkaCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ImageDeleteAllCommand extends BaseRokkaCliCommand
{
    protected function configure()
    {
        $this
            ->setName('image:delete-all')
            ->setDescription('Remove all images from Rokka')
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The organization to delete the images from')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Confirm the deletion of all images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $organization = $this->configuration->getOrganizationName($input->getOption('organization'));

        if (!$this->verifyOrganizationName($organization, $output)) {
            return -1;
        }

        if (!$this->verifyOrganizationExists($organization, $output)) {
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
        $images = $client->listSourceImages($limit);
        $skipped = [];
        $stopOnError = false;

        // Skipped images must be not considered in the counting here
        while ($images->count() - count($skipped) > 0) {
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
                        $output->writeln('Image <info>'.$image->name.'</info> ('.$image->hash.') removed from <info>'.$organization.'</info>.');
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

            // We need to increment the limit here, to avoid to get stuck with retrieving all skipped images!
            $images = $client->listSourceImages($limit + count($skipped));
        }

        return 0;
    }
}
