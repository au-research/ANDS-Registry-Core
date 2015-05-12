<section class='section element-short-bottom swatch-white' ng-controller="searchCtrl">
	<div class="container">
		<div class="row">
			<div class="col-md-12 swatch-white">
				<input type="text" ng-model="query"/>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">

				<div ng-repeat="vocab in vocabs | filter:query" class="well animated fadeInLeft">
					<h3>[[ vocab.prop.title ]]</h3>
					<p>[[ vocab.prop.description ]]</p>
				</div>

			</div>	
		</div>

	</div>
</section>