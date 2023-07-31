<?php
/**
 * This script will take a CSV-seperated definition of the
 * ANZSRC Field of Research codes vocabulary (as provided by
 * the ABS) and output a SKOS-structured Turtle file which
 * can then be imported into a triple-store for integration
 * with the ANDS Vocabulary Service.
 */


ini_set('auto_detect_line_endings',1);

$concepts = array();
define('CONCEPT_NAMESPACE', 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/');
define('CONCEPTSCHEME_PREFLABEL', '"1297.0 - Australian and New Zealand Standard Research Classification (ANZSRC), 2008"@en');
define('CONCEPTSCHEME_ALTLABEL', '"Field of Research Codes"@en');


// Parse the values from the ABS csv file
if (($handle = fopen("ANZSRC-FOR-EXPORT.csv", "r")) !== FALSE) {
	while (($data = fgetcsv($handle,0, ",")) !== FALSE) {

		if (isset($data[0]) && isset($data[1]) && trim($data[1]))
		{
			// By default, the first field is the notation, the second is the label
			if ($data[0])
			{
				$concepts[$data[0]] = $data[1];
			}
			else if($data[1])
			{
				// For some "headings", the notation and label both appear in the second field
				$values = explode(" ",$data[1]);
				$data[0] = $values[0];
				$data[1] = implode(array_slice(	$values, 1)," ");
				$concepts[$data[0]] = $data[1];
			}
		}

	}
}

// Store the topConcepts (when we find them) and display at the end of the loop
$topConcepts = array();

// Generate the TTL/N3 triples for each concept 
foreach($concepts AS $notation => $label)
{

	$concept_uri = CONCEPT_NAMESPACE . $notation;
	$properties = array();

	$properties["rdf:type"] = "skos:Concept";
	$properties["skos:prefLabel"] = '"'.$label.'"@en';
	$properties["skos:notation"] = '"'.$notation.'"@en';
	$properties["skos:inScheme"] = "<".CONCEPT_NAMESPACE.">";

	// Top level concepts are two digits long
	if (strlen($notation) == 2)
	{
		$properties["skos:topConceptOf"] = "<".CONCEPT_NAMESPACE.">";
		$topConcepts[] =  "<".CONCEPT_NAMESPACE.$notation.">";
	}

	// Drill down on our narrower terms and up to our broader terms
	// NB: This is expensive (O(n^2)) -- could probably build
	// the tree somewhere above and recurse to be more performant
	foreach($concepts AS $sub_notation => $sub_label)
	{
		if (substr($sub_notation,0,strlen($notation)) == $notation && $sub_notation != $notation)
		{
			if (strlen($sub_notation) == (strlen($notation) + 2)) {
				$properties["skos:narrower"][] = "<".CONCEPT_NAMESPACE.$sub_notation.">";
			}
			else
			{
				$properties["skos:narrowerTransitive"][] = "<".CONCEPT_NAMESPACE.$sub_notation.">";
			}
		}
		else if (substr($notation,0,strlen($sub_notation)) == $sub_notation && $sub_notation != $notation)
		{	
			if (strlen($sub_notation) == (strlen($notation) - 2)) {
				$properties["skos:broader"][] = "<".CONCEPT_NAMESPACE.$sub_notation.">";
			}
			else
			{
				$properties["skos:broaderTransitive"][] = "<".CONCEPT_NAMESPACE.$sub_notation.">";
			}
		}
	}

	// Generate the triples for each property of this concept
	foreach($properties AS $predicate => $property)
	{
		if (is_array($properties[$predicate]))
		{
			foreach ($properties[$predicate] AS $object)
			{
				echo "<".$concept_uri . "> " . $predicate . " " . $object . "." . PHP_EOL;
			}
		}
		else
		{
			echo "<".$concept_uri . "> " . $predicate . " " . $property . "." . PHP_EOL;
		}
	}
	echo PHP_EOL;
}


// Describe the ConceptScheme
echo "<".CONCEPT_NAMESPACE.">" . " rdf:type " . "skos:ConceptScheme.";
echo "<".CONCEPT_NAMESPACE.">" . " skos:prefLabel " . CONCEPTSCHEME_PREFLABEL . " ." . PHP_EOL;
echo "<".CONCEPT_NAMESPACE.">" . " skos:altLabel " . CONCEPTSCHEME_ALTLABEL . " ." . PHP_EOL;

foreach ($topConcepts AS $concept_uri)
{
	echo "<".CONCEPT_NAMESPACE.">" . " skos:hasTopConcept " . $concept_uri . " ." . PHP_EOL;
}
