<?php
    //preparation
    $cc = false;
    $ar = false;
    $detail = false;
    if ($ro->rights) {
        foreach($ro->rights as $right) {
            if ($right['rights_type']=='licence' && $right['type']=='CC-BY') {
                $cc = $right['value'];
            } elseif ($right['rights_type']=='accessRights') {
                $ar = $right['type'];
            } else {
                $detail = true;
            }
        }
        if ($detail) {
            $content = '';
            foreach ($ro->rights as $right) {
                $content .= '<h4>'.readable($right['rights_type']).'</h4>';
                $content .= '<p>'.$right['value'].'</p>';
            }
        }
    }
?>
<div class="panel panel-primary swatch-white">
    <div class="panel-body">
    	<div class="container-fluid" style="padding-left:0">
    		<div class="row element element-short-bottom">
    			<div class="col-md-6">
    				<a href="" class="btn btn-lg btn-primary btn-block">Get Data @if($ar)
    				    <span class="label label-info label-{{$ar}}" for="">{{$ar}}</span>
    				@endif</a>
    			</div>
    			<div class="col-md-6">
    				@if($cc)
    				    <img src="{{asset_url('images/icons/'.$cc.'.png', 'core')}}" class="img-cc-standard3" alt="{{$cc}}">
    				@endif
    			</div>
    		</div>
    		<div class="row">
    			<div class="col-md-6">
    				@if($detail)
    				   @if($content) {{$content}} @endif
    				@endif
    			</div>
    			<div class="col-md-6">
    				@include('registry_object/contents/contact-info')
    			</div>
    		</div>
    	</div>
    </div>
</div>