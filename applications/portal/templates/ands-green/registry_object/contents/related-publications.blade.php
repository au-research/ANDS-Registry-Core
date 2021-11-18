<div class="related-publications">
    <h4>Related Publications</h4>
    <ul class="list-unstyled">
        <?php //dd($related['publications']['docs']); ?>
        @foreach($related['publications']['contents'] as $col)
            <?php //var_dump($col['_childDocuments_'][0]['relation_type_text'])?>
                <li>
                <i class="fa fa-book icon-portal"></i>
                <small>{{ $col['relations'][0]['relation_type_text'] }}</small>

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
                @if(isset($col['relation_notes']))
                @foreach ($col['relation_notes'] as $note)
                   <p> {{ $note }} </p>
                @endforeach
                @endif

            </li>
        @endforeach
    </ul>
</div>

