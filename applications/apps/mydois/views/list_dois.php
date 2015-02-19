<?php 

/**
 * DOI Listing Screen
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<?php 
$this->load->view('header'); 
$testDoiPrefix =  $this->config->item('test_doi_prefix');
?>

<div class="content-header">
	<h1><?php echo $client->client_name;?></h1>
</div>

<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor(apps_url('mydois'), 'Digital Object Identifiers'); ?>
	<a href="#/" class="current"><?php echo $client->client_name ?> <small class="muted">(<?php echo $client->app_id?>)</small></a>
</div>

<div class="container-fluid">
	<div class="widget-box">
		<div class="widget-title">
			<ul class="nav nav-tabs">
				<li class="active" name="list"><a href="javascript:;">My DOIs</a></li>
				<li name="mint"><a href="javascript:;">Mint DOI</a></li>
				<li name="log"><a href="javascript:;">Activity Log</a></li>
				<li name="conf"><a href="javascript:;">App ID Configuration</a></li>
				<li name="check_links"><a href="javascript:;">Check DOI Links</a></li>
			</ul>
		</div>
		<div class="widget-content" name="list">
			<h3>Listing DOIs for <?php echo $client->client_name;?> <small>(<?php echo $client->app_id;?>)</small></h3>
			<table class="table table-hover table-condensed">
				<thead>
					<tr>
						<th style="text-align:left">Title</th>
						<th style="text-align:left">DOI</th>
						<th style="text-align:left"></th>
						<th style="text-align:left"></th>
						<th style="text-align:left">Status</th>
						<th style="text-align:left">Last Updated</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($dois AS $doi): 
				$doiTitle = getDoiTitle($doi->datacite_xml);
				?>
					<tr>
						<td width="40%"><small><strong><?=$doiTitle;?></strong><br/><?=anchor($doi->url,$doi->url);?></small></td>
						<td>
							<?=anchor('http://dx.doi.org/' . $doi->doi_id, $doi->doi_id);?>
							<?php if(strpos($doi->doi_id ,$testDoiPrefix) === 0) {echo "<br/><span class='muted'><em>Test prefix DOI</em></span>";}  ?>
						</td>
						<td>
							<?=anchor('mydois/updateDoi?app_id='.rawurlencode($client->app_id).'&doi_id=' . rawurlencode($doi->doi_id), 'Update', array("role"=>"button", "class"=>"btn btn-mini", "data-target"=>"#updateDoiModal", "data-toggle"=>"modal"));?>
						</td>
						<td>
							<?=anchor('mydois/getDoiXml?doi_id=' . rawurlencode($doi->doi_id), 'View XML', array("role"=>"button", "class"=>"btn btn-mini", "data-target"=>"#viewDoiXmlModal", "data-toggle"=>"modal"));?>
						</td>
						<td><?=$doi->status;?>
						</td>
						<td><?=date('Y-m-d H:i:s', strtotime($doi->updated_when));?></td>
					</tr>	
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="widget-content hide" name="mint">
			<?php $this->load->view('mint_doi'); ?>
		</div>
		<div class="widget-content hide" name="log">
			<table class="table table-hover table-condensed">
				<thead>
					<tr>
						<th>Service</th>
						<th>Date</th>	
						<th>DOI</th>
						<th>Message</th>		
					</tr>
				</thead>
				<tbody>
				<?php foreach($activities AS $act): ?>
					<tr class="<?=($act->result == "FAILURE" ? 'error' : 'success');?>">
						<td><small><?=$act->activity;?></small></td>
						<td>
							<small><?=date('Y-m-d H:i:s', strtotime($act->timestamp));?></small>
						</td>
						<td>
							<small><?=$act->doi_id;?></small>
						</td>
						<td width="60%"><pre><small><?=nl2br($act->message);?></small></pre></td>
					</tr>	
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="widget-content hide" name="conf">
			<dl class="dl-vertical">
			  <dt>Client Name</dt>
			  <dd><?=$client->client_name;?></dd>
			  <dt>Client Contact Name</dt>
			  <dd><?=$client->client_contact_name;?></dd>
			  <dt>Client Contact Email</dt>
			  <dd><?=$client->client_contact_email;?></dd>
			</dl>

			<dl>
			  <dt>Authorised IP Address</dt>
			  <dd><pre><?=$client->ip_address;?></pre></dd>
			  <dt>DataCite Prefix</dt>
			  <dd><pre><?=$client->datacite_prefix;?></pre></dd>
			  <dt>Permitted URL Domains</dt>
			  <dd><pre><?=implode(", ", $client->permitted_url_domains);?></pre></dd>
			</dl>
            <p class="alert">To request a change to any of the information related to this DOI AppID, please contact <a href="mailto:services@ands.org.au">services@ands.org.au</a></p>
		</div>
		<div class="widget-content hide" name="check_links">
			<div class="alert alert-info">
                The DOI Link report will be sent to the registered Client Contact Email.
            </div>
			<a href="javascript:;" id="linkChecker" class="btn btn-primary" app_id="<?php echo $app_id; ?>">Check DOI Links</a>
			<hr>
			<div id="linkChecker_result"></div>
		</div>
	</div>
</div>

<div class="modal hide fade" id="viewDoiXmlModal" tabindex="-1" role="dialog" aria-labelledby="viewDoiXmlModal" aria-hidden="true">
  <div class="modal-body">
    <p>Loading...</p>
    <div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"></div>
	</div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>

<div class="bigModal  modal hide fade" id="updateDoiModal" tabindex="-1" role="dialog" aria-labelledby="updateDoiModal" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="doi_update_close_x">×</button>
        <h3>Update DOI</h3>
    </div>
    <div class="modal-body">
        <p>Loading...</p>
        <div class="progress progress-striped active">
            <div class="bar" style="width: 100%;"></div>
        </div>
    </div>
    <div class="modal-footer">
        <span id="result"></span>
        <a id="doi_update_confirm" class="btn btn-primary" data-loading-text="Updating..." href="javascript:;">Update DOI</a>
        <a id="doi_update_close"class="btn hide" data-dismiss="modal" href="#">Close</a>
        </form>
    </div>
</div>

<?php $this->load->view('footer');?>
<?php 
if(isset($doi_update))
{
	//echo (substr($doi_update,0,5));
	//{
		//$doi_update = "<span class='error'>".$doi_update."</span>";
	//}
?>
<div class="modal hide fade" id="updateDoiResult" tabindex="-1" role="dialog" aria-labelledby="updateDoiResult" aria-hidden="true">
	<div class="modal-header">
		 <button type="button" class="close" data-dismiss="modal">×</button>
		  <h3><?php if(isset($error)) { echo "Alert"; } else { echo '&nbsp;';}?></h3>
	</div>	
  	<div class="modal-body">
   		<p>
    	<div>
    		<?php 
    		if(isset($error))
    		{
    		?>
    			<p>An error has occurred:</p>
    			<p>Update of the doi was unsuccessful. The following error message was returned:</p>
    		<?php 
    		}
    		?>
    		<p><?=$doi_update?></p>
    	</div>
    </p>
    </div>
    <div class="modal-footer">
        <a id="doi_update_close" class="btn hide" data-dismiss="modal" href="#">Close</a>

  	</div>
</div>

<script >
$("#updateDoiResult").modal();
</script>


<?php 
}
?>
<?php 

function getDoiTitle($doiXml)
{
	
	$doiObjects = new DOMDocument();
	$titleFragment = 'No Title';
	if(strpos($doiXml ,'<') === 0)
	{			
		$result = $doiObjects->loadXML(trim($doiXml));
		$titles = $doiObjects->getElementsByTagName('title');
		
		if($titles->length > 0)
		{
			$titleFragment = '';
			for( $j=0; $j < $titles->length; $j++ )
			{
				if($titles->item($j)->getAttribute("titleType"))
				{
					$titleType = $titles->item($j)->getAttribute("titleType");
					$title = $titles->item($j)->nodeValue;
					$titleFragment .= $title." (".$titleType.")<br/>";
				}
				else {
					$titleFragment .= $titles->item($j)->nodeValue."<br/>";
				}
			}
		}
	}
	else{
		$titleFragment = $doiXml;
	}
		
	return $titleFragment;
	
}


?>