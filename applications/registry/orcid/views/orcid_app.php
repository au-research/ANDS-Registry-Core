<?php $this->load->view('header'); ?>
<div ng-app="orcid_app">
	<div class="content-header" style="height:100px;">
		<h1 style="margin-top:30px"> Link Your Datasets to ORCID</h1>
        <img src="<?php echo asset_url('img/ORCID_Member_Logo.png'); ?>"
             alt="orcid member logo"
             style="height:65px;margin:15px 20px;" class="pull-right"/>
	</div>
	<span class="hide" id="orcid_id"><?php echo $orcid->orcid_id; ?></span>
	<div ng-view></div>
	<div class="container_clear"></div>
</div>
<div id="index" class="hide">
	<div class="container-fluid" id="main-content" >
        <div class="alert alert-info">
            You are logged in as
             <?php echo $orcid->full_name; ?> <img
                    src="<?php echo asset_url('img/orcid_16x16.png'); ?>"
                    alt="orcid logo id"> <a
                    href="<?php echo $orcid->url ?>"><?php echo $orcid->url ?>
            </a>
            <div class="pull-right">
                <a href="#helpModal" role="button" data-toggle="modal" style="margin-right: 8px;">Help <i class="icon icon-question-sign"></i> </a>
                <a href="<?php echo registry_url('orcid/logout')?>"><i class="icon icon"></i>Sign Out</a>
            </div>
        </div>
		<div class="row-fluid">
			<div class="span8">
				<div class="widget-box">
					<div class="widget-title">
						<span
                            class="icon"
                            tip="The Suggested Datasets section will list any datasets from Research Data Australia, which are either directly related to your ORCID ID or are related to a researcher matching your surname.">
                            <i class="icon icon-question-sign"></i>
                        </span>
						<h5>Suggested Datasets</h5>
					</div>
					<div class="widget-content">
						<label class="checkbox" ng-repeat="item in filteredWorks = (works| filter:{type:'suggested'})">
							<input type="checkbox" ng-model="item.to_import"/>
                            <a href="{{item.url}}" target="_blank">{{item.title}}</a>
                            <span class="label label-info" ng-show="item.in_orcid">Linked</span>
						</label>
                        <div ng-show="!works">
                            Loading, please wait
                        </div>
						<div class="alert alert-info" ng-show="works && filteredWorks.length == 0">
                            There are no suggested datasets, please use the search functions to look for works
                        </div>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Search for your relevant works in Research Data Australia</h5>
					</div>
					<div class="widget-content">
						<form class="form-search" ng-submit="search()">
							<div class="input-append">
								<input type="text" class="search-query" ng-model="filters.q"/>
								<button type="submit" class="btn">Search</button>
							</div>
						</form>
						<div style="height:450px;overflow:auto">
							<div ng-repeat="doc in search_results.docs">
								<div style="width:25px;float:left;line-height:10px;">
									<input type="checkbox" ng-model="doc.to_import" />
								</div>
								<div style="margin-left:25px;">
									<h5><a href="<?php echo portal_url()?>{{doc.slug}}/{{doc.id}}">{{doc.title}}</a><span class="label label-info pull-right" style="margin-right:15px;" ng-show="alreadyImported(doc.id)">Linked</span></h5>
									<p>{{doc.description | removeHtml}}</p>
								</div>
								<div class="clearfix"></div>
								<hr/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="span4">
				<div class="widget-box">
					<a href="#myModal" role="button" data-toggle="modal" class="btn btn-primary btn-block btn-large import" ng-class="{true:'', false:'disabled', '':'hidden'}[import_available]" ng-click="resetImported()">
						Link Selected <span ng-show="to_import.length>0">{{to_import.length}}</span> Works
					</a>
				</div>

				<div class="widget-box">
					<div class="widget-title">
                        <span
                                class="icon"
                                tip="Refresh"
                                ng-click="refresh(true)">
                            <i class="icon icon-refresh"></i>
                        </span>
                        <h5>Datasets already linked from Research Data Australia</h5>
                    </div>
					<div class="widget-content">
						<ul>
							<li ng-repeat="item in filteredWorks = (works | filter:{in_orcid:true})">
                                <a href="{{item.url}}" target="_blank">{{item.title}}</a>
                                <a href="" ng-click="remove(item)"><i class="icon icon-remove"></i></a>
                            </li>
						</ul>
                        <div ng-show="!works">
                            Loading... Please wait...
                        </div>
						<div class="alert alert-info" ng-show="works && filteredWorks.length == 0">You have not linked any works from Research Data Australia!</div>
					</div>
				</div>



			</div>
		</div>
	</div>

	<!-- Modal -->
	<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="modalLabel">Review and Link</h3>
		</div>
		<div class="modal-body">
			<p><b>({{to_import.length}}) works have been selected for linking to your ORCID Record.</b></p>
			<p>Please review your selected works and ensure they are appropriate before continuing with the linking</p>
			<div class="well" ng-hide="importResult">
				<p ng-repeat="item in to_import">
                    <a href="" ng-click="item.to_import=!item.to_import" tip="Remove">
                        <i class="icon icon-minus-sign"></i>
                    </a> {{item.title}}
                </p>
				<p ng-show="to_import.length==0">No works are selected for linking</p>
			</div>
			<hr>
            <div class="well" ng-show="importResult">
                <div ng-repeat="item in importResult">
                    <i ng-class="{'icon icon-ok': item.in_orcid, 'icon icon-remove': !item.in_orcid}"></i> {{ item.registry_object.title }}
                    <p ng-show="!item.in_orcid">{{ item.error_message }}</p>
                </div>
            </div>
            <hr>
            <div ng-show="import_stg=='complete'">
                <div class="alert alert-success" ng-show="importedResultCount > 0">
                    <p>Congratulations, <b>({{ importedResultCount }})</b> works have successfully been linked to your ORCID Record.</p>
                </div>
                <div class="alert alert-danger" ng-show="failedResultCount > 0">
                    <p><b>({{ failedResultCount }})</b> works has failed .</p>
                </div>
                <p>Remember to review and set the appropriate <a href="https://support.orcid.org/knowledgebase/articles/124518-visibility-settings" target="_blank">visibility settings</a> for the works via your ORCID Record.</p>
            </div>
		</div>
        <div class="modal-footer">
            <button ng-show="import_stg=='ready'" class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button ng-show="import_stg=='ready' && to_import.length > 0" class="btn btn-primary" ng-click="import()">Link</button>
            <button ng-show="import_stg=='importing'" disabled class="btn btn-primary-disabled">Linking {{to_import.length}} works... please wait</button>
            <button ng-show="import_stg=='complete'" class="btn" data-dismiss="modal" aria-hidden="true" ng-click="import_stg='ready'">Ok</button>
        </div>
	</div>

    <div id="helpModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="helpModalLabel">ORCID Search and Link Wizard Help</h3>
        </div>
        <div class="modal-body">
            <p>The ARDC Search and Link Wizard allows you to link your ORCID record with your research datasets published in <a href="<?php echo portal_url();?>">Research Data Australia</a>. By using the wizard, you can enrich your research profile and promote your research to others. Your ORCID ID will also be indexed with the Research Data Australia records you link to, making your work more discoverable.
            </p>
            <h4>Suggested Datasets</h4>
            <p>The Suggested Datasets section will list any datasets from Research Data Australia, which are either directly related to your ORCID ID or are related to a researcher matching your surname. These datasets can be linked to your ORCID record using steps 3 to 6 in the Linking datasets to your ORCID record section below.</p>
            <h4>Linking datasets to your ORCID record</h4>
            <ol>
                <li>Enter a search term (e.g. a subject, part of a title or a researcher’s name)</li>
                <li>Click the ‘Search’ button.</li>
                <li>From the search results, locate the datasets you would like to link to your ORCID record and select them by clicking the checkbox displayed with each record.</li>
                <li>Once you have selected all the records you wish to link, click the ‘Link Selected Works’ button displayed on the right. This will open up the review and link popout.</li>
                <li>Review the selected datasets to make sure they are appropriate. Datasets are like other academic output. Only link to  those that you can justify/prove your connection to. You can remove datasets from within the popout by clicking on the minus icon displayed with each record</li>
                <li>Click the ‘Link’ button to link the datasets to your ORCID record.</li>
            </ol>
            <h4>Unlinking datasets from your ORCID record</h4>
            <p>Unlinking Research Data Australia datasets from your ORCID record can be done via your account on the ORCID website or through the ARDC Search and Link Wizard. The following instructions are for the ARDC Search and Link Wizard.</p>
            <ol>
                <li>Login to your ORCID account and access the ARDC Search and Link Wizard.</li>
                <li>Locate the ‘Datasets already linked from Research Data Australia’ section on the right-hand side of the page.</li>
                <li>Click the ‘X’ icon displayed with the record you wish to unlink. This will open up an unlink confirmation message.</li>
                <li>Click the ‘Ok’ button to unlink the record. Note that you will need to refresh your record on the ORCID website to see the change.</li>
            </ol>
            <h4>Authorisation</h4>
            <p>In order to access the ARDC Search and Link Wizard you are required to authorise ARDC to access your ORCID profile.</p>
            <p>Upon your approval, ARDC retrieves and stores the details of your ORCID record. This information is used to customise your sessions and enable ARDC to link works to your ORCID record.</p>
            <p>ARDC requests the following access permissions to your ORCID record:</p>
            <ul>
                <li>Add or update your research activities<br/>Allow this organization or application to add activity information stored in their system(s) to your ORCID record and update information they have added.</li>
                <li>Read limited information from your research activities<br/>Will allow this organization or application to read limited information from your works, funding or affiliations</li>
            </ul>
            <h4>Accessing/deleting your personal information</h4>
            <p>You have a right to access your personal information or ask for it to be removed from the ARDC system (subject to exceptions allowed by law). To make a request please use the contact information below. You may be required to put your request in writing for security reasons.</p>
            <h4>Contacting us</h4>
            <p>If you have any questions or concerns, please contact us by any of the following means during business hours Monday to Friday.</p>
            <p>
                ARDC Office<br/>
                Monash University,<br/>
                PO Box 197,<br/>
                Caulfield East VIC 3145,<br/>
                AUSTRALIA<br/>
                <br/>
                Phone: +61 3 9902 0585 <br/>
                E-mail: <a href="mailto:services@ardc.org.au">services@ardcs.org.au</a>
                <br/>
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>

</div>

<?php $this->load->view('footer'); ?>