@extends('layouts/single-with-search')
@section('content')
<article>

    <section class="section swatch-white element-short-bottom">
       <div class="container">
           <div class="row">
               <div class="col-md-12">
                   <header class="text-center element-normal-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> We're sorry </h1>
                   </header>
               </div>
           </div>
           <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <p>The page or record you are looking for cannot be found or displayed.</p>
                    <p>We've let our engineers know so that they can take a look at the problem.</p>
                    <p>You may wish to return to the <a href="{{base_url()}}">home page</a> or contact <a href="mailto:services@ands.org.au">services@ands.org.au</a> for further support.</p>
                </div>
                @if ($message)
                    {{ $message }}
                @endif
            </div>
        </div>
    </section>

</article>
@stop

