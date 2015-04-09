<!-- Modal -->
<div class="modal fade modal-center" id="help_modal" role="dialog" aria-labelledby="Help" aria-hidden="true" style="z-index:9999">
	<div class="modal-dialog">
		<div class="modal-content">

			<!-- <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Help</h4>
			</div> -->
			
			<div class="modal-body">
				<div role="tabpanel">
				  <!-- Nav tabs -->
				  <ul class="nav nav-tabs" role="tablist">
				    <li role="presentation" class="active"><a href="#overview" aria-controls="overview" role="tab" data-toggle="tab">Overview</a></li>
				    <li role="presentation"><a href="#search" aria-controls="search" role="tab" data-toggle="tab">Search</a></li>
				    <li role="presentation"><a href="#myrda" aria-controls="MyRDA" role="tab" data-toggle="tab">MyRDA</a></li>
				    <li role="presentation"><a href="#advsearch" aria-controls="advsearch" role="tab" data-toggle="tab">Advanced Search</a></li>
				  </ul>

				  <!-- Tab panes -->
				  <div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="search">
				    	Overview Video	
				    </div>
				    <div role="tabpanel" class="tab-pane active" id="search">
				    	
				    </div>
				    <div role="tabpanel" class="tab-pane" id="myrda">
				    	
				    </div>
				    <div role="tabpanel" class="tab-pane" id="advsearch">
				    	@include('includes/help-adv-search')
				    </div>
				  </div>

				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-link">Don't show this to me again (ever!)</button>
			</div>
		</div>
	</div>
</div>