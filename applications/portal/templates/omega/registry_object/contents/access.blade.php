@if($ro->directaccess)

    @if($ro->directaccess[0]['access_type']=='url')
        <a href="{{$ro->directaccess[0]['access_value']}}" class="btn btn-lg btn-primary btn-block"><i class="fa fa-cube"></i> Go to Data</a>
    @endif

    @if($ro->directaccess[0]['access_type']!='url')
        <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data</a>
        <div id="dataformats">
        @foreach($ro->directaccess as $access)
            <?php
            if($access['byteSize']!=''){
                $access['title'] = $access['title'].'  ('.$access['byteSize'].')' ;
            }
            $title = $access['title'];
            if(strlen($title)>35){
                $title = substr($title,0,35)."...";
            }
            $preText='';
            if($access['access_type']=='directDownload')
            {
                $preText='Download data<br/>';
            }
            elseif($access['access_type']=='landingPage'){
                $preText='Access data via landing page</br >';
            }elseif($access['access_type']=='viaService'){
                $preText='Access data online via tools</br >';
            }
            ?>
           <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{$access['access_value']}}" title="{{$access['notes']}}">
               <span>{{$access['mediaType']}}</span>
               <?=$preText;?><?=$title?> </a>
        @endforeach
        </div>
    @endif
@else
    <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data</a>
    <div id="dataformats">
        <p>Please use the contact information below to request access to this data.</p>
    </div>
@endif