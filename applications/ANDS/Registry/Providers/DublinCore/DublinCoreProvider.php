<?php


namespace ANDS\Registry\Providers\DublinCore;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\RegistryObject;

class DublinCoreProvider implements RegistryContentProvider
{

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Return the DC representation of a registry object
     *
     * @param RegistryObject $record
     * @return string
     * @throws \Exception
     */
    public static function get(RegistryObject $record)
    {
        $xml = $record->getCurrentData()->data;
        $data = [
            'title' => $record->title,
            'publisher' => $record->group,
            'type' => $record->type,
            'source' => MetadataProvider::getOriginatingSource($record),
            'identifiers' => [],
            'descriptions' => [],
            'rights' => [],
            'coverages' => [],
            'subjects' => [],
            'contributors' => []
        ];

        // identifiers include the record url, identifiers and relatedInfo identifiers
        $data['identifiers'][] = $record->portal_url;

        $identifiers = IdentifierProvider::get($record, $xml);

        $data['identifiers'] = collect($data['identifiers'])
            ->merge(collect($identifiers)->pluck('value'))
            ->unique()
            ->toArray();


        $allDescriptions = MetadataProvider::getDescriptions($record, $xml);

        // descriptions are any ro:description without the rights type
        $descriptions = collect($allDescriptions)
            ->filter(function($description){
                return !in_array($description['type'], ['rights', 'accessRights']);
            })->toArray();

        $data['descriptions'] = collect($data['descriptions'])
            ->merge(collect($descriptions)->pluck('value'))
            ->unique()->toArray();

        // rights are any ro:description with the rights type
        $rights = collect($allDescriptions)
            ->filter(function($description){
                return in_array($description['type'], ['rights', 'accessRights']);
            })->toArray();

        $data['rights'] = collect($data['rights'])
            ->merge(collect($rights)->pluck('value'))
            ->unique()->toArray();

        // TODO: Rights in ro:rights

        // spatial coverages
        $spatials = MetadataProvider::getSpatial($record, $xml);
        $data['coverages'] = collect($data['coverages'])
            ->merge(collect($spatials)->pluck('value')->map(function($spatial){
                return 'Spatial: ' . $spatial;
            }))->unique()->toArray();

        // coverage temporal (friendly dates)
        $temporals = DatesProvider::getDateCoverages($record, $xml);
        $friendlyDate = DatesProvider::humanReadableCoverages($temporals);
        if ($friendlyDate) {
            $data['coverages'][] = "Temporal: ". $friendlyDate;
        }

        // contributors are all parties and come in the form of
        // title (relation)

        $contributors = [];
        $relationships = RelationshipProvider::getRelationByClassAndType($record, 'party', null);
        foreach($relationships as $relation){
            foreach($relation['relations'] as $rel) {
                $contributors[] = "{$relation['to_title']} ({$rel['relation_type']})";
            }
        }
        $data['contributors'] = collect($data['contributors'])
            ->merge($contributors)->unique()->toArray();

        $subjects = SubjectProvider::processSubjects($record);
        $data['subjects'] = collect($data['subjects'])
            ->merge(collect($subjects)->pluck('resolved'))
            ->unique()->toArray();

        $dc = new DublinCoreDocument($record, $data);

        return $dc->toXML();
    }
}