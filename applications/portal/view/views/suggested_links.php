<?php
if ($suggested_links_contents)
{
	$output = '';

	// Matching identifiers
	if ($suggested_links_contents['identifiers'] && $suggested_links_contents['identifiers']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['identifiers']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a href="javascript:;" class="" data-title="Records with matching identifiers" data-suggestor="ands_identifiers" data-start="0" data-rows="10" relation_type="identifier" ng-click="open($event)">'.$suggested_links_contents['identifiers']['count'].' '.$count_str.'</a> with matching identifiers</h5>';
		foreach($suggested_links_contents['identifiers']['values'] as $v) {
			$output .= '<span class="identifier_value hide">'.$v.'</span>';
		}
	}

	// Matching identifiers
	if ($suggested_links_contents['subjects'] && $suggested_links_contents['subjects']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['subjects']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a href="javascript:;" class="" data-title="Records with matching subjects" data-suggestor="ands_subjects" data-start="0" data-rows="10" relation_type="subject" ng-click="open($event)">'.$suggested_links_contents['subjects']['count'].' '.$count_str.'</a> with matching subjects</h5>';

		foreach($suggested_links_contents['subjects']['values'] as $v) {
			$output .= '<span class="subject_value hide">'.$v.'</span>';
		}
	}

	if ($output)
	{
		$output = '<h2>Suggested Links</h2><h4>Internal Records</h4>'  .  $output.'';
		echo $output;
	}

}
?>
<div id="datacite_explanation" class="hide">
<h3>About DataCite</h3>

<div class='about_datacite'>
	<p>Datacite is a not-for-profit orginisation formed in London on 1 December 2009.</p>

<p>DataCite's aim is to:
<ul style="list-style-type:circle;">
<li>- Establish easier access to research data on the internet</li>
<li>- Increase acceptance of research data as legitimate, citable contributions to the scholarly record</li>
<li>- Support data archiving that will permit results to be verified and re-purposed for further study.</li>
</ul>
</p>

<p>For more information about DataCite, visit <a href='http://datacite.org'>http://datacite.org</a></p>

</div>
</div>