<div class="modal fade" id="citationModal" role="dialog" aria-labelledby="Citation" aria-hidden="true" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Cite</div>
            <div class="modal-body">
                Copy and paste a formatted citation or use one of the links to import into a bibliography manager.
                <form action="" class="form">
                	<div class="form-group">
                		<label for="">EndNote</label>
                		<textarea name="" id="" cols="30" rows="3" class="form-control">
                			{{$ro->cite('endnote', 'text')}}
                		</textarea>
                	</div>
                </form>
                <div class="btn-group btn-link">
                	<a href="{{$ro->cite('endnote', 'link')}}">EndNote</a>
                </div>
            </div>
        </div>
    </div>
</div>