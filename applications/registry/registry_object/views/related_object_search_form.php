<div class="input-append">
	<input type="text" class="input input_search_related" placeholder="Search Term"/>
	<button type="button" class="btn btn-primary search_related">Search</button>
	<button type="button" class="btn btn-link show_advanced_search_related">Advanced</button>
</div>
<div id="advanced" class="hide">
	<label class="checkbox">
		<input type="checkbox" id="ds_option" value=""> Only in this data source
	</label>
	<label class="checkbox">
		<input type="checkbox" id="published_option" value=""> Only Published Records
	</label>
	<select id="class_related_search_option">
		<option value="all">All Classes</option>
		<option value="collection">Collections</option>
		<option value="party">Parties</option>
		<option value="activity">Activities</option>
		<option value="service">Services</option>
	</select>
</div>
<div id="result"></div>