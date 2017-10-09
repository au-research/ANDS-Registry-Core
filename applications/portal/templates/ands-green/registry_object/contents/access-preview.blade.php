@if($ro->directaccess)
    <div class="panel panel-primary panel-content swatch-white">
        <div id="data"><h4> Access data </h4></div>
        @foreach($ro->directaccess as $access)
            <?php
            $title = $access['title'];

            if($access['byteSize']!=''){
                $title = $access['title'].'  ('.$access['byteSize'].')' ;
            }

            if(strlen($title)>100){
                $title = substr($title,0,100)."...";
            }
            $preText='';

            if($access['access_type']=='directDownload')
            {
                $preText='Download data <br/>';
            }
            elseif($access['access_type']=='landingPage'){
                $preText='Access data via landing page </br >';
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
                    <?=$preText;?>
            <a href="{{trim($access['access_value']['href'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}">
            <span>{{$access['mediaType']}}</span>
                <?=$title?></a></br>
            @elseif(isset($access['access_value']))
                    <?=$preText;?>
            <a href="{{trim($access['access_value'])}}" ng-click="$event.preventDefault();access($event)" {{$tip}} title="{{$access['notes']}}">
                <span>{{$access['mediaType']}}</span>
               <?=$title?></a></br>
            @endif

        @endforeach
    </div>
@endif