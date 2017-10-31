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
            $content .= '<h4>'.readable($right['type']).'</h4>';
            $content .= '<p>'.$right['value'].'</p>';
        }
    }
}

?>
<div class="panel panel-primary swatch-white">
    <div class="panel-body">
        <div class="center-block" style="text-align:center">
            <div ng-if="ro.stat">
                <span style="padding-right:4px;" tip="This page has been viewed [[ro.stat.viewed]] times.<br/><span style='font-size:0.8em'>Statistics collected since April 2015</span>"><small>Viewed: </small>[[ro.stat.viewed]]</span>
               <!-- <a href="#" style="padding-right:4px;"><small>Accessed: </small>[[ro.stat.accessed]]</a> -->
            </div>
        </div>
        
    	<div class="btn-group btn-group-justified element element-short-bottom element-short-top" role="group" aria-label="...">
            <!--<a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> Cite</a>
            <a class="btn btn-sm btn-default"><i class="fa fa-cloud-download"></i> Export</a> -->
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="ro.bookmarked"><i class="fa fa-bookmark"></i> Saved to MyRDA</a>
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="!ro.bookmarked"><i class="fa fa-bookmark-o"></i> Save to MyRDA</a>
           <!-- <a class="btn btn-sm btn-default"><i class="fa fa-print"></i> Print</a> -->
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
        <div class="panel-tools">
            @include('registry_object/contents/social-sharing')
        </div>
        
    </div>
</div>