<div class="modal fade" id="citationModal" role="dialog" aria-labelledby="Citation" aria-hidden="true" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">Cite</div>
            <div class="modal-body">
                <p>Copy and paste a formatted citation or use one of the links to import into a bibliography manager.</p>
                        <?php
                        $order = array('fullCitation');
                        ?>
                            @if($ro->citations)
                            @foreach($order as $o)
                            @foreach($ro->citations as $citation)
                            @if($citation['type']==$o)
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
                            {{$citation['coins']}}
                            @if(!in_array($citation['type'], $order))
                        <dl>
                            <dt >Datacite</dt>
                            <dd>
                                {{$citation['contributors']}}
                                ({{$citation['date']}}): {{$citation['title']}}.
                                {{$citation['publisher']}}.
                                {{$citation['identifier_type']}} :{{$citation['identifier']}}
                                <br /><a href="{{$citation['identifierResolved']['href']}}">{{$citation['identifier']}}</a>
                                @if($citation['url'])
                                <br /><a href="{{$citation['url']}}">{{$citation['url']}}</a>
                                @endif
                            </dd>
                            @endif
                            @endforeach
                            @endif
                        </dl>
                <div class="btn-group btn-link">
                	<a title="Export to EndNote" href="<?=base_url()."registry/registry_object/exportToEndnote/".$ro->core['id'].".ris?foo=".time()?>">EndNote</a>
                </div>
                <div class="btn-group btn-link">
                    <a title="Export to EndNote Web" href="http://www.myendnoteweb.com/?func=directExport&partnerName=ResearchDataAustralia&dataIdentifier=1&dataRequestUrl=<?=base_url()."registry/registry_object/exportToEndnote/".$ro->core['id']."?foo=".time()?>">EndNote Web</a>
                </div>
            </div>
        </div>
    </div>
</div>