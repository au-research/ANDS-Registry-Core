<?php 
	$url = base_url().$ro->core['slug'].'/'.$ro->core['id'];
	$title = $ro->core['title'];
?>
<div class="center-block" style="text-align:center">
    <a href="http://www.facebook.com/sharer.php?u={{$url}}"><i class="fa fa-facebook" style="padding-right:4px"></i></a>
    <a href="https://twitter.com/share?url={{$url}}&text={{$title}}&hashtags=andsdata"><i class="fa fa-twitter" style="padding-right:4px"></i></a>
   	<a href="https://plus.google.com/share?url={{$url}}"><i class="fa fa-google" style="padding-right:4px"></i></a>
</div>