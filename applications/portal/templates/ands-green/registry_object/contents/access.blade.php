<?php
if($ro->core['class']=='service'){
    $textStr = 'service';
}elseif($ro->core['class']=='collection'&& $ro->core['type']=='software'){
    $textStr = 'software';
}else{
    $textStr = 'data';
}

?>
@if($ro->directaccess)
    @if(($ro->directaccess[0]['access_type']=='url' || $ro->directaccess[0]['access_type']=='uri')  && count($ro->directaccess) == 1)
        @if(isset($ro->directaccess[0]['access_value']['href']))
            <a href="{{trim($ro->directaccess[0]['access_value']['href'])}}" ng-click="$event.preventDefault();access($event)" class="btn btn-lg btn-primary btn-block btn_access"><i class="fa fa-external-link"></i> Access the {{$textStr}}</a>
        @elseif(isset($ro->directaccess[0]['access_value']))
            <a href="{{trim($ro->directaccess[0]['access_value'])}}" ng-click="$event.preventDefault();access($event)" class="btn btn-lg btn-primary btn-block btn_access"><i class="fa fa-external-link"></i> Access the {{$textStr}}</a>
        @endif

    @endif

    @if(($ro->directaccess[0]['access_type']!='url' && $ro->directaccess[0]['access_type']!='uri' ) || count($ro->directaccess) > 1 )
        <a href="" class="btn btn-lg btn-primary btn-block  btn_access" id="gotodata"><i class="fa fa-chevron-down"></i> Access the {{$textStr}}</a>
        <div id="dataformats" class="formats_block">
        @foreach($ro->directaccess as $access)
            <?php
            $title = $access['title'];

            if($access['byteSize']!=''){
                $title = $access['title'].'  ('.$access['byteSize'].')' ;
            }

            if(strlen($title)>35){
                $title = substr($title,0,35)."...";
            }
            $preText='';
            if($access['access_type']=='directDownload')
            {
                $preText='Download '.$textStr.' <br/>';
            }
            elseif($access['access_type']=='landingPage'){
                $preText='Access '.$textStr.' via landing page </br >';
            }elseif($access['access_type']=='viaService'){
                $preText='Access '.$textStr.' online via tools </br >';
            }

            $tip = ' tip="'.$access['title']."<br />";
            if($access['notes']!='') $tip .= $access['notes']."<br />";
            if($access['mediaType']!='') $tip .= $access['mediaType']."<br />";
            if($access['byteSize']!='') $tip .= $access['byteSize'];
            $tip .='"';
            ?>

            @if(isset($access['access_value']['href']))
            <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{trim($access['access_value']['href'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}">
            <span>{{$access['mediaType']}}</span>
            <?=$preText;?><?=$title?></a>
            @elseif(isset($access['access_value']))
            <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{trim($access['access_value'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}">
                <span>{{$access['mediaType']}}</span>
                <?=$preText;?><?=$title?></a>
            @endif

        @endforeach
        </div>
    @endif
@elseif($ro->contact)

    <a href="" class="btn btn-lg btn-primary btn-block btn_access" id="gotodata"><i class="fa fa-chevron-down"></i>  Access the  {{$textStr}}</a>
    <div id="dataformats">
        <p>Please use the contact information below to request access to this {{$textStr}}.</p>
        @include('registry_object/contents/contact-info')
    </div>
@endif