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
        margin-left: 250px;
        margin-top: -30px;
        align-content: center;
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

    .sv_nav1 input[type="button"], .sv_default_css button {
        color: white;
        /*background-color: #00819D; */
        margin-bottom: 15px;
        background: linear-gradient(#00A2C4, #00819D);
        border: .5px solid #00b0d5;
        border-radius: 4px;
        font-family: 'Roboto',sans-serif;
        padding: 3px 12px;
        height: 28px;
        text-decoration: none;

    }
    .sv_nav1 input[type="button"]:hover {
        background: #026d84;
        border: .5px solid #00A2C4;
        border-radius: 4px;
        color: #fff;
        font-family: 'Roboto',sans-serif;
        padding: 6px 12px;
        height: 28px;
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
        position: fixed;
        bottom: -20px;
        left: 220px;
        white-space: nowrap;
        text-decoration: none !important;
        font-family: 'Roboto', sans-serif;
        cursor: pointer;
        font-size: 14px;
        z-index: 99;
        border: 1px;
        font-size: 100%;
    }

    @media only screen and (max-width: 1000px) {

        .sv-container-modern{
            margin: 10px;
        }
    }
    @media only screen and (min-width: 1000px) {
        .sv-body__page, .sv-body__footer {
            margin-right: 10px;
            margin-left: 10px;
        }
    }

    .sv-completedpage {
        font-family: Raleway;
        font-size: 1em;
        box-sizing: border-box;
        height: 14em;
        padding-top: 4.5em;
        padding-bottom: 4.5em;
        text-align: center;
        color: #404040;
        background-color: #f5f5f5;
    }
    .sv-completedpage:before {
        display: none;
    }
    @media only screen and (min-width: 1000px) {
        .sv-completedpage {
            margin-left: calc(10px + 0.293em);
            margin-right: 10px;
        }
    }
</style>
<div class="survey_button" id="survey_button">

    <div class="panel panel-primary" id="survey_focus_id">
        <div class="panel-heading" id="survey_focus_id">
            <h3 class="panel-title" id="survey_focus_id">Research Data Australia - Short Survey </h3>
            <span class="pull-right" id="survey_focus_id">
                <a style="color:white" onclick="$('#collapsed-chevron').toggleClass('fa-rotate-180')"
                    data-toggle="collapse" href="#collapse" id="collapse_chevron">
   <i class="fa fa-chevron-circle-down" id="collapsed-chevron"></i></a></span>
        </div>
        <div class="panel-body collapse in" id="collapse">
            <div id="surveyContainer" id="survey_focus_id"></div>
            <div id="surveyResult"></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{asset_url('js/rda_survey.js','core')}}"></script>

