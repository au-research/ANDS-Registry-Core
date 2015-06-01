<section class='section swatch-gray'>
	<div class="container element-short-bottom element-short-top">
		<div class="row">
			
			<div class="col-md-4 col-lg-3 sidebar search-sidebar">
				<h3>Subjects</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.subjects">
						<a href="" ng-click="toggleFilter('subjects', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a>
						<a href="" ng-click="toggleFilter('subjects', facet.name, true)" ng-if="isFacet('subjects',facet.name)"><i class="fa fa-remove"></i></a>
					</li>
				</ul>
				<h3>Language</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.language">
						<a href="" ng-click="toggleFilter('language', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a>
						<a href="" ng-click="toggleFilter('language', facet.name, true)" ng-if="isFacet('language',facet.name)"><i class="fa fa-remove"></i></a>
					</li>
				</ul>
				<h3>License</h3>
				<ul class="list-unstyled">
					<li ng-repeat="facet in facets.licence">
						<a href="" ng-click="toggleFilter('licence', facet.name, true)">[[ facet.name ]] ([[facet.value]])</a>
						<a href="" ng-click="toggleFilter('licence', facet.name, true)" ng-if="isFacet('licence',facet.name)"><i class="fa fa-remove"></i></a>
					</li>
				</ul>
			</div>

			<div class="col-md-8 col-lg-9">

				<div ng-repeat="result in result.response.docs" class="animated fadeInLeft vocab-search-result">
					<h3><a href="[[ base_url ]][[ result.slug ]]">[[ result.title ]]</a></h3>
					<p>[[ result.description ]]</p>
				</div>

				<div ng-if="result.response.numFound == 0" class="animated fadeInLeft vocab-search-result">
					There are no result!
				</div>
			</div>
			
		</div>

	</div>
</section>