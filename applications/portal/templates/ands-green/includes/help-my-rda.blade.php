<?php
$this->load->library('user_agent');
$useIFrame = true;
if ($this->agent->is_browser('Chrome'))
{
    $useIFrame = false;
}

?>
<ul style="list-style-type: none;">
    <li><a href="#accessing_my_rda">Accessing MyRDA</a></li>
    <li><a href="#saving_searches">Saving Searches</a></li>
    <li><a href="#saving_records">Saving Records</a></li>
    <li><a href="#check_for_new_records">Checking for New Results for your Saved Searches</a></li>
</ul>
<br/>
<h3 id="my_rda_video">How to use MyRDA in Research Data Australia</h3>
<br/>
@if($useIFrame)
<iframe width="560" height="315" src="https://www.youtube.com/embed/C2HImxMDY3c" frameborder="0" allowfullscreen></iframe>
@else
<object width="560" height="315">
    <param name="movie" value="https://www.youtube.com/v/C2HImxMDY3c?version=3&hl=en_US"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed
        src="https://www.youtube.com/v/C2HImxMDY3c?version=3&hl=en_US"
        type="application/x-shockwave-flash" width="560" height="315"
        allowscriptaccess="always"
        allowfullscreen="true">
    </embed>
</object>
@endif

<br/><br/>
<p>MyRDA provides you with a personal account in Research Data Australia. Once logged in you will gain access to additional functionality such as the ability to save searches and records for viewing across separate RDA sessions, and the ability to contribute to the Research Data Australia community by adding tags (keywords) to records to assist in discovery.</p>
<h3 id="accessing_my_rda">Accessing MyRDA</h3>
<p>MyRDA can be accessed from the ‘MyRDA Login’ menu option shown in the Research Data Australia menu bar displayed at the top of each page.</p>
<img style="width:560px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/MyRDALogin.png', 'core')}}" alt="Advanced Search"/>

<p>To log in you must either belong to an institution which is a member of the Australian Access Federation (AAF), have local credentials registered in the system or have an account with a supported social media provider (e.g. Facebook). Upon logging in an account will automatically be generated for you by the system. Options for logging in are:</p>
<ul>
<li><b>Social</b>– Social authentication allows users to login with their social media accounts. The social logins currently supported by Research Data Australia are Facebook, Twitter, Google and LinkedIn. Upon logging in via a social account for the first time, users will be asked to grant Research Data Australia some permissions to their profile. These are generally the minimum permissions allowable by the social provider. Research Data Australia will not post content to your social account unless asked to by you (eg. Clicking a share button in Research Data Australia). More information can be found in the <a href="{{portal_url()}}home/privacy" title="Privacy Policy">Privacy Policy</a>.</li>
<li><b>Shibboleth AAF</b> - Research Data Australia is open to all Australian researchers who have valid <a href="http://www.aaf.edu.au/" title="Australian Access Federation">Australian Access Federation</a> (AAF) accounts. The AAF provides an infrastructure to facilitate trusted electronic communications between higher education and research institutions both locally and internationally. The infrastructure is built upon the federated identity solution <a href="http://shibboleth.net/" title="Shibboleth">Shibboleth</a>.  Researchers who can authenticate with the AAF do not need any additional credentials to access Research Data Australia.</li>
<li><b>Built In</b> – Built In authentication is generally used by ANDS staff, testers, and users who belong to institutions that are not affiliated with the AAF. Users are created as a User Role in the registry with an Authentication Type of ‘Built In’, and authenticate directly against the registry.</li>
<li><b>LDAP</b> – LDAP authentication is only used by ANDS staff.</li>
</ul>

<h3 id="saving_searches">Saving Searches</h3>
<p>Once logged into your MyRDA account searches can be saved and rerun across Research Data Australia sessions.
To save a search:</p>
<ol>
    <li>Ensure you are logged into MyRDA.</li>
    <li>Execute a search.</li>
    <li>Refine the search with filters and additional keywords until you are happy with the result.</li>
    <li>Click the ‘Save Search’ button displayed in the In the Current Search section above the filters on the left hand side of the search results page. The ‘Save Search’ popout will be displayed.</li>
    <li>Enter a name for the saved search then click the ‘Save Search’ button shown in the popout. The search will be saved to your MyRDA account.</li>
</ol>
<img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SaveSearch.png', 'core')}}" alt="Save Search"/>

<h3 id="saving_records">Saving Records</h3>
<p>Once logged into your MyRDA account records can be saved from the Search Results and Record View pages.</p>
<img style="width:360px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SaveRecordsSearch.png', 'core')}}" alt="Save Search"/>


<p>To save a record from the Search Results page:</p>
<ol>
    <li>Ensure you are logged into MyRDA.</li>
    <li>Execute a search which returns some results.</li>
    <li>Use the checkbox shown with each search result to select the records to save. Alternatively the ‘Select All’ button can be used to select all records on the page.</li>
    <li>Click the ‘Save Records’ button shown in the search results header. The ‘Save to MyRDA’ popout will be displayed.</li>
    <li>Enter a name for a new folder to save the record(s) into and click the ‘Go’ button, or select an existing folder to save the record(s) into. The records will be saved to your MyRDA account.</li>
</ol>
<img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SaveToMyRDA.png', 'core')}}" alt="Saved Searches"/>


<p>To save a record from a Record View page:</p>
    <ol>
        <li>Ensure you are logged into MyRDA.</li>
        <li>Open the record you wish to save.</li>
        <li>Click the ‘Save to MyRDA’ button shown on the page. The ‘Save to MyRDA’ popout will be displayed.</li>
        <li>Enter a name for a new folder to save the record(s) into and click the ‘Go’ button, or select an existing folder to save the record(s) into. The records will be saved to your MyRDA account.</li>
    </ol>

<h3 id="check_for_new_records">Checking for New Results for your Saved Searches</h3>
<p>Once a search has been saved to your account checking for new results across Research Data Australia sessions is a quick and easy process.</p>

<p>Before checking for new results it’s important to understand the details displayed for a saved search. Each saved search displays 3 record counts:</p>

<ul>
    <li>The number of records returned by the search at the time of saving the search.</li>
    <li>The number of new records added to Research Data Australia since the time of saving.
        <ul>
            <li>This number is automatically refreshed each time the page is loaded.
                <img style="width:460px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SavedSearches.png', 'core')}}" alt="Saved Searches"/>

            </li>
        </ul>
    </li>
    <li>The number of records added to Research Data Australia since the user last checked for new results.
        <ul>
            <li>This number allows users to keep track of the number of new records across Research Data Australia sessions without the need to remember how many new records were added since the search was saved. This number is only updated when the user invokes a check for new results.</li>
        </ul>
    </li>
</ul>



<p>To check for new results:</p>
<ol>
    <li>Login to MyRDA and access your account.</li>
    <li>In the Saved Search table click the ‘Refresh’ button displayed with the search you would like to check for new results. The system will rerun the saved search using the ‘Last Refresh’ date as a constraint. The count and last refresh information will then be updated.</li>
</ol>
<p>If you would like to check for new results for multiple saved searches you can use the checkboxes displayed with each saved search then click the ‘Bulk Action’ dropdown and select ‘Refresh’.</p>
