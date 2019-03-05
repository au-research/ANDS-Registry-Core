<?php
	$url = base_url().$ro->core['slug'].'/'.$ro->core['id'];
	$title = $ro->core['title'];
    $id = $ro->core['id'];

    $params = [
        'url' => $url,
        'title' => $title,
        'id' => $id
    ];

    $params = http_build_query($params);
?>
<div class="center-block social-sharing" style="text-align:center">
    <a href="javascript:;" onclick="window.print();"><i class="fa fa-print" style="padding-right:4px"></i></a>
    <a href="{{ portal_url('page/share/facebook/?'.$params) }}"><i class="fa fa-facebook" style="padding-right:4px"></i></a>
    <a href="{{ portal_url('page/share/twitter/?'.$params) }}"><i class="fa fa-twitter" style="padding-right:4px"></i></a>
</div>