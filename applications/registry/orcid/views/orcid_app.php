<?php $this->load->view('header'); ?>
<div ng-app="orcid_app">
	<div class="content-header">
		<h1>Import Your Datasets to <img style="margin: -13px 0 0 1px;" src="<?php echo asset_url('img/orcid_tagline_small.png'); ?>"/></h1>
	</div>
	<span class="hide" id="orcid_id"><?php echo $orcid->orcid_id; ?></span>
	<div ng-view></div>
	<div class="container_clear"></div>
</div>
<div id="index" class="hide">
	<div class="container-fluid" id="main-content" >
        <div class="alert alert-info">
            You are logged in as
             <?php echo $orcid->full_name; ?> <a href="<?php echo $orcid->url ?>"><?php echo $orcid->url ?>
                <img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" alt=""></a>
            <a class="pull-right" href="<?php echo registry_url('orcid/logout')?>"><i class="icon icon"></i>Sign Out</a>
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
                            <span class="label label-info" ng-show="item.in_orcid">Imported</span>
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
									<h5><a href="<?php echo portal_url()?>{{doc.slug}}/{{doc.id}}">{{doc.title}}</a><span class="label label-info pull-right" style="margin-right:15px;" ng-show="alreadyImported(doc.id)">Imported</span></h5>
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
					<a href="#myModal" role="button" data-toggle="modal" class="btn btn-primary btn-block btn-large import" ng-class="{true:'', false:'disabled', '':'hidden'}[import_available]">
						Import Selected <span ng-show="to_import.length>0">{{to_import.length}}</span> Works
					</a>
				</div>
				<div class="widget-box">
					<div class="widget-title"><h5>Guidelines</h5></div>
					<div class="widget-content">
						<ul>
							<li>Datasets are like other academic output. Only claim those that you can justify/prove your connection to.</li>
							<li>Use the <a href="http://services.ands.org.au/documentation/rifcs/1.6.1/vocabs/vocabularies.html#Party_Relation_Type" target="_blank">"Party relation Type (party)"</a> in RIFCS to help define your connection</li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Datasets already imported from Research Data Australia</h5></div>
					<div class="widget-content">
						<ul>
							<li ng-repeat="item in filteredWorks = (works | filter:{in_orcid:true})"> <a href="{{item.url}}" target="_blank">{{item.title}}</a></li>
						</ul>
                        <div ng-show="!works">
                            Loading... Please wait...
                        </div>
						<div class="alert alert-info" ng-show="works && filteredWorks.length == 0">You have not imported any works from Research Data Australia!</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="modalLabel">Review and Import</h3>
		</div>
		<div class="modal-body">
			<p><b>({{to_import.length}}) works have been selected for import to your ORCID profile.</b></p>
			<p>Please review your selected works and ensure they are appropriate before continuing with the import</p>
			<div class="well" ng-hide="importResult">
				<p ng-repeat="item in to_import">
                    <a href="" ng-click="item.to_import=!item.to_import" tip="Remove">
                        <i class="icon icon-minus-sign"></i>
                    </a> {{item.title}}
                </p>
				<p ng-show="to_import.length==0">No works are selected for import</p>
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
                    <p>Congratulations, <b>({{ importedResultCount }})</b> works have successfully been imported to your ORCID profile.</p>
                </div>
                <div class="alert alert-danger" ng-show="failedResultCount > 0">
                    <p><b>({{ failedResultCount }})</b> works has failed .</p>
                </div>
                <p>Remember to review and set the appropriate visibility settings for the works via your profile in ORCID.</p>
            </div>
		</div>
        <div class="modal-footer">
            <button ng-show="import_stg=='ready'" class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button ng-show="import_stg=='ready' && to_import.length > 0" class="btn btn-primary" ng-click="import()">Import</button>
            <button ng-show="import_stg=='importing'" disabled class="btn btn-primary-disabled">Importing {{to_import.length}} works... please wait</button>
            <button ng-show="import_stg=='complete'" class="btn" data-dismiss="modal" aria-hidden="true" ng-click="import_stg='ready'">Ok</button>
        </div>
	</div>

</div>

<?php $this->load->view('footer'); ?>