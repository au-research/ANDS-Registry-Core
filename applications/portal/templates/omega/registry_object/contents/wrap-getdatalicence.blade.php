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
    	<a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data</a>
    	<div id="dataformats">
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span>CSV</span>File name (7 KB)</a>
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span>CSV</span>File name</a>
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span>ZIP</span>File name</a>
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span>RAR</span>File name</a>
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span>XML</span>Some long filename ...(102 MB)</a>
    		<a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top"><span><i class="fa fa-globe"></i></span>A Cage Aquaculture...(CADS_TOOL)</a>
		</div>

    	<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
            <a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> Cite</a>
            <a class="btn btn-sm btn-default" ng-click="bookmark()"><i class="fa fa-bookmark-o"></i> Bookmark</a>
        </div>
        <div class="center-block" style="text-align:center">
        	@if($ar)
        	    <span class="label label-info label-{{$ar}}" for="">{{$ar}}</span>
        	@endif
        	@if($cc)
        	    <img src="{{asset_url('images/icons/'.$cc.'.png', 'core')}}" class="img-cc" alt="{{$cc}}">
        	@endif
        	@if($detail)
        	    <br/><a href="javascript:;" id="toggleRightsContent">View details</a>
        	@endif
        </div>
        
        @if($detail)
            <div id="rightsContent">
                @if($content) {{$content}} @endif
            </div>
        @endif

        
    </div>
</div>