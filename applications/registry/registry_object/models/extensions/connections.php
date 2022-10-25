<?php


class Connections_Extension extends ExtensionBase
{

	private $party_one_types = array('person','administrativePosition');
	private $party_multi_types = array('group','Group');

	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}

    function getRelatedObjectsByClassAndRelationshipType($classArray = array(), $relationshipTypeArray = array(), $forDCI = false)
    {
        // todo call the new RelationshipSearchService for every reference
        // and remove the connections_extension afterward
        return [];
    }


}