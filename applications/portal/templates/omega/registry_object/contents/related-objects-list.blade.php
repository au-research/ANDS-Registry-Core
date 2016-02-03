<div class="panel panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-body swatch-white">

        {{--Related Publications--}}
        @if (isset($related['publications']) &&sizeof($related['publications']) > 0)
            @include('registry_object/contents/related-publications')
        @endif

        @if (isset($related['data']) && sizeof($related['data']) > 0)
            @include('registry_object/contents/related-data')
        @endif

        @if (isset($related['organisation']) && sizeof($related['organisation']) > 0)
            @include('registry_object/contents/related-organisation')
        @endif

        @if (isset($related['programs']) && sizeof($related['programs']) > 0)
            @include('registry_object/contents/related-program')
        @endif

        @if (isset($related['grants_projects']) && sizeof($related['grants_projects']) > 0)
            @include('registry_object/contents/related-grants_projects')
        @endif

        @if (isset($related['service']) && sizeof($related['service']) > 0)
            @include('registry_object/contents/related-service')
        @endif

        @if (isset($related['website']) && sizeof($related['website']) > 0)
            @include('registry_object/contents/related-website')
        @endif

    </div>
</div>