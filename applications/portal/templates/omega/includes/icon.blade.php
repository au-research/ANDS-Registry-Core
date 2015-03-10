@if($ro->core['class']=='collection')
    <i class="fa fa-folder-open icon-portal"></i>
@elseif($ro->core['class']=='activity')
    <i class="fa fa-flask icon-portal"></i>
@elseif($ro->core['class']=='service')
    <i class="fa fa-wrench icon-portal"></i>
@elseif($ro->core['class']=='party')
    @if($ro->core['type']=='person')
        <i class="fa fa-user icon-portal"></i>
    @elseif($ro->core['type']=='group')
        <i class="fa fa-group icon-portal"></i>
    @endif
@endif