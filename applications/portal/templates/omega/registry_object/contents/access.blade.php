@if($ro->directaccess)

    @if($ro->directaccess[0]['access_type']=='url')
        <a href="{{$ro->directaccess[0]['contact_value']}}" class="btn btn-lg btn-primary btn-block"><i class="fa fa-cube"></i> Go to Data</a>
    @endif

    @if($ro->directaccess[0]['access_type']=='direct')
        <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data</a>
        <div id="dataformats">
        @foreach($ro->directaccess as $access)
            <?php if($access['byteSize']!=''){
                $access['title'] = $access['title'].'  ('.$access['byteSize'].')' ;
            }
            ?>
           <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{$access['contact_value']}}"><span>{{$access['mediaType']}}</span>{{$access['title']}} </a>
        @endforeach
        </div>
    @endif
@else
    <a href="" class="btn btn-lg btn-primary btn-block" id="gotodata"><i class="fa fa-cube"></i> Go to Data</a>
    <div id="dataformats">
        <p>Please use the contact information below to request access.</p>
    </div>
@endif