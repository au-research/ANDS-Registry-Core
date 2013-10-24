<?php $this->load->view('rda_header');?>
<div id="search_loading" class="hide"><h3>Loading...</h3></div>
<div id="search_notice" class="hide"></div>
<div class="container less_padding">
<div id="searchmap" class="hide"></div>
<div class="main">
	<div id="searchmap" class="hide"></div>
	<div class="page_title">
		<h1 id="selected_subject"></h1>
		<h3 id="selected_group"></h3>
	</div>
	<div class="tabs" class="hide">
		<?php
			$tabs = array(
				'All' => array('facet_value'=>'all','display'=>'All','selected'=>true, 'count'=>0),
				'collection' => array('facet_value'=>'collection','display'=>'Collections','selected'=>false, 'count'=>0),
				'party' => array('facet_value'=>'party','display'=>'Parties','selected'=>false, 'count'=>0),
				'activity' => array('facet_value'=>'activity','display'=>'Activities','selected'=>false, 'count'=>0),
				'service' => array('facet_value'=>'service','display'=>'Services','selected'=>false, 'count'=>0)
			);
			
			foreach($tabs as $t){
				if($t['selected']){
					$s = 'current';
				}else $s = '';
				echo "<a href='javascript:;' class='filter icontip_".$t['facet_value'] . " " . $s."' filter_type='class' filter_value=".$t['facet_value'].">".$t['display']."</a>";
			}
		?>
		
		<!-- <a href="javascript:;" class="toggle_sidebar"></a>	 -->
		<div class="clear"></div>
	</div>
	<div id="search-result"></div>
<div id="collection_explanation" class="hide">
	<strong>Collection</strong><br />
	Research dataset or collection of research materials.
</div>
<div id="activity_explanation" class="hide">
	<strong>Activity</strong><br />
	Project or program that creates research datasets or collections.
</div>
<div id="service_explanation" class="hide">
	<strong>Service</strong><br />
	Service that supports the creation or use of research datasets or collections.
</div>
<div id="party_explanation" class="hide">
	<strong>Party</strong><br />
	Researcher or research organisation that creates or maintains research datasets or collections.
</div>
<div id="topic_explanation" class="hide">
	<strong>Topic</strong><br />
	Topics pages collate areas of research interest together, and associate research datasets, people and activities from within the Australian Research Data Commons.
</div>
</div><!-- main -->
<div class="sidebar">
	<h2>Refine Search</h2>
	<div id="togglefacetsort" class="facetsortcount" tip="Search option categories (below) are being sorted by the number of matching records"></div>
	<div class="widget facet_subjects">
	<h3 class="widget_title">Subjects</h3>
	<div id="browse-subjects-container">
		<a href="javascript:;" id="browse-more-subject">Browse More Subjects</a>
	</div>
		<ul id="top_concepts"></ul>
	</div>
	<div id="facet-result"></div>				
</div><!-- sidebar -->				
<div class="container_clear"></div>
<!-- <div class="border"></div> -->

<script type="text/x-mustache" id="search-trunc-template">
{{#trunc}}
{{found}} records match your search query; however, only the top {{returned}} could be displayed on the map. <br/>Please refine your search by drawing a smaller search area (using the box tool <span style="display: inline-block; "><div style="top:3px; width: 16px; height: 16px; overflow: hidden; position: relative; "><img style="position: absolute; left: 0px; top: -16px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px; width: auto; height: auto; " src="https://maps.gstatic.com/mapfiles/drawing.png" draggable="false"></div></span>) or selecting one or more additional search facets. 
{{/trunc}}
</script>

<script type="text/x-mustache" id="search-noterms-template">
Please define a search region using the box tool (<span style="display: inline-block; "><div style="top:3px; width: 16px; height: 16px; overflow: hidden; position: relative; "><img style="position: absolute; left: 0px; top: -16px; -webkit-user-select: none; border: 0px; padding: 0px; margin: 0px; width: auto; height: auto; " src="https://maps.gstatic.com/mapfiles/drawing.png" draggable="false"></div></span>), or specify some search terms.<br/>
Only records which have coverage of the search region will be displayed.
</script>

<script type="text/x-mustache" id="search-result-template">
{{#fuzzy_result}}
	<div class="block-record hide"></div>
	<p class="fuzzy-suggest">Your search for '<b>{{search_term}}</b>' returned 0 results. Below are some alternatives which closely match your query.</p>
{{/fuzzy_result}}
{{#no_result}}
	<div class="block-record hide"></div>
	<p>The search term - <b>{{search_term}}</b> did not return any results.</p>
	<p>Some suggestions for searching in Research Data Australia:</p>
	<ul>
		<li>Make sure that all words are spelled correctly</li>
		<li>Try using different or more general search keywords</li>
	</ul>
{{/no_result}}
{{#has_result}}
	<div class="results_navi">
		<div class="results"><span id="numFound">{{numFound}}</span> results ({{timeTaken}} seconds)</div>
		{{{pagination}}}
		<div class="clear"></div>
	</div>
	{{#result.docs}}
		<div class="post clear" ro_id="{{id}}">
			{{#contributor_page}}
			<span class="contributor hide" slug="{{slug}}">{{contributor_page}}</span>
			{{/contributor_page}}
			{{#logo}}
				<img src="{{logo}}" class="logo right"/>
			{{/logo}}
			{{#class}}
				<img src="<?php echo base_url();?>assets/img/{{class}}.png" class="class_icon icontip_{{class}}" type="{{class}}"/>
		    {{/class}}
			{{#list_title}}
				<a href="<?php echo base_url();?>{{slug}}" class="title">{{list_title}}</a>
			{{/list_title}}
			{{#description}}
				<div class="excerpt">
				  {{description}}
				</div>
		        {{/description}}
		</div>
	{{/result.docs}}
	<div class="results_navi">
		<div class="results"><span id="numFound">{{numFound}}</span> results ({{timeTaken}} seconds)</div>
		{{{pagination}}}
		<div class="clear"></div>
	</div>
{{/has_result}}
</script>

<script type="text/x-mustache" id="pagination-template">

</script>

<script type="text/x-mustache" id="facet-template">
{{#facet_result}}
<div class="widget facet_{{facet_type}}">
	<h3 class="widget_title">{{label}}</h3>
	<ul>
		{{#values}}
			<li><a href="javascript:;" class="filter" filter_type="{{facet_type}}" filter_value="{{title}}">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
{{/facet_result}}
</script>

<script type="text/x-mustache" id="top-level-template">
{{#topConcepts}}
	<li><a href="javascript:;" class="filter" filter_type="subject_vocab_uri" filter_value="{{uri}}">{{prefLabel}} ({{collectionNum}})</a></li>
{{/topConcepts}}
</script>

</div>
<?php $this->load->view('rda_footer');?>