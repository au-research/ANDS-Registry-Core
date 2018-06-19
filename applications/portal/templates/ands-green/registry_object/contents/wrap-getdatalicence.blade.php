<?php
    //preparation

    $ar = false;
    $access_detail = false;
    $access_content = '';

    $cc = false;
    $licence_detail = false;
    $licence_group = false;

    $display_order = array("licence","rightsStatement");

    if ($ro->rights) {
        foreach($ro->rights as $right) {
            if ($right['type']=='licence' && $right['licence_type']!='') {
                $cc = $right['licence_type'];
                $licence_detail = true;
                $licence_group = $right['licence_group'];

                $thegroup = strtolower($right['licence_group']);
                if($thegroup=='unknown') $licence_group='Other';

                if(isset($right['rightsUri']) && $right['rightsUri']!=''){
                   $cc_uri = $right['rightsUri'];
                }else{
                    $cc_uri = '';
                }
                if(isset($right['value'])) {
                    if($right['value']!='') $licence_detail = true;
                }

                if(isset($right['rightsUri'])) {
                    if($right['rightsUri']!='') $licence_detail = true;
                }

            }
            elseif ($right['type']=='rightsStatement') {
                if(isset($right['accessRights_type'])){
                    $ar = $right['accessRights_type'];
                }
                if(isset($right['value'])) {
                    if($right['value']!='') $licence_detail = true;
                }

                if(isset($right['rightsUri'])) {
                    if($right['rightsUri']!='') $licence_detail = true;
                }
            }elseif ($right['type']=='accessRights') {
                if(isset($right['accessRights_type'])){
                    $ar = $right['accessRights_type'];
                }
                if(isset($right['value'])) {
                    if($right['value']!='') $access_detail = true;
                }

                if(isset($right['rightsUri'])) {
                    if($right['rightsUri']!='') $access_detail = true;
                }
            }

            if(($right['type']=='licence' && $right['licence_type']=='')||( $right['type']!='licence' && $right['type']!='accessRights')){
                if(isset($right['value'])) {
                    if($right['value']!='') $access_detail = true;
                }

                if(isset($right['rightsUri'])) {
                    if($right['rightsUri']!='') $access_detail = true;
                }
            }

        }
        if ($licence_detail) {
            $licence_content = '';
            foreach($display_order as $order){
                foreach ($ro->rights as $right) {
                    if($right['type']=='licence' && $right['type']==$order){
                         if((isset($right['value']) &&trim($right['value'])!='')||(isset($right['rightsUri']) && $right['rightsUri']!=''))
                            $licence_content .= '<p>';
                        if(isset($right['value']) && trim($right['value'])!=''){
                            $description = html_entity_decode($right['value']);
                            if(strip_tags($description) == $description)
                                $description = nl2br($description);
                            $licence_content .= $description.'<br />';
                        }
                        if(isset($right['rightsUri']) && $right['rightsUri']!='')
                            $licence_content .= '<a href="'.$right['rightsUri'].'">'.$right['rightsUri'].'</a><br />';
                        $licence_content .= '</p>';
                    }
                    if($right['type']=='rightsStatement'&& $right['type']==$order){
                        if((isset($right['value']) &&trim($right['value'])!='')||(isset($right['rightsUri']) && $right['rightsUri']!=''))
                            $licence_content .= '<p>';
                        if(isset($right['value']) && trim($right['value'])!=''){
                            $description = html_entity_decode($right['value']);
                            if(strip_tags($description) == $description)
                                $description = nl2br($description);
                            $licence_content .= $description.'<br />';
                        }
                        if(isset($right['rightsUri']) && $right['rightsUri']!='')
                            $licence_content .= '<a href="'.$right['rightsUri'].'">'.$right['rightsUri'].'</a><br />';
                        $licence_content .= '</p>';
                    }
                }
            }
        }
        if ($access_detail && $access_detail!='') {
            $access_content = '';
            foreach ($ro->rights as $right) {
                if($right['type']!='licence' && $right['type']!='rightsStatement'){
                    if((isset($right['value']) &&trim($right['value'])!='')||(isset($right['rightsUri']) && $right['rightsUri']!=''))
                        $access_content .= '<p>';
                    if(isset($right['value']) && trim($right['value'])!=''){
                        $description = html_entity_decode($right['value']);
                        if(strip_tags($description) == $description)
                            $description = nl2br($description);
                        $access_content .= $description.'<br />';

                    }
                    if(isset($right['rightsUri']) && $right['rightsUri']!='')
                        $access_content .= '<a href="'.$right['rightsUri'].'">'.$right['rightsUri'].'</a><br />';
                    if($access_content!='')
                    $access_content .= '</p>';
                }
            }
        }
    }

