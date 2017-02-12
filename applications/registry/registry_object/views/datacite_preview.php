<?php

//echo '<h3><a href="#">'.$title[0].'</a></h3>';
echo '<div>';

if(isset($description)){
	echo "<p><strong>Description:</strong></p>";
	echo substr($description[0],0,500);
	if(strlen($description[0])>500) echo " ...";
	echo '<hr/>';
}

$citation = implode("; ", $creator);
$citation .= '('.$publicationYear.'): '.$title[0].'; '.$publisher.'. http://dx.doi.org/'.$doi;

echo "<p><strong>Citation:</strong></p>";
echo $citation;
echo "<hr />";

if(isset($resourceTypeGeneral)){
	echo "<p><strong>Resource Type:</strong></p>";	
	echo $resourceTypeGeneral."<hr />";
}

echo '<a href="http://data.datacite.org/'.$doi.'" class="button" target="_blank">View DataCite Metadata</a>';
echo '<div style="float:right;position:relative;"><a href="http://dx.doi.org/'.$doi.'" class="button" target="_blank">View record webpage</a></div>';
echo '</div><br/>';

?>