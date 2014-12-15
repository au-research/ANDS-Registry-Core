<div class="container-fluid">
	<div class="pull-left">
		<small>[[result.response.numFound]] Results found ([[result.responseHeader.QTime]] milliseconds)</small>
	</div>
	
	<nav class="pull-right">
	  <ul class="pagination pagination-sm" style="margin:0 auto;">
	    <li><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>
	    <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
	    <li><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>
	  </ul>
	</nav>
</div>

