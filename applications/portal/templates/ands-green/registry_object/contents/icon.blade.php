@if($ro->core['class']=='collection')
    <i class="fa fa-folder-open icon-portal"></i> <small>Dataset</small>
@elseif($ro->core['class']=='activity')
    <i class="fa fa-flask icon-portal"></i> <small>{{$ro->core['type']}}</small>
@elseif($ro->core['class']=='service')
    <i class="fa fa-wrench icon-portal"></i> <small>Service or Tool</small>
@elseif($ro->core['class']=='party')
    @if($ro->core['type']=='person')
        <i class="fa fa-user icon-portal"></i> <small>Person</small>
    @elseif($ro->core['type']=='group')
        <i class="fa fa-group icon-portal"></i> <small>Organisation</small>
    @endif
@endif