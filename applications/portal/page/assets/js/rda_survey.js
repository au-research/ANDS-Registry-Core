var defaultThemeColors = Survey.StylesManager.ThemeColors["modern"];
defaultThemeColors["$main-color"] = "#00819D";
defaultThemeColors["$main-hover-color"] = "#00819D";
defaultThemeColors["$text-color"] = "#4a4a4a";
defaultThemeColors["$header-color"] = "#00819D";
defaultThemeColors["$header-background-color"] = "#4a4a4a";
defaultThemeColors["$body-container-background-color"] = "#f8f8f8";

Survey.StylesManager.applyTheme("modern");

var surveyID = "rda_short_survey";
var longSurveyID ="rda_long_survey";
var hasrun = getCookie(surveyID);
var hasrunlong = getCookie(longSurveyID);
var longSurveyJSON  = {"pages":
        [{"name":"page1",
            "elements":[{"type":"html","name":"introduction","html":"<h3>Welcome to the ARDC Survey on Research Data Australia</h3>\n" +
                    "<p>Research Data Australia (RDA) is the data discovery service of the Australian Research Data Commons (ARDC).\n" +
                    "    The ARDC enables the Australian research community and industry access to nationally significant,\n" +
                    "    leading edge data intensive eInfrastructure, platforms, skills and collections of high-quality data.</p>\n" +
                    "\n" +
                    "<p>RDA helps data seekers find, access, and reuse data from over one hundred Australian research organisations,\n" +
                    "    government agencies, and cultural institutions. We do not store the data itself here but provide descriptions of,\n" +
                    "    and links to, the data from our data publishing partners.</p>\n" +
                    "\n" +
                    "<p>This survey asks you about your search experience with the RDA. Your responses will help us to understand why\n" +
                    "    and how you search for data, and inform us how to design the data discovery service in a way so you can have a\n" +
                    "    better data search experience in future.</p>\n" +
                    "<p>The survey has 14 questions, will take approximately 15 minutes to complete.</p>\n" +
                    "<p>Please read the ARDC <a href='/page/privacy' target='_blank'> Privacy Policy</a> about how ARDC manages survey responses.</p>\n" +
                    "<h3>Your consent</h3>\n" +
                    "<p>I consent to participate in the survey, I confirm that:\n" +
                    "<ul>\n" +
                    "    <li>I understand my  participation in this survey is voluntary.</li>\n" +
                    "    <li>I understand that even if I choose to start the survey, I can withdraw at any time.</li>\n" +
                    "    <li>I understand that all information I provide for the survey will be treated confidentially.</li>\n" +
                    "    <li>I understand that in any report or presentation on the result of this survey my identity will remain anonymous.</li>\n" +
                    "    <li>I understand that anonymous extracts from my survey may be quoted in presentations and publications.</li>\n" +
                    "    <li>I understand that I am free to contact ARDC via email (services@ardc.edu.au) for any clarification or questions about the survey and my responses.</li>\n" +
                    "</ul>\n" +
                    "</p>\n" +
                    "<p>\n" +
                    "    By clicking the “Start” button below to start the survey, you are consenting to the above conditions and to participate in the survey.</p>\n" +
                    "<p>Thank you for your participation.</p>"}],
            "title":"Online Survey"},
         {"name":"page2", "elements":[
             {"type":"radiogroup","name":"background","title":"What is your primary job role?","isRequired":true,
                 "choices":[
                     {"value":"researcher","text":"Researcher/scientist"},
                     {"value":"student","text":"Student "},
                     {"value":"data_manager","text":"Data manager"},
                     {"value":"librarian","text":"Librarian, archivist or research/data supporter"},
                     {"value":"policy_maker","text":"Policy maker/supporter"},{"value":"educator","text":"Educator from primary/secondary school"}
                     ],
                 "hasOther":true,"otherText":"Other, please specify:","colCount":2},
                 {"type":"radiogroup","name":"primary_employer","title":"Which type of primary organisation are you working for?","isRequired":true,"choices":[
                     {"value":"university","text":"University"},
                      {"value":"research_institution ","text":"Research institution "},
                         {"value":"museum_cultural_institution","text":"Museum or cultural institution"},
                         {"value":"government","text":"Government"},
                         {"value":"private_organisation","text":"Industry/business"},
                         {"value":"ngo_non-profit_charity","text":"NGO/non-profit/charity"},
                         {"value":"school","text":"School"}
                         ],"hasOther":true,"otherText":"Other, please specify:","colCount":2},
                 {"type":"checkbox",
                     "name":"research_area",
                     "hasComment":true,
                     "commentText":"Please specify if your choice(s) includes other; or leave any comment here",
                     "title":"What are your research areas or topics of interest? (Please mark/tick all that apply)","choices":[
                     {"value":"30","text":"Agricultural, Veterinary and Food Sciences"},
                         {"value":"31","text":"Biological Sciences"},
                         {"value":"32","text":"Biomedical and Clinical Services"},
                         {"value":"33","text":"Built Environment and Design"},
                         {"value":"34","text":"Chemical Sciences"},
                         {"value":"35","text":"Commerce, Management, Tourism and Services"},
                         {"value":"36","text":"Creative Arts and Writing"},
                         {"value":"37","text":"Earth Sciences"},
                         {"value":"38","text":"Economics"},
                         {"value":"39","text":"Education"},
                         {"value":"40","text":"Engineering"},
                         {"value":"41","text":"Environmental Sciences"},
                         {"value":"42","text":"Health sciences"},
                         {"value":"43","text":"History, Heritage and Archaeology"},
                         {"value":"44","text":"Human Society"},
                         {"value":"45","text":"Indigenous Studies"},
                         {"value":"46","text":"Information and Computing Sciences"},
                         {"value":"47","text":"Language, Communication and Culture"},
                         {"value":"48","text":"Law and Legal Studies"},
                         {"value":"49","text":"Mathematical Sciences"},
                         {"value":"50","text":"Philosophy and Religios Studies"},
                         {"value":"51","text":"Physical Sciences"},
                         {"value":"52","text":"Psychology"},
                         {"value":"other","text":"Other"}
                         ],"colCount":2},
                 {"type":"radiogroup","name":"searching_for","title":"If you just searched from Research Data Australia, what are you searching for?","choices":[
                     {"value":"dataset","text":"Dataset"},
                         {"value":"software","text":"Software"},
                         {"value":"service","text":"Data service"},
                         {"value":"grant_projects","text":"Grant/Projects"},
                         {"value":"research_paper","text":"Research paper"},
                         {"value":"other","text":"Other"}
                     ],
                     "hasComment":true,"commentText":"Please specify if your choice(s) includes other; or leave any comment here","colCount":2},
                 {"type":"checkbox","name":"primary_purpose","title":"What is the primary purpose for you to search for data? (Please mark/tick all that apply)","choices":[
                     {"value":"research","text":"For Research"},
                         {"value":"teaching","text":"For Teaching/training"},
                         {"value":"policy_making","text":"For Policy making"},
                         {"value":"other","text":"Other"}
                         ],"hasComment":true,"commentText":"Please specify if your choice(s) includes other; or leave any comment here"},
                 {"type":"checkbox","name":"major_criteria","title":"What are the major criteria/characteristics  for the dataset you are searching for? (Please mark/tick all that apply)","choices":[
                     {"value":"subject_relevance","text":"Subject relevance"},
                         {"value":"certain_organisation","text":"From a certain organisation"},
                         {"value":"clear_licence","text":"Have a clear licence statement"},
                         {"value":"permission_re-use","text":"Have permission for re-use"},
                         {"value":"specific_time_range","text":"From a specific time range"},
                         {"value":"specific_location","text":"From a specific location(s)"},
                         {"value":"include_some_variables","text":"Include common variables"},
                         {"value":"instruction_to_cite","text":"Instruction of how to cite the data"},
                         {"value":"able_to_download","text":"Be able to download"},
                         {"value":"other","text":"Other"}
                         ],"hasComment":true,"commentText":"Please specify if your choice(s) includes other; or leave any comment here","colCount":2}]},
            {"name":"page3",
                "title": "Search experience with Research Data Australia",
                "elements":[{"type":"radiogroup","name":"found_dataset","title":"Have you found what you are looking for from Research Data Australia? ","choices":[
                        {"value":"yes","text":"Yes, I found exactly what I was looking for"},
                        {"value":"somewhat","text":"Somewhat, not exactly what I searched for, but I found some useful information"},
                        {"value":"no","text":"No, I didn’t get any useful information"}
                        ],"hasComment":true,"commentText":"If no, please specify why not (e.g. no dataset fits my criteria)"},
                    {"type":"rating","name":"rate_rda","title":"How do you rate your data search experience with Research Data Australia",
                        "minRateDescription":"Extremely unsatisfied ",
                        "maxRateDescription":"Extremely satisfied"},
                    {
                        "type": "comment",
                        "name": "rda_what_worked",
                        "title": "(a) Can you tell us what helped you search in the Research Data Australia?  ",
                    },
                    {
                        "type": "comment",
                        "name": "rda_what_didnt_work",
                        "title": "9. (b) Can you tell us what didn’t work out for you when you searched in the Research Data Australia?",
                        "hideNumber": true

                    },
                    ]},

         {"name":"page4","title": "Search experience with other tools","elements":[{
             "type":"checkbox",
              "name":"used_search_tools",
              "title":"What are the tools/websites/other methods you use to search for data? (Please mark/tick all that apply)",
             "isRequired":false,
                 "choicesOrder": "asc",
                 "hasComment":true,
                 "commentText":"Please specify if your choice(s) includes other; or leave any comment here",
                 "choices":[
                    {"value":"web_search_engines","text":"Web search engines"},
                    {"value":"web_data_search_tools","text":"Web data search tools"},
                    {"value":"repository_external","text":"Data repository/Data catalogue external to my institution/organisation (e.g, Research Data Australia, data.gov, Zenodo, DRYAD, etc)"},
                    {"value":"repository_own_institution","text":"Data repository/Data catalogue from my own institution"},
                    {"value":"website_government","text":"Website from government agencies (e.g. Australian Bureau of Statistics, Australian Government Bureau of Meteorology, etc)"},
                    {"value":"following_citation","text":"Following citation of research paper"},
                    {"value":"colleagues","text":"Asking colleagues"},
                    {"value":"conferences","text":"Attending conferences"},
                    {"value":"other","text":"Other"}
                    ]},
                 {
                     "type": "ranking",
                     "title":"Please rank your choices from Question 10 from high to low frequency of use. (drag a choice and drop it to the right rank)",
                     "name": "most_used_search_tool",
                     "visible": true,
                     "choicesFromQuestion": "used_search_tools",
                     "choices": [
                         {"value":"web_search_engines","text":"Web search engines"},
                         {"value":"web_data_search_tools","text":"Web data search tools"},
                         {"value":"repository_external","text":"Data repository/Data catalogue external to my institution/organisation (e.g, Research Data Australia, data.gov, Zenodo, DRYAD, etc)"},
                         {"value":"repository_own_institution","text":"Data repository/Data catalogue from my own institution"},
                         {"value":"website_government","text":"Website from government agencies (e.g. Australian Bureau of Statistics, Australian Government Bureau of Meteorology, etc)"},
                         {"value":"following_citation","text":"Following citation of research paper"},
                         {"value":"colleagues","text":"Asking colleagues"},
                         {"value":"conferences","text":"Attending conferences"},
                         {"value":"other","text":"Other"}
                     ],
                     "choicesFromQuestionMode": "selected",
                     "hideIfChoicesEmpty": true
                 },
                 {"type":"rating","name":"search_frequency","title":"How often do you search for data outside of your usual research area(s) (or topics of interest)?","minRateDescription":"Never","maxRateDescription":"All the time"},
                 {"type":"rating","name":"search_success","title":"If you use web search engines to find data, how successful are you at finding data with the search engines?","minRateDescription":"Not successful","maxRateDescription":"Very successful"},
                 {"type":"checkbox","name":"what_search_tools_used", "choicesOrder": "asc","title":"In general, what tools do you mostly use for searching for information  (e.g. research papers) other than data? (Please mark/tick all that apply)","choices":[
                         {"value":"disciplinary_portals","text":"Disciplinary portals (e.g. Digital Library of Association for Computing Machinery, PubMed.gov of National Library of Medicine)"},
                         {"value":"library_catalogues","text":"Library catalogues"},
                         {"value":"google_scholar","text":"Google scholar"},
                         {"value":"publisher_websites","text":"Publisher websites (e.g. Elsevier,  Springer)"},
                         {"value":"web_search_engines","text":"Web search engines"},
                         {"value":"other","text":"Other"}
                        ],"hasComment":true,"commentText":"Please specify if your choice(s) includes other; or leave any comment here"}]},
            {"name":"page5","elements":[
                    {
                        "type": "html",
                        "name": "thanks_text",
                        "html": "<p>Thank you for your participation in the survey, your feedback is valuable for us to improve the RDA data discovery service and beyond. </p>" +
                            "<p>We plan to conduct a further interview to elicit more information about what information and discovery " +
                            "functionality matter to data seekers when they search for data." +
                            "If you would like to participate in a further interview of your data search experience and expectation, " +
                            "please leave your contact below. (Participants in the interview will be eligible for gift cards.) </p>"
                    },
                      {
                          "type":"multipletext",
                          "name":"user_contact",
                          "titleLocation":"hidden",
                          "items":[
                              {"name":"name","title":"Name"},
                              {"name":"organisation","title":"Organisation"},
                              {"name":"email_contact","title":"Email contact"}
                              ]
                      }
                      ],
                }
                    ],
    "cookieName": longSurveyID,
    "completeText": "Submit"}
