<div class="panel panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-body swatch-white">

        {{--Related Publications--}}
        @if (isset($related['publications']) && $related['publications']['count'] > 0)
            @include('registry_object/contents/related-publications')
        @endif

        @if (isset($related['data']) && $related['data']['count'] > 0)
            @include('registry_object/contents/related-data')
        @endif

        @if (isset($related['organisations']) && $related['organisations']['count'] > 0)
            @include('registry_object/contents/related-organisation')
        @endif

        @if (isset($related['programs']) && $related['programs']['count'] > 0)
            @include('registry_object/contents/related-program')
        @endif

        @if (isset($related['grants_projects']) && $related['grants_projects']['count'] > 0)
            @include('registry_object/contents/related-grants_projects')
        @endif

        @if (isset($related['services']) && $related['services']['count'] > 0)
            @include('registry_object/contents/related-service')
        @endif

        @if (isset($related['websites']) && $related['websites']['count'] > 0)
            @include('registry_object/contents/related-website')
        @endif

    </div>
</div>