<?php
/**
 * Connections layout view, called by connections.php
 * This is an angularJS enabled template
 *
 *
 * @requires portal-filters for truncate and relationship filters
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>
<div id="connections_layout_container" class="connections_layout_container hide">
	
	<div class="connections_layout_right">
		<div class="connections_layout_filter">
			<input type="text" placeholder="Type to filter" ng-model="query"/>
		</div>
		<div class="connections_layout_result" infinite-scroll="load_more()">
			<div class="ro_preview" ng-repeat="doc in results.docs">
				<div class="ro_preview_header" >
					<div class="title connection_preview_link"><i class="portal-icon portal-icon-{{doc.class}}"></i>
						{{doc.title | truncate:160}}
						<span class="relations">({{relations[doc.id].related_relation | relationship:relations[doc.id].related_class}})</span>
					</div>
					<div class="clear"></div>
				</div>
				<div class="ro_preview_description hide">
					<span ng-bind-html-unsafe="doc.description | removeHtml | truncate:320"></span>
					<div class="ro_preview_footer">
						<a href="<?php echo base_url(); ?>{{doc.slug}}">View Full Record</a>
					</div>
				</div>
			</div>
			<a href="" ng-click="load_more()" ng-show="!loading && !done" class="load_more">Load More Results</a>
		</div>
	</div>

	<div class="connections_layout_facet">
		<div ng-repeat="f in facet" class="widget facet_group" ng-show="f.facet_type!='class'">
			<h3 class="widget-title">{{f.label}}</h3>
			<ul>
				<li ng-repeat="v in f.values | limitTo:facet_limit">
					<a href="" ng-click="select(f.facet_type, v.title)" ng-show="!infacet(f.facet_type, v)">{{v.title}} ({{v.count}})</a>
					<a href="" ng-click="deselect(f.facet_type)" ng-show="infacet(f.facet_type, v)"><i class="portal-icon portal-icon-delete"></i>{{v.title}} ({{v.count}})</a>
				</li>
				<li ng-show="facet_limit < f.values.length"><a href="" ng-click="facet_limit = f.values.length">View More</a></li>
			</ul>
		</div>
	</div>

	<div class="clearfix"></div>
	
</div>