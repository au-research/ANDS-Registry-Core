<?php 

/**
 * DOI View XML (DataCite)
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<h3>Viewing Datacite XML fragment for <?=$doi_id;?></h3>
<pre style="height: auto;max-height:200px; overflow:auto;">
<?=($datacite_xml != "" ? htmlentities($datacite_xml) : "<i>XML fragment is empty</i>");?>
</pre>