var surveyJSON = {
    "description": "Please take a moment to complete the following short survey.",
    "focusOnFirstError": false,
    "pages":
        [{"name":"page1",
            "elements":[{"type":"html","name":"introduction","html":"" +
                    "<p>Please complete this very short survey (only two questions) to improve the service for you.</p>" +
                    "<div class='sv_nav1'>" +
                    "<input width='250' id='survey_not_now' type='button' value='Not now' class='sv_prev_btn'> &nbsp;" +
                    "<input type='button'  width='250' id='survey_never' value='Never' class='sv_prev_btn'> &nbsp;" +
                    "</div>"}],
        },
            {
            "name": "page2",
            "elements": [
                {
                    "type": "checkbox",
                    "name": "describe_yourself",
                    "minWidth": "300px",
                    "title": "How do you describe yourself? (Please mark/tick all that apply)",
                    "choices": [
                        {
                            "value": "researcher",
                            "text": "Research/Scientist",
                            "startWithNewLine": false,
                        },
                        {
                            "value": "University_employee",
                            "text": "University/Research Institute employee",
                            "startWithNewLine": false,
                        },
                        {
                            "value": "student ",
                            "text": "Student ",
                            "startWithNewLine": false,
                        },
                        {
                            "value": "Industry_employee",
                            "text": "Industry/business employee"
                        },
                        {
                            "value": "Data_manager ",
                            "text": "Data manager "
                        }                        ,
                        {
                            "value": "Government_employee",
                            "text": "Government employee (non research)"
                        },
                        {
                            "value": "Librarian_archivist_supporter",
                            "text": "Librarian, archivist or research/data supporter"
                        },
                        {
                            "value": "Educator_school",
                            "text": "Educator from primary/secondary school"
                        },
                        {
                            "value": "Policy_maker",
                            "text": "Policy maker/supporter"
                        },
                        {
                            "value": "other",
                            "text": "Other"
                        }
                    ],
                    "hasComment": true,
                    "commentText": "Please specify if your choice(s) includes other; or leave any comment here",
                    "colCount": 2
                },
                {
                    "type": "checkbox",
                    "name": "looking_for",
                    "minWidth": "300px",
                    "title": "What dataset, information or other resources (e.g. project, grant, data service) are you looking for this visit? (Please mark/tick all that apply)",
                    "choices": [
                        {
                            "value": "Specific",
                            "text": "Specific dataset I know"
                        },
                        {
                            "value": "General",
                            "text": "General dataset with common variables"
                        },
                        {
                            "value": "Exploratory",
                            "text": "Exploratory search of datasets"
                        },
                        {
                            "value": "Resources",
                            "text": "Resources from specific organization"
                        },
                        {
                            "value": "Information",
                            "text": "Information (other than data) about a topic"
                        },
                        {
                            "value": "Nothing_specific",
                            "text": "Nothing specific"
                        },
                        {
                            "value": "other",
                            "text": "Other"
                        }
                    ],
                    "hasComment":true,
                    "commentText": "Please specify if your choice(s) includes other; or leave any comment here",
                    "colCount": 2
                }
            ]
        }
    ],
    "cookieName": surveyID,
    "completeText": "Submit"
}

