<head>
	<meta charset="utf-8">
	{{-- Add title, if defined, or add a default title if not. --}}
	{{-- If this were real Laravel, we could/should/would use the two-argument version of yield. --}}
	@if(isset($this->_sections['title']))
	<title>@yield('title')</title>
	@else
	<title>ANDS Vocabulary Vocabs</title>
	@endif
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta content="yes" name="apple-mobile-web-app-capable">
	@if(get_config_item('environment_name'))
	<meta property="og:site_name" content="{{ get_config_item('environment_name') }}" />
	@else
	<meta property="og:site_name" content="Research Vocabularies Australia" />
	@endif
	<meta property="og:type" content="article" />
	@if(isset($vocab))
		@if(gettype($vocab) == "array" && isset($vocab['description']))
			{{-- View page --}}
			<?php $description = $vocab['description']; ?>
		@elseif(gettype($vocab) == "object" && isset($vocab->prop))
			{{-- CMS (a.k.a. edit page)--}}
			<?php $description = $vocab->prop['description']; ?>
		@else
			{{-- No idea! --}}
		@endif
	@endif
	@if(isset($description))
		<?php
			$clean_description = htmlspecialchars(substr(str_replace(array('"','[[',']]'), '', $description), 0, 200));
		?>
		<meta ng-non-bindable property="og:description" content="{{ $clean_description }}" />
	@else
		<meta ng-non-bindable property="og:description" content="Find, access, and re-use vocabularies for research" />
	@endif
	{{-- Add Additional Facebook metadata, if any. --}}
	@yield('og-meta')
	{{-- If more metadata needed, insert more yields here. --}}
	@include('includes/styles')
	<script type="text/javascript">
		//Fix Facebook return URL
		if (window.location.hash && window.location.hash == '#_=_') {
			if (window.history && history.pushState) {
				window.history.pushState("", document.title, window.location.pathname);
			} else {
				// Prevent scrolling by storing the page's current scroll offset
				var scroll = {
					top: document.body.scrollTop,
					left: document.body.scrollLeft
				};
				window.location.hash = '';
				// Restore the scroll offset, should be flicker free
				document.body.scrollTop = scroll.top;
				document.body.scrollLeft = scroll.left;
			}
		}
	</script>
	@include('includes/scripts')
</head>

<!-- Environment Indicator -->
@if(ENVIRONMENT!='production')
	<div class="environment" style="background:{{get_config_item('environment_colour')}};color:white;padding:5px 10px;">
		<h3>{{get_config_item('environment_name')}} - {{ENVIRONMENT}}</h3>
	</div>
@endif