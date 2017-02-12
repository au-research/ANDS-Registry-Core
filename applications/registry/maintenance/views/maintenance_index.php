<?php 

/**
 * Core Maintenance Dashboard
 *  
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="container-fluid" id="main-content">
	<div class="row-fluid">
		<div class="span12  well">
			<form class="form form-inline">
            <div class="input-append">
			  <input id="idkey" type="text" placeholder="ID or Key">
			  <button id="syncRO" class="btn" type="button" tip="Enrich and Index">Sync</button>
			</div>
			<span id="result"></span>
          </form>
		</div>
	</div>
	<div id="stat">Loading Stats...</div>
	<div id="ds">Loading Data Sources Table...</div>
</div>


<script type="text/x-mustache" id="stat-template">
<div class="row-fluid">
	<div class="span12 center" style="text-align: center;">
		<ul class="stat-boxes">
			<li>
				<div class="left peity_bar_good"><span>Database</span>Registry Objects</div>
				<div class="right">
					<strong>{{totalCountDB}}</strong>
				</div>
			</li>
			<li>
				<div class="left peity_bar_good"><span>Database</span>Published</div>
				<div class="right">
					<strong>{{totalCountDBPublished}}</strong>
				</div>
			</li>
			<li>
				<div class="left peity_bar_neutral"><span>SOLR Indexed</span>Registry Objects</div>
				<div class="right">
					<strong>{{totalCountSOLR}}</strong>
				</div>
			</li>
			<li>
				<div class="left peity_bar_bad"><span>Missing</span>Registry Objects</div>
				<div class="right">
					<strong>{{notIndexed}}</strong>
				</div>
			</li>
		</ul>
	</div>
</div>
</script>



<script type="text/x-mustache" id="ds-template">
<div class="row-fluid">
	<div class="span12 center" style="text-align:center;">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button class="btn btn-primary" id="refresh" data-loading-text="Refreshing">Refresh</button>
			</div>
			<div class="btn-group">
				<button class="btn btn-danger task" op="enrich_all" data-loading-text="Loading..." >Enrich Everything</button>
				<button class="btn btn-primary task" op="enrich_missing" data-loading-text="Loading...">Enrich Missing</button>
			</div>
			<div class="btn-group">
				<button class="btn btn-danger task" op="index_all" data-loading-text="Loading...">Re Index Everything</button>
				<button class="btn btn-primary task" op="cleanNotExist" data-loading-text="Loading...">Clear Bad Index</button>
				<button class="btn btn-primary task" op="index_missing" data-loading-text="Loading...">Index Missing</button>

			</div>
		</div>
	</div>
</div>
<div class="widget-box">
	<div class="widget-title">
		<h5>Data Sources</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>id</th>
					<th>Title</th>
					<th>Total Registry Count</th>
					<th>Total Published</th>
					<th>Total Indexed</th>
					<th>Total Missing</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
			{{#dataSources}}
				<tr>
					<td>{{id}}</td>
					<td>{{title}}</td>
					<td>{{totalCountDB}}</td>
					<td>{{totalCountDBPUBLISHED}}</td>
					<td>{{totalCountSOLR}}</td>
					<td>{{totalMissing}}</td>
					<td>
						<div class="btn-group">
							<button class="btn task" op="index_ds" ds_id="{{id}}" data-loading-text="Reindexing">ReIndex</button>
							<button class="btn task" op="enrich_ds" ds_id="{{id}}" data-loading-text="Re Enriching">ReEnrich</button>
							<button class="btn task btn-danger" op="clear_ds" ds_id="{{id}}" data-loading-text="Clearing">Clear Index</button>
						</div>
					</td>
				</tr>
			{{/dataSources}}
			</tbody>
		</table>  
	</div>
</div>
</script>

<?php $this->load->view('footer');?>