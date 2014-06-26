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

	<link rel="stylesheet" href="<?php echo asset_url('ands_portal.combined.css', 'core'); ?>" type="text/css" media="screen">
	<link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/vocab_widget/css/vocab_widget.css')?>"  media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/lib/font-awesome-4.1.0/css/font-awesome.min.css');?>" />
	<link rel="stylesheet" href="<?php echo asset_url('print.css','core');?>" type="text/css" media="print" />

	<link rel="shortcut icon" href="<?php echo base_url('favicon.ico');?>"/>

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
	<?php if ($versionInfo = codeVersionInfo()): 
		echo '<div class="codeversion"><img src="'. asset_url('images/info.png','core').'" /> '.$versionInfo.'</div>';
	endif; ?>
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
				<li><a href="<?=base_url("themes");?>">Themes</a></li>
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

			<?php 
				if(config_item('oauth_config')) {
					$logged_in = oauth_loggedin();
					if($logged_in) {
						$user = oauth_getUser();
						if($user) {
							echo '<a class="login_st" tip="Logged in as '.$user['profile']->displayName.'">';
							echo '<img src="'.$user['profile']->photoURL.'" class="social_profile_pic"/>';
							echo '</a>';
						}
					} else {
						echo '<a class="login_st" tip="Login to Research Data Australia"><img class="social_profile_pic" src="'.asset_url('images/social_login.png', 'core').'" /></a>';
					}
				}
			?>

			<div class="clear" style="margin-left:312px; padding-bottom:4px;">
				<a href="#" id="ad_st">Advanced Search</a>
			</div>
			
		</div><!-- inner -->

		<div class="login_banner">
			<div class="login_close"><a href="#">x</a></div>
			<div class="adv_inner">
				<?php if(config_item('oauth_config')): ?>
				<?php if(!$logged_in): ?>

				<div style="width:50%;float:left;padding:0 15px;">
					<p style="font-weight:normal;">By logging into Research Data Australia, you will have access to additional features including the ability to contribute to the Research Data Australia community by adding tags (keywords) to records.</p>
					<p style="font-weight:normal;font-size:10px;">Research Data Australia. <?php echo anchor('home/privacy_policy', 'Privacy Policy');?></p>
				</div>
				<div>
					<?php
						$oauth_conf = $this->config->item('oauth_config');
					?>
					<p><?php if($oauth_conf['providers']['Facebook']['enabled']) echo anchor('auth/login/Facebook/?redirect='.current_url(),'Login With Facebook', array('class'=>'zocial facebook')); ?></p>
					<p><?php if($oauth_conf['providers']['Twitter']['enabled']) echo anchor('auth/login/Twitter/?redirect='.current_url(),'Login With Twitter', array('class'=>'zocial twitter')); ?></p>
					<p><?php if($oauth_conf['providers']['Google']['enabled']) echo anchor('auth/login/Google/?redirect='.current_url(),'Login With Google', array('class'=>'zocial google')); ?></p>
					<p><?php if($oauth_conf['providers']['LinkedIn']['enabled']) echo anchor('auth/login/LinkedIn/?redirect='.current_url(),'Login With LinkedIn', array('class'=>'zocial linkedin')); ?></p>
				</div>
				<div class="clearfix"></div>

				<?php else: ?>
					<p>Logged in as <b><?php echo $user['profile']->displayName; ?></b></p>
					<p style="margin:10px 0px 5px 0px;"><?php echo anchor('auth/logout/?redirect='.current_url(),'Log Out of Research Data Australia', array('style'=>'color: #fff;font-size: 16px;font-weight: bold;background: #f58000;border: none;padding: 11px 15px;margin:10px 0')); ?></p>
					<p style="font-weight:normal;font-size:12px;">Please note that logging out of Research Data Australia will not log you out of <?php echo $user['service'];?></p>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

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
