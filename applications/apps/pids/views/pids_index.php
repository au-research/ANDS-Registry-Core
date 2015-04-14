<?php 

/**
 * PIDs Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Identify My Data</h1>
</div>

<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/pids', 'Identify My Data', array('class'=>'current')); ?>
</div>

<input type="hidden" value="<?php echo $identifier; ?>" id="identifier"/>


<div class="container-fluid">
	<div class="widget-box">
		<div class="widget-title">
			<ul class="nav nav-tabs pull-left">
				<li class="active" name="list"><a href="javascript:;">List PIDs</a></li>
				<li name="mint"><a href="javascript:;">Mint PIDs</a></li>
				<li name="export"><a href="javascript:;">Export</a></li>
			</ul>
			<select class="chosen" id="pid_chooser">
				<option value=""></option>
				<?php foreach($orgRole as $o): ?>
				<option value="<?php echo $o ?>"><?php echo $o; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="widget-content" name="list">
			<div id="pids">Loading...</div>
		</div>
		<div class="widget-content hide" name="mint">
			<form action="#" method="get" class="form-horizontal" id="mint_form">
				<div class="control-group">
					<label for="" class="control-label">Mint as</label>
					<div class="controls">
						<span class="uneditable-input"><?php echo $identifier ? $identifier : 'My Identifier'; ?></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">URL</label>
					<div class="controls">
						<input type="url" name="url" value="" placeholder="http://"/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc"/>
					</div>
				</div>
				<?php if($registry_super_user == true): ?>
				<div class="control-group">
					<label for="" class="control-label"></label>
					<div class="controls">
						<label class="checkbox">
							<input type="checkbox" id="batch_mint_toggle"> Batch Mint
						</label>
					</div>
				</div>
				<div id="pids_counter" class="hide">
					<div class="control-group">
						<label class="control-label">Amount</label>
						<div class="controls">
							<input type="number" name="counter" value="1"/>
							<p class="help-inline">1 - 100</p>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label">Upload CSV</label>
						<div class="controls">
							<input type="file" name="csv_file" id="csv_file"> <a href="javascript:;" id="clear_csv_file"><i class="icon icon-remove" tip="Clear"></i></a>
						</div>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls" id="upload_result"></div>
				</div>
				<?php endif; ?>
<div style="height:175px;overflow:auto;border:1px solid #ccc;display:none;" id="terms"><p>You have asked to mint a persistent identifier through ANDS <i>Identify
My Data</i> self-service. This means that you will enter location and/or
description information relating to the object you wish to identify and
ANDS will provide you with a persistent identifier for that object.</p>
<p>
In using ANDS <i>Identify My Data</i> self-service you agree that:
</p>
<ul>
	<li>You are part of the higher education, public research or cultural
	collections sector and that at least some of the objects you are
	identifying are publicly available or will eventually become publicly
	available.</li>
	<li>You are authorised and entitled to mint and manage persistent
	identifiers for the objects you intend to identify.</li>
	<li>You will endeavour to keep up-to-date the location and
	description fields for the persistent identifiers you mint.</li>
	<li>You understand that this location and description information
	will be available to the general public and that confidential material
	should not be entered into these fields.</li>
	<li>You will take responsibility for liaison with any party who has
	queries regarding persistent identifiers that you mint. (ANDS does not
	provide link-rot checking or help-desk services for end-users of
	persistent identifiers.)</li>
</ul>

<p>
You understand that:
</p>
<ul>
	<li>ANDS provides the <i>Identify My Data</i> product on an ‘as is’ and
	‘as available’ basis. ANDS hereby exclude any warranty either express
	or implied as to the merchantability, fitness for purpose, accuracy,
	currency or comprehensiveness of this product. To the fullest extent
	permitted by law, the liability of ANDS under any condition or warranty
	which cannot be excluded legally is limited, at the option of ANDS to
	supplying the services again or paying the cost of having the services
	supplied again.</li>
	
	<li>ANDS does not manage persistent identifiers; ANDS only provides
	the infrastructure that allows minting, resolution and updating of
	identifiers. Processes and policies need to be put in place by those
	utilising <i>Identify My Data</i> to ensure that appropriate maintenance
	practices are put in place to underpin persistence.</li>
	<li>ANDS will endeavour to persist ANDS Identifiers for a minimum of
	twenty years.</li>
	<li>The allocation of a persistent identifier to an object does not
	include any transfer or assignment of ownership of any Intellectual
	Property right (IPR) with regard to that content.</li>
	<li>ANDS will endeavour to provide a high availability service.
	However, ANDS <i>Identify My Data</i> is underpinned and reliant on the <a href="http://www.handle.net/">Handle
	services</a> provided by the <a href="http://cnri.reston.va.us/">Corporation for National Research Initiatives</a>
	(CNRI), in particular the Global Handle Registry. ANDS cannot warrant
	the longevity or reliability of the Handle system or the CNRI.</li>
</ul>
</div>
				<div class="control-group">
					<div class="controls">
						<input type="checkbox" name="agree" checked=checked/> I Agree To the <a href="javascript:;" id="toggleTerms">Terms and Conditions</a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a href="javascript:;" id="mint_confirm" class="btn btn-primary">Mint</a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls" id="mint_result"></div>
				</div>
				<hr>
				
			</form>
		</div>
		<div class="widget-content hide" name="export">
			<a href="<?php echo apps_url('pids/my_pids')?>" class="btn btn-primary">Export All PIDs as CSV</a>
			<?php if($batch_pid_files):  ?>
			    <ul>
			        <?php foreach($batch_pid_files as $o): ?>
			            <li><a href="../assets/uploads/pids/<?php echo $o; ?>"><?php echo $o; ?></a></li>
			        <?php endforeach; ?>
			    </ul>
			<?php endif; ?>
		</div>
	</div>
</div>



<script type="text/x-mustache" id="pids-list-template">
<form class="form-search">		
	<div class="input-append">
	    <input type="text" class="search-query" id="search_query" value="{{search_query}}"/>
	    <button type="submit" class="btn">Search</button>
	</div>
	Total number of Identifiers owned: <strong>{{result_count}}</strong>
</form>
{{#no_result}}
<div class="well">No result!</div>
{{/no_result}}
<hr/>
{{#pids}}
<div class="widget-box">
	<div class="widget-title">
		<h5><a href="<?php echo base_url();?>pids/view/?handle={{handle}}">{{handle}}</a></h5>
	</div>
	<div class="widget-content">
		<dl class="dl-nomargin">
			{{#resolver_url}}
				<dt>Resolver Link</dt> 
				<dd><a href="{{resolver_url}}">{{resolver_url}}</a></dd>
			{{/resolver_url}}
			{{#hasDESC}}<dt>Description</dt>{{/hasDESC}}
			{{#DESC}}
				<dd><span class="desc">{{.}}</span></dd>
			{{/DESC}}
			{{#hasURL}}<dt>URL</dt>{{/hasURL}}
			{{#URL}}
				<dd>{{URL}}</dd>
			{{/URL}}
		</dl>
	</div>	
</div>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>

<script type="text/x-mustache" id="pids-more-template">
{{#pids}}
<div class="widget-box">
	<div class="widget-title">
		<h5><a href="<?php echo base_url();?>pids/view/?handle={{handle}}">{{handle}}</a></h5>
	</div>
	<div class="widget-content">
		<dl class="dl-nomargin">
			{{#resolver_url}}
				<dt>Resolver Link</dt> 
				<dd><a href="{{resolver_url}}">{{resolver_url}}</a></dd>
			{{/resolver_url}}
			{{#hasDESC}}<dt>Description</dt>{{/hasDESC}}
			{{#DESC}}
				<dd><span class="desc">{{.}}</span></dd>
			{{/DESC}}
			{{#hasURL}}<dt>URL</dt>{{/hasURL}}
			{{#URL}}
				<dd>{{URL}}</dd>
			{{/URL}}
		</dl>
	</div>	
</div>
{{/pids}}
{{#hasMore}}
<a href="javascript:;" class="btn btn-block load_more" next_offset="{{next_offset}}">Load More <i class="icon icon-arrow-down"></i></a>
{{/hasMore}}
</script>
<?php $this->load->view('footer');?>