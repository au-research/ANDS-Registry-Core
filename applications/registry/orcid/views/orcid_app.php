<?php $this->load->view('header'); ?>
<div ng-app="orcid_app">
	<div class="content-header">
		<h1>Import Your Datasets to <img style="margin: -13px 0 0 1px;" src="<?php echo asset_url('img/orcid_tagline_small.png'); ?>"/></h1>
	</div>
	<span class="hide" id="orcid_id"><?php echo $orcid_id; ?></span>
	<span class="hide" id="first_name"><?php echo $first_name; ?></span>
	<span class="hide" id="last_name"><?php echo $last_name; ?></span>
	<div ng-view></div>
	<div class="container_clear"></div>
</div>
<div id="index" class="hide">
	<div class="container-fluid" id="main-content" >
		<div class="row-fluid">
			<div class="span8">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" tip="The Suggested Datasets section will list any datasets from Research Data Australia, which are either directly related to your ORCID ID or are related to a researcher matching your surname."><i class="icon icon-question-sign"></i></span>
						<h5>Suggested Datasets</h5>
						<div class="btn-group pull-right">
							<a href="" class="btn btn-primary btn-small" ng-class="{true:'', false:'disabled', '':'hidden'}[import_suggested]">Import Selected Works</a>
						</div>
					</div>
					<div class="widget-content">
						<label class="checkbox" ng-repeat="item in works | filter:{type:'suggested'}">
					    	<input type="checkbox" ng-model="item.to_import"> <a href="{{item.url}}" target="_blank">{{item.title}}</a> <span class="label label-info" ng-show="item.imported">Imported</span>
						</label>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Search for your relevant works in Research Data Australia</h5>
						<div class="btn-group pull-right">
							<a href="" class="btn btn-primary btn-small" ng-class="{true:'', false:'disabled', '':'hidden'}[import_searched]">Import Selected Works</a>
						</div>
					</div>
					<div class="widget-content">
						<form class="form-search" ng-submit="search()">
							<div class="input-append">
								<input type="text" class="search-query" ng-model="filters.q">
								<button type="submit" class="btn">Search</button>
							</div>
						</form>
						<div>
							{{search_results.docs}}
						</div>
					</div>
				</div>
			</div>
			<div class="span4">
				<div class="widget-box">
					<div class="widget-title"><h5>Guidelines</h5></div>
					<div class="widget-content">
						<ul>
							<li>Datasets are like other academic output. Only claim those where your connection to the dataset is something that you would include on your CV</li>
							<li>Only claim datasets that you can justify/prove your connection to</li>
							<li>Use the <a href="http://services.ands.org.au/documentation/rifcs/1.5/vocabs/vocabularies.html#Party_Relation_Type" target="_blank">"Party relation Type (party)"</a> in RIFCS to help define your connection</li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Datasets already imported from Research Data Australia</h5></div>
					<div class="widget-content">
						<ul>
							<li ng-repeat="item in works | filter:{type:'imported'}"> <a href="{{item.url}}" target="_blank">{{item.title}}</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>