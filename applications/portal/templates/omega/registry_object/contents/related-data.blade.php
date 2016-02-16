<div class="related-data">
    <h4>Related Data</h4>
    <ul class="list-unstyled">
        @foreach($related['data']['docs'] as $col)
            <li>
            <span
                    @if($ro->core['class'] == 'collection')
                    itemprop="isBasedOnUrl"
                    @endif
            >
                <i class="fa fa-folder-open icon-portal"></i>
                <small>{{ $col['display_relationship'] }}</small>
                <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
                   title="{{$col['to_title']}}"
                   class="ro_preview"
                   tip="{{ $col['display_description'] }}"
                   ro_id="{{$col['to_id']}}">
                    {{$col['to_title']}}
                </a>
            </span>
            </li>
        @endforeach

        @if($related['data']['count'] > 5)
            <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $related['data']['count'] }} related data</a></li>
        @endif
    </ul>
</div>