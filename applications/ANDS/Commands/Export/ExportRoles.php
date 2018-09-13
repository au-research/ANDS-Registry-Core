<?php


namespace ANDS\Commands\Export;


use ANDS\Commands\ANDSCommand;
use ANDS\Role\Role;
use ANDS\Role\RoleRelation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportRoles extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('export:roles')
            ->setDescription('Export roles in CSV')
//            ->addOption('nodes', null, InputOption::VALUE_NONE, "Nodes")
//            ->addOption('direct', null, InputOption::VALUE_NONE, "Direct Relations")
//            ->addOption('primary', null, InputOption::VALUE_NONE, "Direct Relations")
//            ->addOption('identical', null, InputOption::VALUE_NONE, "Identical Relations")
//            ->addOption('relatedInfoRelations', null, InputOption::VALUE_NONE, "Related Info Relations")
//            ->addOption('relatedInfoNodes', null, InputOption::VALUE_NONE, "Related Info Nodes")
//            ->addOption('subjectsRelations', null, InputOption::VALUE_NONE, "Related Subjects")
//            ->addOption('importPath', 'i', InputOption::VALUE_REQUIRED, "Import Path", env("NEO4J_IMPORT_PATH", "/tmp/"))
//            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, "Export Format", RegistryObject::$CSV_NEO_GRAPH)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();
//        dd(RoleRelation::all());
        $role = Role::findByRoleID("109698716774087641529");
        dd($role->functions()->pluck('role_id'));
    }
}