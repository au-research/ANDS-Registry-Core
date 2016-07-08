<?php
	$url = base_url().$ro->core['slug'].'/'.$ro->core['id'];
	$title = $ro->core['title'];
?>
<div class="center-block social-sharing" style="text-align:center">
    <a href="javascript:;" onclick="window.print();"><i class="fa fa-print" style="padding-right:4px"></i></a>
    <a href="{{ portal_url('page/share/facebook/?url='.$url) }}"><i class="fa fa-facebook" style="padding-right:4px"></i></a>
    <a href="{{ portal_url('page/share/twitter/?url='.$url.'&title='.$title) }}"><i class="fa fa-twitter" style="padding-right:4px"></i></a>
   	<a href="{{ portal_url('page/share/google/?url='.$url) }}"><i class="fa fa-google" style="padding-right:4px"></i></a>
</div>