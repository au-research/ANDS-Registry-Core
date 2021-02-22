<style>
    .sv_main.sv_default_css input[type="button"], .sv_default_css button {
        color: white;
        /*background-color: #00819D; */
        margin-bottom: 15px;
        background: linear-gradient(#00A2C4, #00819D);
        border: .5px solid #00b0d5;
        border-radius: 4px;
    }
    .sv_main.sv_default_css input[type="button"]:hover {
        background: #026d84;
        border: .5px solid #00A2C4;
        border-radius: 4px;
        color: #fff;
        text-decoration: none;
    }
    .survey_button .sv-footer {
        min-height: 2em;
        padding: 2.5em 0 0.87em 0;
        margin-left: 194px;
        margin-top: -98px;
    }
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

    .sv-selectbase__label {
        display: block;
        box-sizing: border-box;
        width: 100%;
        cursor: inherit;
        font-size:14px;

    }
    .panel-body .sv_nav1 {
        display: inline-flex;
        white-space: nowrap;
        text-decoration: none !important;
        font-family: 'Roboto',sans-serif;
        cursor: pointer;
        font-size: 14px;
        z-index: 99;
        border: 1px;
        font-size: 100%;
        vertical-align: middle;
        horiz-align: center;
        margin-left:20px;
        margin-right: 2px;
    }

    @media only screen and (max-width: 1000px) {

        .sv-container-modern{
            margin: 10px;
        }
    }
</style>
<div class="survey_button" id="survey_button">

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Research Data Australia - Short Survey </h3>
            <span class="pull-right">
                <a style="color:white" onclick="$('#collapsed-chevron').toggleClass('fa-rotate-180')"
                    data-toggle="collapse" href="#collapse">
   <i class="fa fa-chevron-circle-up" id="collapsed-chevron"></i></a></span>
        </div>
        <div class="panel-body collapse in" id="collapse">
            <div id="surveyContainer"></div>
            <div id="surveyResult"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{asset_url('js/rda_survey.js')}}"></script>

