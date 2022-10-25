@extends('layouts/single-with-search')
@section('content')
    <article>

        <section class="section swatch-white element-short-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <header class="text-center element-normal-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                            <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Draft Record </h1>
                        </header>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        {{ $message }}
                    </div>

                </div>
            </div>
        </section>

    </article>
@stop