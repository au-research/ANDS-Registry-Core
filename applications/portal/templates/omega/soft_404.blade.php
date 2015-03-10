@extends('layouts/single')
@section('content')
<article>
    <section class="section swatch-white element-normal-bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <header class="text-center element-normal-top element-medium-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                        <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;">404</h1>
                    </header>
                    <div class="error_exception">
                        <div class="message">
                            <img src="{{$logo}}" alt="Sad Smiley"/>

                            <div>
                                <h3>We're sorry...</h3>
                                Oops! An error occured:<br/><br/>
                                <div style="width:600px;"><pre>{{$message}}</pre>
                                <pre>@if(isset($id))
                                    id: {{$id}}</pre>
                                @endif
                                <pre>@if(isset($slug))
                                    slug: {{$slug}}</pre>
                                @endif
                                <pre>@if(isset($key))
                                    key: {{$key}}</pre>
                                @endif

                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
             </div>
             <div class="row ">
                 <p>please email Joel!</p>
            </div>
        </div>
    </section>
</article>
@stop

