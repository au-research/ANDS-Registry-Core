<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Group;
use ANDS\RegistryObject;

class ProcessGroups extends GenericScript implements GenericScriptRunnable
{
    public function run()
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