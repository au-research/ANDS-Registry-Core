<?php 

/**
 * Role Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Login to ORCID</h1>
</div>
<div class="container-fluid" id="main-content">
	<div class="row-fluid">
		<div class="span3">&nbsp;</div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title">
					<h5>Login</h5>
				</div>
				<div class="widget-content">
					<img src="<?php echo asset_url('img/orcid_tagline.png'); ?>" style="display:block;margin:10px auto;"/>
					<p>
						<a href="<?php echo $link?>" class="btn btn-block btn-primary">Login with ORCID ID</a>
					</p>
					<?php if($this->config->item('deployment_state')!='production'):?>
					<div class="alert alert-info">
						This is a demonstration of ORCID Integration Wizard with <a href="http://researchdata.ands.org.au">Research Data Australia</a>. A <a href="http://sandbox-1.orcid.org/oauth/signin" target="_blank">Sandbox ORCID account</a> is required for testing.
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>
	
</div>

<script type="text/x-mustache" id="roles-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Roles</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Type</th>
					<th>Enabled</th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td><a href="<?php echo base_url();?>role/view/?role_id={{{role_id}}}">{{name}}</a></td>
					<td><span class="label">{{type}}</span></td>
					<td>{{{enabled}}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>

<?php $this->load->view('footer');?>