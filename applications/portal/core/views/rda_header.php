<!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
<meta charset="UTF-8" />
<title><?php
		echo (isset($title) ? $title : 'Research Data Australia');
	?></title>

<?php if(isset($title)): ?>
	<meta property="og:title" content="<?=ellipsis($title, 128);?>"/>
<?php endif; ?>

<link rel="stylesheet" href="<?php echo asset_url('css/knacss.css', 'core');?>" type="text/css" media="screen" />


<!-- Zoo Stylesheets Untouched -->
<link rel="stylesheet" href="<?php echo asset_url('style.css','core');?>" type="text/css"/>
<link rel="stylesheet" href="<?php echo asset_url('print.css','core');?>" type="text/css" media="print" />
<link rel="stylesheet" href="<?php echo asset_url('css/ands-theme/jquery-ui-1.10.0.custom.min.css', 'core');?>" type="text/css" media="screen" />

<link rel="stylesheet" href="<?php echo asset_url('css/flexslider.css', 'core');?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo asset_url('css/ui.dynatree.css', 'core');?>" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/vocab_widget/css/vocab_widget.css')?>"  media="screen" />
<!-- ANDS Less file and general styling correction-->
<link href="<?php echo asset_url('less/ands.less', 'core');?>" rel="stylesheet/less" type="text/css">

<!-- Library files -->
<link rel="stylesheet" href="<?php echo asset_url('lib/qtip2/jquery.qtip.min.css', 'base');?>" type="text/css">

<link rel="stylesheet" href="<?php echo asset_url('lib/jQRangeSlider/css/iThing.css', 'base');?>" type="text/css" media="screen" > 

<?php if(isset($the_description)): ?>
<meta name="description" content="<?=ellipsis($the_description,512); ?>"/>
<meta property="og:description" content="<?=ellipsis($the_description,512); ?>"/>
<?php endif; ?>

<?php if(isset($the_title)): ?>
<meta name="keywords" content="<?php echo $the_title; ?>"/>
<?php endif; ?>
</head>
<?php
if($this->config->item('environment_name'))
{
  $environment_name = $this->config->item('environment_name');
  $environment_colour = ($this->config->item('environment_colour') ?: "#0088cc");
  $environment_header_style = " style='border-top: 4px solid " . $environment_colour . ";'";
}
else
{
	$environment_name = '';
  	$environment_colour = '';
  	$environment_header_style = '';
}
?>
<body>
	<div class="header" <?=$environment_header_style;?>>
		<div class="head">
			<div class="tagline">
				<a href="<?php echo base_url();?>"><span>Research Data</span> Australia</a>
				<?php
					if ($environment_name)
					{
						echo "<small style='font-size:0.5em; color:". $environment_colour ."'>" . $environment_name . "</small>";
					}
				?>
			</div><!-- tagline -->
			<a href="<?php echo "http://www.ands.org.au/"; ?>" target="_blank" class="logo"><img src="<?php echo asset_url('images/logo.png','core');?>" alt="Research Data Australia Home Page Link (brought to you by ANDS)" /></a>
			<ul class="top_nav">
				<li><a href="<?=base_url("home/about");?>">About</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=collection');?>">Collections</a></li>								
				<li><a href="<?=base_url('search/#!/q=/tab=party');?>">Parties</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=activity');?>">Activities</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=service');?>">Services</a></li>
				<li><a href="<?=base_url("topic");?>">Topics</a></li>
			</ul><!-- top_nav -->
			<div class="clear"></div>
		</div><!-- head -->
	</div><!-- header -->
	<div class="search">
		<div class="inner">
			<img src="<?php echo base_url('assets/core/images/search_icon_hover.png');?>" id="searchTrigger" tip="Click me to search!"/>
			<input type="text" id="search_box" name="s" value="" placeholder="Search for Research Data"/>
			<img src="<?php echo base_url('assets/core/images/delete.png');?>" class="clearAll" tip="Clear current search"/>
			<a class="browse_button" href="<?php echo base_url('browse');?>">Browse by Subject Area</a>
			<a href="javascript:;" class="search_map" id="search_map_toggle">Browse by Map Coverage</a>
			<div class="clear" style="margin-left:312px; padding-bottom:4px;"><a href="#" id="ad_st">Advanced Search</a></div>
			<!--div class="clear buttons">
				<a href="#" id="ad_st">Advanced Search</a>
			</div-->
		</div><!-- inner -->

		<div class="advanced_search">
		    <div id="adv_note_content" class="hide">
			  <p>Selecting "Collections", "Activities", "Services", or "Parties" from the drop-down box will restrict the search to records of this class.</p>
			  <p>A search string within the field “All of these words”, will return records containing all words entered into this field.</p>
			  <p>A search string within the field “One or more of these words”, will return records containing any of the entered words within this field.</p>
			  <p>Words entered into any or all of the three adjacent fields, “But not these words”, will ensure that records containing these words will not be returned. These fields should be used in conjunction with the preceding fields “All of these words” and/or  “One or more of these words”.</p>
			  <p>Selecting “Restrict Temporal Range” and defining a date range within the slider bar, will return records that only contain a temporal coverage within the specified range.</p>
			</div>
			<div class="ad_close"><a href="#">x</a></div>
			<div class="adv_inner">
				<form action="/" method="post">
					<p>Find  
						<select id="record_tab" name="record">
							<option value="collection">Collections</option>
							<option value="party">Parties</option>
							<option value="activity">Activities</option>
							<option value="service">Services</option>
							<option value="all">All Records</option>
						</select>
					   that have
					   <a href="#" class="adv_note">
					     <img src="<?php echo asset_url('images/question_mark.png', 'core');?>" style="position:relative;top:-8px;width:16px"/>
					   </a>:
					</p>
					<div class="inputs">
						<label for="words">This exact phrase:</label>
						<input type="text" name="words" class="adv_all b_inputs" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="more_words">One or more of these words:</label>
						<input type="text" name="more_words" class="adv_input b_inputs" /> 
					</div><!-- inputs -->	
					<div class="inputs">
						<label for="words_ex">But not these words:</label>
						<input type="text" name="words_ex" id="words_ex" class="s_inputs adv_not" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs adv_not" /> 
						<span class="or">OR</span>
						<input type="text" name="words_ex" class="s_inputs adv_not" /> 						
					</div><!-- inputs -->
					<div class="range_slider_wrap">
						<p><input type="checkbox" name="rst_range" id="rst_range" value="1" /><label for="rst_range">Restrict temporal range</label></p>
						<p><br/></p>
						<div id="slider"></div>
						<!-- <div id="range_slider"></div> -->
					</div><!-- range_slider -->	
					<div class="sbuttons">
						<input type="submit" value="Start Search" id="adv_start_search"/> 	
						<a href="#" id="clear_search">Clear Search</a>
					</div>
				
				</form>	
				<div class="clear"></div>			
			</div><!-- adv_inner -->
		</div><!-- advanced_search -->
	</div><!-- search -->
