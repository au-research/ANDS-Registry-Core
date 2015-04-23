<!-- Modal -->
<div class="modal fade modal-center" id="help_modal" role="dialog" aria-labelledby="Help" aria-hidden="true" style="z-index:9999">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Help</h4>
			</div>
			
			<div class="modal-body">
				<div role="tabpanel">
				  <!-- Nav tabs -->
				  <ul class="nav nav-tabs" role="tablist">
				    <li role="presentation" id="overview_tab" class="active"><a href="" class="help_link" id="overview_link">Overview</a></li>
				    <li role="presentation" id="search_tab"><a href="" class="help_link" id="search_link">Search</a></li>
				    <li role="presentation" id="myrda_tab"><a href="" class="help_link" id="myrda_link">MyRDA</a></li>
				    <li role="presentation" id="advsearch_tab"><a href="" class="help_link" id="advsearch_link">Advanced Search</a></li>
				  </ul>

				  <!-- Tab panes -->
				  <div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="overview">
                        @include('includes/help-overview')
				    </div>
				    <div role="tabpanel" class="tab-pane" id="search">
                        @include('includes/help-search')
				    </div>
				    <div role="tabpanel" class="tab-pane" id="myrda">
                        @include('includes/help-my-rda')
				    </div>
				    <div role="tabpanel" class="tab-pane" id="advsearch">
				    	@include('includes/help-adv-search')
				    </div>
				  </div>

				</div>
			</div>

			<div class="modal-footer">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			</div>
		</div>
	</div>
</div>