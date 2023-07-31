<div class="modal fade" id="citationModal" role="dialog" aria-labelledby="Citation" aria-hidden="false" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Cite</h4>
            </div>
            <div class="modal-body">
                <p>Copy and paste a formatted citation or use one of the links to import into a bibliography manager.</p>
                        <?php
                        $order = array('fullCitation');
                        ?>
                            @if($ro->citations)
                            @foreach($order as $o)
                            @foreach($ro->citations as $citation)
                            @if($citation['type']==$o && $citation['value']!='')
                        <dl>
                            <dt>Citation:</dt>
                            <dd>
                                <p>{{$citation['value']}}</p>
                            </dd>
                        </dl>
                            @endif
                            @endforeach
                            @endforeach

                            @foreach($ro->citations as $citation)
                            @if(!in_array($citation['type'], $order))
                        <dl>
                            <dt >Citation:</dt>
                            <dd>
                                {{$citation['contributors']}}
                                ({{$citation['date']}}): {{$citation['title']}}.
                                @if(isset($citation['version']) && trim($citation['version'])!='')
                                    {{$citation['version']}}.
                                @endif
                                {{$citation['publisher']}}.{{$ro->core['type']}}.
                                @if(isset($citation['identifierResolved']['href']))
                                <br /><a href="{{$citation['identifierResolved']['href']}}">{{$citation['identifier']}}</a>
                                @else
                                <br />{{$citation['identifier']}}</a>
                                @endif
                                @if($citation['url'])
                                <br /><a href="{{$citation['url']}}">{{$citation['url']}}</a>
                                @endif
                            </dd>
                            @endif
                            @endforeach
                            @endif
                        </dl>
                <div class="btn-group btn-link">
                	<a title="Export to EndNote" href="{{ base_url('registry_object/export/endnote/'.$ro->core['id']).'?source=portal_view' }}">EndNote</a>
                </div>
                <div class="btn-group btn-link" style="padding-left:40px">
                    <a title="Export to EndNote Web" href="{{ base_url('registry_object/export/endnote_web/'.$ro->core['id']).'?source=portal_view' }}" target="_blank">EndNote Web</a>
                </div>
                <div class="btn-group btn-link" style="padding-left:40px">
                    <a title="EndNote Help" href="https://documentation.ardc.edu.au/display/DOC/How+to+Export+to+EndNote" target="_blank">EndNote Help</a>
                </div>
            </div>
        </div>
    </div>
</div>