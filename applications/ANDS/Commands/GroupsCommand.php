<?php


namespace ANDS\Commands\Registry;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Group;
use ANDS\RegistryObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GroupsCommand
 * usage: php ands.php group process
 *
 * @package ANDS\Commands\Registry
 */
class GroupsCommand extends ANDSCommand
{
    protected $processors = ['process', 'identify'];

    protected function configure()
    {
        $this
            ->setName('group')
            ->setDescription('Get something from ro')
            ->setHelp("This command allows you to run provider:process on keys")

            ->addArgument('what', InputArgument::REQUIRED, implode('|', array_keys($this->processors)))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $what = $input->getArgument("what");

        if ($what == "process") {
            $this->processGroups();
        }
    }

    private function processGroups()
    {
        $groups = RegistryObject::select('group')->distinct()->get()->pluck('group')->toArray();
        foreach ($groups as $title) {
            $exist = Group::where('title', $title)->first();
            if ($exist) {
                $this->log("$title exists. Skipping");
                continue;
            }
            $group = new Group;
            $group->title = $title;
            $group->slug = str_slug($title);
            $group->save();
            $this->log("Added group $title ($group->id)");
        }
    }


}