<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Group;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\StrUtil;
use ANDS\Util\XMLUtil;

class CoreMetadataProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        // find class
        $classes = ['collection', 'party', 'service', 'activity'];
        $class = null;
        foreach ($classes as $try) {
            $element = $registryObjectsElement->xpath('//ro:registryObject/ro:' . $try);
            if ($element) {
                $class = $try;
            }
        }

        if ($class === null) {
            // todo throw exception here
        }

        $element = array_first($registryObjectsElement->xpath('//ro:registryObject/ro:' . $class));
        $registryObjectElement = array_first(
            $registryObjectsElement->xpath('//ro:registryObject')
        );

        $record->class = $class;
        $record->type = StrUtil::sanitize((string) $element['type']);
        $group = (string)$registryObjectElement['group'];
        $record->group = (string)$registryObjectElement['group'];

        // added group if not exists
        $groupTitle = $group;
        $exist = Group::where('title', $groupTitle)->first();
        if (!$exist) {
            $group = new Group;
            $group->title = $groupTitle;
            $group->slug = str_slug($groupTitle);
            $group->save();
        }

        $record->save();

    }

    public static function get(RegistryObject $record)
    {
        // TODO: Implement get() method.
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {
        return [
            'id' => $record->id,
            'slug' => $record->slug,
            'key' => $record->key,
            'status' => $record->status,
            'group' => $record->group,
            'type' => $record->type,
            'class' => $record->class,
            'data_source_id' => $record->dataSource->id,
            'data_source_key' => $record->dataSource->key,
            'record_modified_at' => $record->modified_at,
            'record_created_at' => $record->created_at
        ];
    }
}