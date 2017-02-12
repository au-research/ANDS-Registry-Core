<?php 

/**
 * DOI View App ID configuration
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<dl class="dl-horizontal">
  <dt>Client Name</dt>
  <dd><?=$client_name;?></dd>
  
  <dt>Client Contact Name</dt>
  <dd><?=$client_contact_name;?></dd>
  
  <dt>Client Contact Email</dt>
  <dd><?=$client_contact_email;?></dd>
</dl>

<dl>
  <dt>Authorised IP Address</dt>
  <dd><pre><?=$ip_address;?></pre></dd>
  
  <dt>DataCite Prefix</dt>
  <dd><pre><?=$datacite_prefix;?></pre></dd>
  
  <dt>Permitted URL Domains</dt>
  <dd><pre><?=implode(", ", $permitted_url_domains);?></pre></dd>
  
</dl>