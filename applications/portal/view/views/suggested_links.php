<?php
if ($suggested_links_contents)
{
	$output = '';

	// Matching identifiers
	if ($suggested_links_contents['identifiers'] && $suggested_links_contents['identifiers']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['identifiers']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a href="#" class="show_accordion" data-title="Records with matching identifiers" data-suggestor="ands_identifiers" data-start="0" data-rows="10">'.$suggested_links_contents['identifiers']['count'].' '.$count_str.'</a> with matching identifiers</h5>';
	}

	// Matching identifiers
	if ($suggested_links_contents['subjects'] && $suggested_links_contents['subjects']['count'] > 0)
	{
		$count_str = ($suggested_links_contents['subjects']['count'] == 1 ? "record" : "records");
		$output .= '<h5><a href="#" class="show_accordion" data-title="Records with matching subjects" data-suggestor="ands_subjects" data-start="0" data-rows="10">'.$suggested_links_contents['subjects']['count'].' '.$count_str.'</a> with matching subjects</h5>';
	}

	if ($output)
	{
		$output = "<h2>Suggested Links</h2><h4>Internal Records</h4>"  .  $output;
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