?>

<div class="panel panel-primary swatch-white gray-bg">
    <div class="panel-body">
        @if($ro->citations)
            @foreach($ro->citations as $citation)
                @if(isset($citation['coins']))
                <span class="Z3988" ng-non-bindable>
                    {{ str_replace('"','',$citation['coins']) }}
                </span>
                @endif
            @endforeach
        @endif
        @include('registry_object/contents/access')
    	<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
            <a class="btn btn-sm btn-default" ng-click="openCitationModal()"><i class="fa fa-edit"></i> Cite</a>
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="ro.bookmarked"><i class="fa fa-bookmark"></i> Saved to MyRDA</a>
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="!ro.bookmarked"><i class="fa fa-bookmark-o"></i> Save to MyRDA</a>
        </div>

        <div>
            @if( $cc || $licence_group || $licence_detail)
                <h4>Licence & Rights: </h4>
            @endif
            @if($licence_group=='Open Licence')
            <i class="fa fa-check" style="color:forestgreen"></i>
            @endif
            {{$licence_group}}

            @if(isset($licence_content))
            <a href="javascript:;" id="toggleLicenceContent" class="small" style="padding-left:15px;">view details</a>
            @endif

            @if(isset($licence_content))
            <div id="licenceContent">
                @if($cc=='CC-BY')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by/3.0/au/";
                ?>
                <a href="{{$cc_uri}}"><img src="{{asset_url('images/icons/CC-BY.png', 'core')}}" class="img-cc" alt="CC-BY"></a> <br/>
                @elseif($cc=='CC-BY-SA')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by-sa/3.0/au/";
                ?>
                <a href="{{$cc_uri}}" tip="Attribution-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-SA.png', 'core')}}" class="img-cc" alt="CC-BY-SA"></a> <br/>
                @elseif($cc=='CC-BY-ND')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by-nd/3.0/au/";
                ?>
                <a href="{{$cc_uri}}" tip="Attribution-No Derivatives"><img src="{{asset_url('images/icons/CC-BY-ND.png', 'core')}}" class="img-cc" alt="CC-BY-ND"></a> <br/>
                @elseif($cc=='CC-BY-NC')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by-nc/3.0/au/";
                ?>
                <a href="{{$cc_uri}}" tip="Attribution-Non Commercial"><img src="{{asset_url('images/icons/CC-BY-NC.png', 'core')}}" class="img-cc" alt="CC-BY-NC"></a> <br/>
                @elseif($cc=='CC-BY-NC-SA')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by-nc-sa/3.0/au/";
                ?>
                <a href="{{$cc_uri}}" tip="Attribution-Non Commercial-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-NC-SA.png', 'core')}}" class="img-cc" alt="CC-BY-NC-SA"></a> <br/>
                @elseif($cc=='CC-BY-NC-ND')
                <?php
                if($cc_uri=='') $cc_uri = "http://creativecommons.org/licenses/by-nc-nd/3.0/au/";
                ?>
                <a href="{{$cc_uri}}" tip="Attribution-Non Commercial-Non Derivatives"><img src="{{asset_url('images/icons/CC-BY-NC-ND.png', 'core')}}" class="img-cc" alt="CC-BY-NC-ND"></a> <br/>
                @else
                <span>{{sentenceCase($cc)}}</span>
                @endif
                @if($licence_content) {{$licence_content}} @endif
            </div>
            @endif

            @if($ar || $access_detail )
            <h4>Access: </h4>

            @if($ar=='open')
                <i class="fa fa-check" style="color:forestgreen"></i> Open
            @elseif($ar=='conditional')
                Conditions apply
            @elseif($ar=='restricted')
                Restrictions apply
            @else
                Other
            @endif

        	@if($access_content!='')
        	   <a href="javascript:;" id="toggleRightsContent" class="small" style="padding-left:15px;">view details</a>
        	@endif

            @endif
        </div>

        @if(isset($access_content))
            <div id="rightsContent">

                @if($access_content) {{$access_content}}@endif
            </div>
        @endif
        @if($ro->directaccess)
            @include('registry_object/contents/contact-info')
        @endif


    </div>
</div>