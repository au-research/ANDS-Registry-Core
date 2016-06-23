@extends('layout/vocab_layout')
@section('content')
<article>
    <section class="section swatch-white element-short-bottom">
        <div class="container element-short-bottom">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <header class="text-center element-short-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
                       <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Use a vocabulary </h1>
                   </header>
                </div>
            </div>
            <div class="row animated fadeInUp">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 col-md-offset-3 text-center">
                    <img src="{{asset_url('images/use_vocab.jpg', 'core')}}" class="element element-short-bottom element-short-top"/>
                    <h3>A service built for discovery and reuse</h3>
                    <p>
                        Research Vocabularies Australia helps you find, access, and reuse vocabularies for research. Many of the vocabularies you can discover here are immediately accessible, either directly through Research Vocabularies Australia or via partners and publishers, and are free to use (subject to licence conditions).
                    </p>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <h3 class="normal light bordered">Explore the concepts in a vocabulary</h3>
                    <p>Research Vocabularies Australia includes a portal which allows users to search and browse controlled vocabularies hosted in the vocabulary editor or repository or have been described in the portal. Users can browse for vocabulary based on:</p>
                    <ul>
                        <li> Subject</li>
                        <li> Publisher</li>
                        <li> Language</li>
                        <li> Format</li>
                        <li> Access type</li>
                        <li> Licence</li>
                    </ul>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <h3 class="normal light bordered">Consume a vocabulary in your own system</h3>
                    <p>
                        Users of Research Vocabularies Australia have several options for using an Australian National Data Service (ANDS)-hosted vocabulary in their own data centres, portals, data generation tools, vocabulary services or other applications. The Vocabulary API allows developers to integrate with the vocabulary service using HTTP and a variety of data representations (including XML, JSON and RDF) and the Vocabulary Widget allows you to add Data Classification capabilities to your data capture tools via Research Vocabularies Australia.
</p>
                   <p>     Learn more about the <a href="http://developers.ands.org.au/services/vocabulary_api/" target="_blank">Vocabulary API</a> and <a href="http://developers.ands.org.au/widgets/controlled-vocabulary-widget-v2/" target="_blank">Vocabulary Widget</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

</article>
@stop