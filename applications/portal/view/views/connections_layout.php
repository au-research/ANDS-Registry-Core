<div id="connections_layout_container" class="connections_layout_container">
	<div class="connections_layout_facet">
		<div ng-repeat="f in facet">
			<h2>{{f.label}}</h2>
			<ul>
				<li ng-repeat="v in f.values"><a href="" ng-click="select(f.facet_type, v.title)">{{v.title}} ({{v.count}})</li></a>
			</ul>
		</div>
	</div>
	<div class="connections_layout_right">
		<div class="connections_layout_filter">
			<input type="text" placeholder="Type to filter" ng-model="query"/>
		</div>
		<div class="connections_layout_result">
			<div class="ro_preview" ng-repeat="doc in results.docs">
				<div class="ro_preview_header" >
					<div class="title connection_preview_link">{{doc.title}}</div>
					<div class="clear"></div>
				</div>
				<div class="ro_preview_description hide"></div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
</div>

<script type="text/ng-template" id="connections_layout_template">
	<div class="connections_layout_container">	
		<div class="connections_layout_facet">
			<div ng-repeat="f in facet">
				<h2>{{f.label}}</h2>
				<ul>
					<li ng-repeat="v in f.values"><a href="" ng-click="select(f.facet_type, v.title)">{{v.title}} ({{v.count}})</li></a>
				</ul>
			</div>
		</div>
		<div class="connections_layout_right">
			<div class="connections_layout_filter">
				<input type="text" placeholder="Type to filter" ng-model="query"/>
			</div>
			<div class="connections_layout_result">
				<div class="ro_preview" ng-repeat="doc in results.docs">
					<div class="ro_preview_header" >
						<div class="title connection_preview_link">{{doc.title}}</div>
						<div class="clear"></div>
					</div>
					<div class="ro_preview_description hide"></div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</script>