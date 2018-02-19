<?php

namespace RokkaCli;

use Rokka\Client\Core\DynamicMetadata\DynamicMetadataInterface;
use Rokka\Client\Core\DynamicMetadata\SubjectArea;
use Rokka\Client\Core\Membership;
use Rokka\Client\Core\Organization;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Core\Stack;
use Rokka\Client\Core\User;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Utility methods to format rokka output, in addition to general output.
 */
class RokkaFormatter extends FormatterHelper
{
    /**
     * Build a string representation for the StackOperation's options attribute.
     *
     * @param array $settings
     *
     * @return string
     */
    public function formatStackOperationOptions(array $settings)
    {
        $data = [];

        foreach ($settings as $name => $value) {
            $data[] = $name.':'.$value;
        }

        return implode(' | ', $data);
    }

    /**
     * Print information about a source image from rokka.
     *
     * @param SourceImage     $sourceImage
     * @param OutputInterface $output
     */
    public function outputImageInfo(SourceImage $sourceImage, OutputInterface $output)
    {
        $output->writeln([
            '  Hash: <info>'.$sourceImage->hash.'</info>',
            '  Organization: <info>'.$sourceImage->organization.'</info>',
            '  Name: <info>'.$sourceImage->name.'</info>',
            '  Size: <info>'.$sourceImage->size.'</info>',
            '  Format: <info>'.$sourceImage->format.'</info>',
            '  Created: <info>'.$sourceImage->created->format('Y-m-d H:i:s').'</info>',
            '  Dimensions: <info>'.$sourceImage->width.'x'.$sourceImage->height.'</info>',
        ]);

        if ($output->isVerbose()) {
            $output->writeln('  BinaryHash: <info>'.$sourceImage->binaryHash.'</info>');
        }

        if (!empty($sourceImage->dynamicMetadata)) {
            if (!$output->isVerbose()) {
                $metaNames = array_keys($sourceImage->dynamicMetadata);
                $output->writeln('  DynamicMetadatas ('.count($metaNames).'): '.implode(', ', $metaNames));
            } else {
                $output->writeln('  DynamicMetadatas:');
                /** @var DynamicMetadataInterface $meta */
                foreach ($sourceImage->dynamicMetadata as $name => $meta) {
                    $output->writeln('     - <info>'.$name.'</info> '.$this->formatDynamicMetadata($meta));
                }
            }
        }
    }

    /**
     * Print information about a rokka organization.
     *
     * @param Organization    $org
     * @param OutputInterface $output
     */
    public function outputOrganizationInfo(Organization $org, OutputInterface $output)
    {
        $output->writeln([
            '  ID: <info>'.$org->getId().'</info>',
            '  Name: <info>'.$org->getName().'</info>',
            '  Display Name: <info>'.$org->getDisplayName().'</info>',
            '  Billing eMail: <info>'.$org->getBillingEmail().'</info>',
        ]);
    }

    /**
     * Print information about an organization membership.
     *
     * @param Membership      $membership
     * @param OutputInterface $output
     */
    public function outputOrganizationMembershipInfo(Membership $membership, OutputInterface $output)
    {
        $output->writeln([
            '  ID: <info>'.$membership->userId.'</info>',
            '  Role: <info>'.$membership->role.'</info>',
            '  Active: <info>'.($membership->active ? 'True' : 'False').'</info>',
        ]);
    }

    /**
     * Print information about a rokka stack.
     *
     * @param Stack           $stack
     * @param OutputInterface $output
     */
    public function outputStackInfo(Stack $stack, OutputInterface $output)
    {
        $output->writeln('  Name: <info>'.$stack->getName().'</info>');
        $output->writeln('  Created: <info>'.$stack->getCreated()->format('Y-m-d H:i:s').'</info>');

        $operations = $stack->getStackOperations();
        if (!empty($operations)) {
            $output->writeln('  Operations:');

            foreach ($stack->getStackOperations() as $operation) {
                $output->write('    '.$operation->name.': ');
                $output->writeln($this->formatStackOperationOptions($operation->options));
            }
        }

        $options = $stack->getStackOptions();
        if (!empty($options)) {
            $output->writeln('  Options:');
            foreach ($stack->getStackOptions() as $name => $value) {
                $output->write('    '.$name.': ');
                $output->writeln('<info>'.$value.'</info>');
            }
        }
    }

    /**
     * Print information about a rokka user.
     *
     * @param User            $user
     * @param OutputInterface $output
     */
    public function outputUserInfo(User $user, OutputInterface $output)
    {
        $output->writeln([
            '  ID: <info>'.$user->getId().'</info>',
            '  eMail: <info>'.$user->getEmail().'</info>',
            '  API-Key: <info>'.$user->getApiKey().'</info>',
        ]);
    }

    /**
     * Convert dynamic metadata information to a string.
     *
     * @param DynamicMetadataInterface $metadata
     *
     * @return string
     */
    private function formatDynamicMetadata(DynamicMetadataInterface $metadata)
    {
        $info = null;
        switch ($metadata::getName()) {
            case SubjectArea::getName():
                $data = [];
                /* @var SubjectArea $metadata */
                foreach (['x', 'y', 'width', 'height'] as $property) {
                    $data[] = $property.':'.$metadata->$property;
                }
                $info = implode('|', $data);

                break;
        }

        return $info;
    }
}
