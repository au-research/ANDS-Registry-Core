@if($related['software']['count']>0)
    <div class="related-software">
        <h4>Related Software</h4>
        <ul class="list-unstyled">

            <? $relation_to_title = [];
            $dupes = 0;?>
            @foreach($related['software']['contents'] as $col)
                <?php
                $result = array();
                $relation_types = [];
                foreach ($col['relations'] as $element) {
                    $relation_types[] = $element['relation_type_text'];
                }
                $relation_types = array_unique($relation_types);
                $relation_type_text =  implode($relation_types,", ");
                if(!isset($col['to_url'])){
                    $col['to_url']="";
                }
                $relation_to_title[$col['to_title'].$relation_type_text][] = $col['to_title'];
                $dupes = count($relation_to_title[$col['to_title'].$relation_type_text]);
                ?>
                @if($dupes<2)
                <li>
                    <i class="fa fa-file-code-o icon-portal"></i>
                    <small>{{ $relation_type_text }}</small>
                    <?php if(!isset($col['to_url'])) $col['to_url'] = false; ?>
                    <a href="{{ $col['to_url'] }}"
                       title="{{ $col['to_title'] }}"
                       tip="{{ $col['to_title'] }}"
                       class="ro_preview"
                       @if($col["to_identifier_type"]=="ro:id")
                            ro_id="{{$col['to_identifier']}}"
                       @else
                            <?php $col_json = urlencode(json_encode($col));?>
                            identifier_relation_id="{{ $col_json }}"
                       @endif>
                        {{$col['to_title']}}</a>
                </li>
                @endif
            @endforeach
            @if($related['software']['total'] > 5 && $ro->core['status'] === 'PUBLISHED')
                <li><a href="{{ $related['software']['searchUrl'] }}">View all {{ $related['software']['total'] }} related software</a></li>
            @endif
        </ul>
    </div>
@endif
