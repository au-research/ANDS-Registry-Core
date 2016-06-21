@extends('layouts/modal')
@section('content')
<div role="tabpanel">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" id="overview_tab" class="active"><a href="" class="help_link" id="overview_link">Overview</a></li>
        <li role="presentation" id="search_tab"><a href="" class="help_link" id="search_link">Search</a></li>
        <li role="presentation" id="myrda_tab"><a href="" class="help_link" id="myrda_link">MyRDA</a></li>
        <li role="presentation" id="advsearch_tab"><a href="" class="help_link" id="advsearch_link">Advanced Search</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="overview">
            @include('includes/help-overview')
        </div>
        <div role="tabpanel" class="tab-pane" id="search">
            @include('includes/help-search')
        </div>
        <div role="tabpanel" class="tab-pane" id="myrda">
            @include('includes/help-my-rda')
        </div>
        <div role="tabpanel" class="tab-pane" id="advsearch">
            @include('includes/help-adv-search')
        </div>
    </div>

</div>
@stop

