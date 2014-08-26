<?php $this->load->view('rda_header');?>
<a href="#test-popup" class="open-popup-link hide">Show inline popup</a>
<div id="test-popup" class="white-popup mfp-with-anim mfp-hide">
	<img src="<?php echo asset_url('img/ands-full.png','base')?>" alt="" style="float:right;margin:0 20px;">
  <h4> Help us to improve Research Data Australia. </h4>
  <p>
  	We are currently considering a redesign of the Research Data Australia website. We are seeking your input to help us improve our website and provide the information you want. If you are willing to provide us with some of your time to help us understand how you believe content on this website should be organised, then please contact the ANDS Service Desk	<a href="mailto:services@ands.org.au">services@ands.org.au</a> with your contact details and availability from 1 September.
  </p>
  <p>
  	<a href="javascript:;" id="nothanks" class="yellow_button">No Thanks!</a>
  </p>
</div>

<div class="container">
	<div class="intro">
		<h3>What’s in Research Data Australia</h3>
		
		<a href="<?=base_url('search/#!/q=/class=collection');?>">
			<div class="intro_box">
				<div class="intro_inner" id="collection_icon">
					<h4>Collections <span>(<?php echo $collection;?>)</span></h4>
					Research datasets or collections of research materials.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/class=party');?>">
			<div class="intro_box">
				<div class="intro_inner" id="party_icon">
					<h4>Parties <span>(<?php echo $party;?>)</span></h4>
					Researchers or research organisations that create or maintain research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/class=activity');?>">
			<div class="intro_box">
				<div class="intro_inner" id="activity_icon">
					<h4>Activities <span>(<?php echo $activity;?>)</span></h4>
					Projects or programs that create research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

		<a href="<?=base_url('search/#!/q=/class=service');?>">
			<div class="intro_box">
				<div class="intro_inner" id="service_icon">
					<h4>Services <span>(<?php echo $service;?>)</span></h4>
					Services that support the creation or use of research datasets or collections.
				</div><!-- intro_inner -->
			</div>
		</a><!-- intro_box -->

	</div><!-- intro -->
	<div class="right">
		<h3>Spotlight on research data</h3>
		<div class="flexslider" id="spotlight">
		</div><!-- flexslider -->
		<div class="clear"></div>
		<h3>Who contributes to Research Data Australia?</h3>
		<p><a href="<?=base_url('home/contributors');?>" id=""><strong><?php echo sizeof($groups);?> research organisations</strong></a> from around Australia contribute information to Research Data Australia.</p> 			
		<a href="<?=base_url('home/contributors');?>" id=""><strong>See All</strong></a>
		<p></p>
		<!-- AddThis Button BEGIN -->
		<div class="addthis_toolbox addthis_default_style ">
			<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			<a class="addthis_button_tweet"></a>
			<a class="addthis_counter addthis_pill_style"></a>
		</div>
		<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-521ea5093dcee175&async=1"></script>
		<!-- AddThis Button END -->
		<a href="javascript:;" class="sharing_widget">Share</a>
	</div>
	<div class="clear"></div>

	
	<!--div class="social">
		<a href="feed/rss"><img src="<?php echo asset_url('images/rss.png','core');?>" alt="" /></a><a href="https://twitter.com/andsdata"><img src="<?php echo asset_url('images/twitter.png','core');?>" alt="" /></a>
	</div-->


<script type="text/x-mustache" id="spotlight_template">
<div class="pauseicon"></div>
	<ul class="slides">
	{{#items}}
		<li>
			<img src="{{img_url}}" {{#img_attr}}title="{{img_attr}}" alt="{{img_attr}}"{{/img_attr}} />
			<a href="{{url}}" class="title">{{{title}}}</a>
			<div class="excerpt">
				{{{description}}}
				<p><a {{#new_window}}target="_blank"{{/new_window}} href="{{{url}}}"><strong>{{#url_text}}{{url_text}}{{/url_text}}{{^url_text}}{{url}}{{/url_text}}</strong></a></p>
			</div>
			
		</li>
	{{/items}}
	</ul>
</script>
</div>
<?php $this->load->view('rda_footer');?>