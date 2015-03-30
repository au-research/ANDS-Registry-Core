<?php
if($ro->core['class']=='service'){
    $buttonStr = 'Service Provider';
    $textStr = 'service';
}else{
    $buttonStr = 'Data Provider';
    $textStr = 'data';
}

?>
@if($ro->directaccess)
    @if($ro->directaccess[0]['access_type']=='url')
        @if(isset($ro->directaccess[0]['access_value']['href']))
            <a href="{{trim($ro->directaccess[0]['access_value']['href'])}}" ng-click="$event.preventDefault();access($event)" class="btn btn-lg btn-primary btn-block"><i class="fa fa-cube"></i> Go to {{$buttonStr}}</a>
        @elseif(isset($ro->directaccess[0]['access_value']))
            <a href="{{trim($ro->directaccess[0]['access_value'])}}" ng-click="$event.preventDefault();access($event)" class="btn btn-lg btn-primary btn-block"><i class="fa fa-cube"></i> Go to {{$buttonStr}}</a>
        @endif

    @endif

    @if($ro->directaccess[0]['access_type']!='url')
        <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data Providers</a>
        <div id="dataformats">
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
            $itemprop='';
            if($access['access_type']=='directDownload')
            {
                $preText='Download data <br/>';
            }
            elseif($access['access_type']=='landingPage'){
                $preText='Access data via landing page </br >';
                $itemprop = ' itemprop="distribution"';
            }elseif($access['access_type']=='viaService'){
                $preText='Access data online via tools </br >';
            }

            $tip = ' tip="'.$access['title']."<br />";
            if($access['notes']!='') $tip .= $access['notes']."<br />";
            if($access['mediaType']!='') $tip .= $access['mediaType']."<br />";
            if($access['byteSize']!='') $tip .= $access['byteSize'];
            $tip .='"';
            ?>

            @if(isset($access['access_value']['href']))
            <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{trim($access['access_value']['href'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}" <?=$itemprop?>>
            <span>{{$access['mediaType']}}</span>
            <?=$preText;?><?=$title?></a>
            @elseif(isset($access['access_value']))
            <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{trim($access['access_value'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}" <?=$itemprop?>>
                <span>{{$access['mediaType']}}</span>
                <?=$preText;?><?=$title?></a>
            @endif

        @endforeach
        </div>
    @endif
@else
    <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to {{$buttonStr}}</a>
    <div id="dataformats">
        <p>Please use the contact information below to request access to this {{$textStr}}.</p>
    </div>
@endif