<?php $this->load->view('rda_header');?>
<div class="container less_padding" ng-app="portal_theme">
	<div class="breadcrumb">
		<?php echo anchor('/', 'Home', array('class'=>'crumb')); ?> / 
		<?php echo anchor('/theme_page/view/'.$page['slug'], ' '.$page['title'], array('class'=>'crumb')); ?>
	</div>
	<div class="main item-view-inner" ng-controller="init">
		<div class="page-title" id="pageTitle"><h1><?php echo $page['title']; ?></h1></div>
		
		<div class="post clear">
			<input type="hidden" id="slug" value="<?php echo $page['slug']; ?>">
			<?php 
				$data = array(
					'title'=>'Main Content',
					'region'=>'left'
				);
				$this->load->view('theme_page_content', $data);
			?>
		</div>
	</div>
	<div class="sidebar">
		<?php 
			$data = array(
				'title'=>'Side Bar',
				'region'=>'right'
			);
			$this->load->view('theme_page_content', $data);
		?>
	</div>
	<div class="container_clear"></div>
</div>

<script type="text/x-mustache" id="search-result-template">
{{#has_result}}
	<div class="tabs hide">
		<a href="<?php echo portal_url('search'); ?>#!/{{filter_query}}">All</a>
		{{#tabs}}
			<a href="<?php echo portal_url('search'); ?>#!/{{filter_query}}class={{inc_title}}" {{#current}}class="current"{{/current}}>{{title}}</a>
		{{/tabs}}
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
	<a href="<?php echo portal_url('search');?>#!/{{filter_query}}">View Full Search ({{numFound}} results)</a>
{{/has_result}}

{{#no_result}}
	There are no result for this search!
{{/no_result}}
</script>

<script type="text/x-mustache" id="facet-template">
<div class="widget facet_{{facet_type}}">
	<ul class="facet">
		{{#values}}
			<li><a href="<?php echo portal_url('search');?>#!/{{filter_query}}{{facet_type}}={{inc_title}}" class="filter" filter_type="{{facet_type}}" filter_value="{{title}}">{{title}} ({{count}})</a></li>
		{{/values}}
	</ul>
</div>
</script>

<script type="text/x-mustache" id="list_ro-template">
<div class="widget">
	<ul>
		{{#ros}}
			<li class="preview_connection"><a href="<?php echo portal_url();?>{{slug}}" slug="{{slug}}">{{title}}</a></li>
		{{/ros}}
	</ul>
</div>
</script>

<script type="text/x-mustache" id="relation-template">
{{#connections}}
	<p class="{{class}} preview_connection"><a href="<?php echo portal_url(); ?>{{slug}}" slug="{{slug}}" relation_type="{{relation_type}}" relation_description="{{relation_description}}" relation_url="{{relation_url}}">{{title}}</a></p>
{{/connections}}
{{#more}}
	<a href="javascript:;" class="view_all_connection" relation_type="{{type}}" ro_slug="{{slug}}" ro_id="">View All {{count}} Collections</a>
{{/more}}
</script>
<?php $this->load->view('rda_footer');?>