<?php
    //preparation
    $cc = false;
    $ar = false;
    $detail = false;
    if ($ro->rights) {
        foreach($ro->rights as $right) {
            if ($right['type']=='licence' && $right['licence_type']=='CC-BY') {
                $cc = $right['value'];
            } elseif ($right['type']=='accessRights') {
                if(isset($right['accessRights_type'])){
                    $ar = $right['accessRights_type'];
                }
                $detail=true;
            } else {
                $detail = true;
            }
        }
        if ($detail) {
            $content = '';
            foreach ($ro->rights as $right) {
                $itemprop = '';
                if($right['type']=='licence') $itemprop = 'itemprop="license"';
                $content .= '<h4>'.readable($right['type']).'</h4>';
                $content .= '<p '.$itemprop.'>'.$right['value'].'</p>';
            }
        }
    }

?>
<div class="panel swatch-white gray-bg">
    <div class="panel-body">
        @include('registry_object/contents/access')
    	<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="ro.bookmarked"><i class="fa fa-bookmark"></i> Saved to MyRDA</a>
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="!ro.bookmarked"><i class="fa fa-bookmark-o"></i> Save to MyRDA</a>
        </div>

        <div>
            @if($ar || $cc || $detail)
                <h4>Licence & Rights</h4>
            @endif
        	@if($ar=='open')
        	    <a href="" tip="Data that is readily accessible and reusable"><span class="label  label-{{$ar}}" for="">OPEN</span></a>
        	@elseif($ar=='conditional')
                <a href="" tip="Data that is accessible and reusable, providing certain conditions are met (e.g. free registration is required)"><span class="label label-{{$ar}}" for="">CONDITIONAL</span></a>
            @elseif($ar=='restricted')
                <a href="" tip="Data access is limited in some way (e.g. only available to a particular group of users or at a specific physical location)"><span class="label label-{{$ar}}" for="">RESTRICTED</span></a>
            @endif

        	@if($cc=='CC-BY')
        	    <a href="http://creativecommons.org/licenses/by/3.0/au/" tip="Attribution"><img src="{{asset_url('images/icons/CC-BY.png', 'core')}}" class="img-cc" alt="CC-BY"></a> <br/>
            @elseif($cc=='CC-BY-SA')
                <a href="http://creativecommons.org/licenses/by-sa/3.0/au/" tip="Attribution-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-SA.png', 'core')}}" class="img-cc" alt="CC-BY-SA"></a> <br/>
            @elseif($cc=='CC-BY-ND')
                <a href="http://creativecommons.org/licenses/by-nd/3.0/au/" tip="Attribution-No Derivatives"><img src="{{asset_url('images/icons/CC-BY-ND.png', 'core')}}" class="img-cc" alt="CC-BY-ND"></a> <br/>
            @elseif($cc=='CC-BY-NC')
                <a href="http://creativecommons.org/licenses/by-nc/3.0/au/" tip="Attribution-Non Commercial"><img src="{{asset_url('images/icons/CC-BY-NC.png', 'core')}}" class="img-cc" alt="CC-BY-NC"></a> <br/>
            @elseif($cc=='CC-BY-NC-SA')
                <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/au/" tip="Attribution-Non Commercial-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-NC-SA.png', 'core')}}" class="img-cc" alt="CC-BY-NC-SA"></a> <br/>
            @elseif($cc=='CC-BY-NC-ND')
                <a href="http://creativecommons.org/licenses/by-nc-nd/3.0/au/" tip="Attribution-Non Commercial-Non Derivatives"><img src="{{asset_url('images/icons/CC-BY-NC-ND.png', 'core')}}" class="img-cc" alt="CC-BY-NC-ND"></a> <br/>
            @else 
                <span>{{$cc}}</span>
        	@endif

        	@if($detail)
        	   <a href="javascript:;" id="toggleRightsContent">View details</a>
        	@endif
        </div>
        
        @if($detail)
            <div id="rightsContent">
                @if($content) {{$content}} @endif
            </div>
        @endif

            @include('registry_object/contents/contact-info')


    </div>
</div>