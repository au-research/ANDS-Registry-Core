<section class='section element-short-bottom element-short-top swatch-white'>
	<div class="container">
		<div class="row">
			

			<div class="col-md-8 col-lg-9">
				<div ng-repeat="result in result.response.docs" class="well animated fadeInLeft">
					<h3><a href="[[ base_url ]][[ result.slug ]]">[[ result.title ]]</a></h3>
					<p>[[ result.description ]]</p>
				</div>
			</div>

			<div class="col-md-4 col-lg-3 sidebar search-sidebar">
				<h3>Subjects</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.subjects">
						<a href="" ng-click="toggleFilter('subjects', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a>
						[[ isFacet('subjects',facet.name) ]]
					</li>
				</ul>
				<h3>Language</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.language">
						<a href="" ng-click="toggleFilter('language', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a>
						[[ isFacet('language',facet.name) ]]
					</li>
				</ul>
				<h3>License</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.licence"><a href="" ng-click="toggleFilter('licence', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a></li>
				</ul>
			</div>
			
		</div>

	</div>
</section>