<div class="related-publications">
    <h4>Related Publications</h4>
    <?php     $disp_count = 0 ; ?>
    <ul class="list-unstyled">

        @foreach($related['publications']['contents'] as $col)
                <?php
                $result = array();
                $relation_types = [];
                foreach ($col['relations'] as $element) {
                    $relation_types[] = $element['relation_type_text'];
                }
                $relation_types = array_unique($relation_types);
                $relation_type_text =  implode($relation_types,", ");
                $disp_count++;
                ?>
                    @if($disp_count<6)
                        <li>
                    @else
                        <li class="morepublications" style="display:none">
                     @endif
                <i class="fa fa-book icon-portal"></i>
                <small>{{ $relation_type_text  }}</small>

                {{--DOI relatedInfo identifier is resolvable--}}
                    @if($col['to_identifier_type'] == 'doi')
                         <a href="" class="ro_preview"
                            identifier_doi="{{ $col['to_identifier'] }}"
                            tip="{{ $col['to_title'] }}">
                             {{ $col['to_title'] }}
                         </a>
                     @else
                         {{ $col['to_title'] }}
                     @endif
                     <br/>


                     {{--Display the identifiers--}}
            <b>{{ $col['to_identifier_type'] }}</b> :
                @if(isset($col['to_url']))
                    <a href="{{ $col['to_url'] }}"
                       >
                        {{ $col['to_identifier'] }}
                    </a>
                @else
                    {{ $col['to_identifier'] }}
                @endif
                <br/>

                {{--Notes display for this relation--}}
                @if(isset($col['to_notes']))

                   <p> {{ $col['to_notes']}} </p>

                @endif

            </li>
            @if($disp_count==5 && $related['publications']['total'] >5 && $ro->core['status'] === 'PUBLISHED')
                <a href="" class="showMorePublications showPublications" >Show all {{$related['publications']['total']}} related publications</a>
            @elseif(($disp_count==$related['publications']['total']))
                <a href="" class="showLessPublications showPublications" style="display:none">Show less related publications</a>
            @endif
        @endforeach
    </ul>
</div>

