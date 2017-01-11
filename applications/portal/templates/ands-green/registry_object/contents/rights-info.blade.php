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

@if($ro->rights)
    <h3>License & Rights</h3>
    @if($ar)
        <span class="label label-info label-{{$ar}}" for="">{{$ar}}</span>
    @endif
    @if($cc)
        <img src="{{asset_url('images/icons/'.$cc.'.png', 'core')}}" class="img-cc" alt="{{$cc}}">
    @endif
    <p>
        @if($detail)
            <a href="javascript:;" id="toggleRightsContent">View details</a>
            <div id="rightsContent">
                @if($content) {{$content}} @endif
            </div>
        @endif
    </p>
@endif