jQuery(document).ready(function( $ ) {
    if(!hasrun) {
        var survey = new Survey.Model(surveyJSON);
        survey.completedHtml = "<p>Thank you for helping out!</p>" +
        "<p>We also conduct a longer survey with 14 questions.</p>" +
            "<p> If you are willing to participate in the longer survey, please click " +
            " <a href='/page/survey'>here</a></p>" +
        "<p> ARDC <a href='/page/privacy'>Privacy Policy</a></p>";
        survey.showCompletedPage = true;
        survey.firstPageIsStarted = true;
        survey.startSurveyText = "Start >>";
            survey
                .onComplete
                .add(function (result) {
                     $.get('https://www.cloudflare.com/cdn-cgi/trace', function (ipdata) {
                        words = ipdata.split('\n');
                        for (i = 0; i < words.length; i++) {
                            if (words[i].substr(0, 3) == "ip=") {
                                user_ip = words[i].substr(3);
                                break;
                            }
                        }
                        $.ajax({
                            url: '/survey_results',
                            type: 'POST',
                            data:{"survey": surveyID, "results": JSON.stringify(result.data), "user_ip": user_ip},
                            success: function (response) {
                                console.log(response);
                            },
                            error: function (response) {
                                console.log(response);
                            }
                        });
                    });
                });
            $("#surveyContainer").Survey({
                model: survey,
                isExpanded: true,
                scrollable: true,
            });
    }

    if ($('#surveyContainerLong').length > 0) {
        $("#survey_button").css("visibility","hidden");
    }

    if(!hasrunlong) {
        var surveyLong = new Survey.Model(longSurveyJSON);
        surveyLong.firstPageIsStarted = true;
        surveyLong.startSurveyText = "Start >>";
        surveyLong
            .onComplete
            .add(function (result) {
                $.get('https://www.cloudflare.com/cdn-cgi/trace', function (ipdata) {
                    words = ipdata.split('\n');
                    for (i = 0; i < words.length; i++) {
                        if (words[i].substr(0, 3) == "ip=") {
                            user_ip = words[i].substr(3);
                            break;
                        }
                    }
                    $.ajax({
                        url: '/survey_results',
                        type: 'POST',
                        data: {"survey": longSurveyID, "results": JSON.stringify(result.data), "user_ip": user_ip},
                        success: function (response) {
                            createCookie(longSurveyID,true, 17520);
                            createCookie(surveyID,true, 17520);
                            console.log(response);
                        },
                        error: function (response) {
                            console.log(response);
                         }
                    });
                });
            });
        $("#surveyContainerLong").Survey({
            model: surveyLong,
            isExpanded: true,
            scrollable: true,
        });
    }
});

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function createCookie(name,value,hours){
    var expires = "";
    if (hours) {
        var date = new Date();
        date.setTime(date.getTime()+(hours*60*60*1000));
        expires = "; expires="+date.toGMTString();
    }
    document.cookie = name+"="+value+expires+"; path=/";
}

$(document).on('click', '.panel-heading span.clickable', function(e){
    var $this = $(this);
    if(!$this.hasClass('panel-collapsed')) {
        $this.parents('.panel').find('.panel-body').slideUp();
        $this.addClass('panel-collapsed');
        $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
    } else {
        $this.parents('.panel').find('.panel-body').slideDown();
        $this.removeClass('panel-collapsed');
        $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
    }
})

$(document).on('click', '#survey_never', function(e){
    var theSurvey = $("#survey_button").css("visibility","hidden");
    createCookie(surveyID,true);
})

$(document).on('click', '#survey_not_now', function(e){
    createCookie(surveyID,true,4);
    $("#survey_button").css("visibility","hidden");

})

var existCondition = setInterval(function() {
    if ($('#survey_not_now').length <= 0) {
        clearInterval(existCondition);
        $('.survey_button .sv-footer').css("margin-top","0px");
    }
}, 100); // check every 100ms



