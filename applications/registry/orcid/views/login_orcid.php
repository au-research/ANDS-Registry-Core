<?php
use ANDS\Util\Config as ConfigUtil;
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
                    <img src="<?php echo asset_url('img/ORCID_Member_Logo.png'); ?>"
                         alt="orcid member logo"
                         style="display:block;margin:10px auto;"/>
                    <p>
                        <a href="<?php echo $link ?>"
                           class="btn btn-large btn-block">Login with ORCID
                            <img src="<?php echo asset_url('img/orcid_16x16.png'); ?>"
                                 alt="orcid id logo" style="margin-bottom: 5px;"></a>
                    </p>
					<?php if(ConfigUtil::get('app.deployment_state') !='production'):?>
					<div class="alert alert-info">
						This is a demonstration of ORCID Integration Wizard with <a href="<?php echo portal_url() ?>">Research Data Australia</a>. A <a href="http://sandbox-1.orcid.org/oauth/signin" target="_blank">Sandbox ORCID account</a> is required for testing.
					</div>
					<?php endif; ?>
                    <p>
                        The ARDC Search and Link Wizard allows you to link your
                        ORCID record with your research datasets published in
                        Research Data Australia. By using the wizard you can
                        enrich your research profile and promote your research
                        to others. Your ORCID ID will also be indexed with the
                        <a href="<?php echo portal_url() ?>">Research Data
                            Australia</a> records you link to, making your
                        work more discoverable.
                    </p>
                    <h4>
                        Authorisation
                    </h4>
                    <p>
                        In order to access the ARDC Search and Link Wizard you
                        will need to authorise ARDC to access your ORCID
                        profile.
                    </p>
                    <p>
                        Upon your approval, ARDC will retrieve and store the
                        details of your ORCID record. This information is used
                        to customise your sessions and enable the ARDC to link works
                        to your ORCID record.
                    </p>
                    <p>
                        The ARDC is requesting the following access permissions to
                        your ORCID record:
                    </p>
                    <ul>
                        <li><strong>Add or update your research
                                activities</strong><br/>Allow this organization
                            or application to add
                            activity information stored in their system(s) to
                            your ORCID record and update information they have
                            added.
                        </li>
                        <li><strong>Read limited information from your research
                                activities</strong><br/>Will allow this organization or application to read
                            limited information from your works, funding or
                            affiliations
                        </li>
                    </ul>
                    <h4>Accessing/Deleting Your Personal Information</h4>
                    <p>You have a right to access your personal information or
                        ask for it to be removed from the ARDC system (subject
                        to exceptions allowed by law). To make a request please
                        use the contact information below. You may be required
                        to put your request in writing for security reasons.</p>
                    <h4>Contacting us</h4>
                    <p>If you have any questions or concerns, please contact us
                        by any of the following means during business hours
                        Monday to Friday.</p>
                    <p>
                        ARDC Office<br/><br/>
                        Phone: <a href="tel:%2B61%203%209902%200585">+61 3 9902 0585</a><br/>
                        E-mail: <a href="mailto:services@ardc.edu.au">services@ardc.edu.au</a>
                        <br/>
                    </p>

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