<?php

namespace ANDS\Commands\Backup;

use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Backup\BackupRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupValidateCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('backups:validate')
            ->setDescription('Restore a backup by id')
            ->setHelp("Validate a backup by id")
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'id of the backup, alphanumeric');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $id = $input->getOption('id');
        if (!$id) {
            throw new \Exception('id is required');
        }

        BackupRepository::init();

        // validate the backup first
        try {
            BackupRepository::validateBackup($id);
        } catch (\Exception $e) {
            $this->log("Backup Validation Failed: ". $e->getMessage(), "error");
            return;
        }

        $this->log("Backup $id validated successfully!", "info");

    }
}