@if($ro->accessPolicy)
<div class="modal fade" id="accessPolicyModal" role="dialog" aria-labelledby="Access Policy" aria-hidden="false" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="AccessPolicy">AccessPolicy</h4>
            </div>
            <div class="modal-body">
                @foreach($ro->accessPolicy as $accessPolicy)
                    <p>{{ $accessPolicy }}</p>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif