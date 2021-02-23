@extends('layouts/single')
@section('content')
@section('content')
    <style>
        .sv-container-modern {
            color: #404040;
            font-size: 14px;
            font-family: "Segoe UI", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
        input[type="button"], .sv_default_css button {
            color: white;
            /*background-color: #00819D; */
            margin-bottom: 15px;
            background: linear-gradient(#00A2C4, #00819D);
            border: .5px solid #00b0d5;
            border-radius: 4px;
            font-family: 'Roboto',sans-serif;
            padding: 6px 12px;
            height: 38px;
            text-decoration: none;

        }
        input[type="button"]:hover {
            background: #026d84;
            border: .5px solid #00A2C4;
            border-radius: 4px;
            color: #fff;
            font-family: 'Roboto',sans-serif;
            padding: 6px 12px;
            height: 38px;
            text-decoration: none;
        }
        .sv-root-modern input.sv-text, textarea.sv-comment, select.sv-dropdown {
            color: rgb(64, 64, 64);
            background-color: transparent;
            border: 1px ridge #e9e9e9;
        }

    </style>
    <article>
        <section class="section swatch-white element-normal-bottom">
            <div  class="container" >

                <div id="surveyContainerLong"></div>
                <script type="text/javascript" src="{{asset_url('js/rda_survey.js')}}"></script>
            </div>
        </section>
    </article>
@stop