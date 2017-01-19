<!--
section links removed as the anchor tags do not work within the modal
Need to set page up as html and open in a new window or use an iframe
<ul style="list-style-type: none;">

    <li><a href="#review_your_adv_search" >Reviewing your Advanced Search</a></li>
    <li><a href="#search_term_query_constructor">Search Terms Query Constructor</a></li>
    <li><a href="#subject_filer">Subject Filter</a></li>
    <li><a href="#data_providerz_filer">Data Provider Filter</a></li>
    <li><a href="#access_filer">Access Filter</a></li>
    <li><a href="#licence_filter">Licence Filter</a></li>
    <li><a href="#time_period_filter">Time Period Filter</a></li>
    <li><a href="#location_filter">Location Filter</a></li>

</ul>
-->
<img style="width:460px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/AdvancedSearch.png', 'core')}}" alt="Advanced Search"/>
<p>The Advanced Search popout allows you to build/refine complex queries all in a single tabbed popout. From within the Advanced Search you can construct boolean searches and apply one or more filter categories to your search.
</p>
<p>Note that there is no defined order to the tabs in the Advanced Search and you can apply the filters in any order you choose. Where there are multiple options for a filter category e.g. (Subjects) the options & record counts displayed are based on your query. Each time you switch tabs the available filter options and record counts are updated to reflect any changes on the previous tab.</p>

<h3 id="review_your_adv_search">Reviewing your Advanced Search</h3>
<p>As you build/refine your search in the Advanced Search popout, you can review the entire search and the number of results which will be returned by selecting the ‘Review’ tab. The tab also allows you to modify your search by removing filters.</p>
<img style="width:460px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/AdvReview.png', 'core')}}" alt="Advanced Search Review"/>
<h3 id="search_term_query_constructor">Search Terms Query Constructor</h3>
<p>The Query Constructor provides a way of searching for records using multiple search term combinations and Boolean operators. </p>
<img style="width:360px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/QueryConstructor.png', 'core')}}" alt="Advanced Search Query Constructor"/>
<h5>Query Rows</h5>
<p>The advanced queries created using the Query Constructor are comprised of Rows. Each Row consists of a Field, Condition Operator and a Value. The Value tells the search what to look for, the Field tells the search where to look, and the Condition Operator tells the search whether a record should ‘Contain’ or ‘Exclude’ the Value.
</p>
<ul>
	<li>Multiple search terms entered into a single Condition Value are treated by the search as being separated by the Boolean operator AND.</li>
	<li>The search terms are treated as case insensitive E.g. ‘Rain’ is the same as ‘rain’.</li>
	<li>Exact phrases can also be entered into Condition Values by using quotes " " E.g. "ice sheets"</li>
	<li>The ? symbol can be used to perform a single character wildcard search. E.g. Organi?ations.</li>
	<li>The * symbol can be used to perform multiple character wildcard search. E.g. Extend*</li>
</ul>
<p class="small muted">Note: Wildcard characters can be applied to single search terms, but not to search phrases.</p>
<h4>Boolean Operators</h4>
<p>The Query Constructor supports the use of the Boolean operators ‘AND’ & ‘OR’ between Query Rows. The operators are applied at the search level, meaning all Query Rows are separated by the same Boolean value. Changing the Boolean value between two Query Rows will change the value between all Query Rows.
</p>
<h4>Example - Constructing an Advanced Query</h4>
<p>Here we will step through constructing an advanced query where we would like to find all the records which contain ‘Rain’ in the title, and ‘flood’ and ‘weather’ in the description.</p>
<ol>
	<li>Ensure you are starting with a fresh search by clearing any previous searches. </li>
	<li>Open the Advanced Search popout and ensure you are on the ‘Search Terms’ tab. Two Query Rows should be displayed by default.</li>
	<li>From the Field drop down in the 1st Query Row select ‘Title’.</li>
	<li>In the empty value field in the 1st Query Row enter the search term ‘Rain’.</li>
	<li>From the Field drop down in the 2nd Query Row select ‘Description’.</li>
	<li>In the empty value field in the 2nd Query Row enter the search term ‘flood’.</li>
	<li>Click the ‘Add Row’ button to add a 3rd Query Row.</li>
	<li>From the Field drop down in the 3rd Query Row select ‘Description’.</li>
	<li>In the empty value field in the 3rd Query Row enter the search term ‘weather’.</li>
	<li>Click the ‘Search’ button to execute the search.</li>
</ol>

<h3 id="subject_filer">Subject Filter</h3>
<p>The Subject tab allows you to refine your search by selecting subjects which have been used to describe data records. The default subject vocabulary in Research Data Australia, and the one which is used consistently by data providers, is the ANZSRC Field of Research. Other supported subject vocabularies are also available and can be selected by using the drop down displayed at the top of the tab (note that these can take a little while to load).
</p>
<img style="width:360px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/Subjects.png', 'core')}}" alt="Advanced Search Subjects Filter"/>
<p>Subject vocabularies are displayed as browsable hierarchical trees. Subject literals displayed as green links can be clicked to display or hide child subjects. </p>
<p>Subjects can be added or removed from your search by using the checkbox displayed with each subject literal. Multiple subjects can be selected within a single subject vocabulary and also across vocabularies.
</p>
<p>The number of records with a subject will be displayed at the end of each subject literal E.g ‘Economics (30)’. Note that because the relationships between records and subjects are many to many, the counts displayed with the subjects will not necessarily match the count of records returned by your search. For example you may see 3 subjects all showing a (1) beside them. This could resolve to a single record containing all 3 of the subjects. Where no records exist with a subject value a (0) will be displayed with the literal. </p>

<h3 id="data_providerz_filer">Data Provider Filter</h3>
<p>The Data Provider tab allows you to limit your search to records published to Research Data Australia by specific providers.The number of records available from providers will be displayed at the end of each provider literal E.g ‘Bond University (25)’.</p>
<p>Data providers can be added or removed from your search by using the checkbox displayed with each data provider literal.</p>
<img style="width:360px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/DataProvider.png', 'core')}}" alt="Advanced Search Data Provider Filter"/>

<h3 id="access_filer">Access Filter</h3>
<p>The Access tab allows you to limit your search to records with specific access types. Data records in Research Data Australia fall into one of four access types:</p>
<dl>
<dt>Open</dt><dd>Data that is readily accessible and reusable.</dd>
<dt>Conditional</dt><dd>Data that is accessible and reusable, providing certain conditions are met (e.g. free registration is required)</dd>
<dt>Restricted</dt><dd>Data access is limited in some way (e.g. only available to a particular group of users or at a specific physical location)</dd>
<dt>Other</dt>
<dd>&lt;no value&gt; or &lt;user defined custom value&gt;</dd></dl>
<p>The number of records available in each access type will be displayed at the end of the access literal E.g ‘Open(23)’.</p>
<p>Access types can be added or removed from your search by using the checkbox displayed with each access literal. </p>

<h3 id="licence_filter" class="setheight">Licence Filter</h3>
<table cellpadding="20" cellspacing="10" border="1px">
    <tbody>
    <tr>
        <th>Licence Filter Group</th>
        <th>Licence types included</th>
    </tr>
    <tr>
        <td>
            <strong>Open Licence</strong>: A licence bearing broad permissions that may include
            a requirement to attribute the source, or share-alike (or both), requiring a
            derivative work to be licensed on the same or similar terms as the reused material. </td>
        <td>
            <p>CC-BY</p>
            <p>CC-BY-SA</p>
            <p>PL</p>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Non-Commercial licence</strong> : As for the Open Licence but also
            restricting reuse only for non-commercial purposes. </td>
        <td>
            <p>CC-BY-NC</p>
            <p>CC BY-NC-SA</p>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Non-Derivative licence</strong>
                <span>: As for the Open Licence but also prohibits adaptation of the material, and
                    in the second case also restricts reuse only for non-commercial purposes.</span>

        </td>
        <td>
            <p>CC BY-ND<</p>
            <p>CC-BY-NC-ND</p>
        </td>
    </tr>
    <tr>
        <td>
            <strong>Restrictive Licence</strong>
            : A licence preventing reuse of material unless certain restrictive
            conditions are satisfied. Note licence restrictions, and contact rights
            holder for permissions beyond the terms of the licence.
        </td>
        <td>
            AusGOALRestrictive
        </td>
    </tr>
    <tr>
        <td>
            <strong>No Licence</strong>
            : All rights to reuse, communicate, publish or reproduce the material are
            reserved, with the exception of specific rights contained within the
            Copyright Act 1968 or similar laws. Contact the copyright holder for
            permission to reuse this material.
        </td>
        <td>
            NoLicense
        </td>
    </tr>
    <tr>
        <td><strong>Other</strong>
        </td>
        <td>&lt;no value&gt; or &lt;user defined custom
            value&gt;
        </td>
    </tr>
    </tbody>
</table>
<p>The number of records available in each licence filter group will be displayed at the end of the licence literal E.g ‘No Licence(57)’.</p>
<p>Licence groups can be added or removed from your search by using the checkbox displayed with each licence literal.</p>

<h3 id="time_period_filter">Time Period Filter</h3>
<p>The Time Period tab allows you to restrict your search to only records which contain Temporal Coverage* information which falls within a specific year range. The filter has been implemented as a pair of text fields which allow you to enter a ‘From Year
and ‘To Year’. The placeholder text shown in the text fields indicates the available Temporal range you can search within.</p>
<img style="width:360px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/TimePeriod.png', 'core')}}" alt="Advanced Search Time Period Filter"/>

<p>To filter your results by a time period:
Open the Advanced Search popout and ensure you are on the ‘Time Period’ tab.
Enter a time period range by using the From Year and To Year Fields.
Click the ‘Search’ button to execute the search.</p>
<p><i>*Temporal Coverage = Time period during which data was collected or observations made</i></p>
<p><i>Note: Where the records in your search contain no temporal information the following message will be displayed on the tab:
"Search results contain no time period information."</i></p>

<h3 id="location_filter">Location Filter</h3>
<p>The Location tab will allow you to filter your search results to only records that have mappable location information described, which falls within a specified region.</p>

<img style="width:460px; display:block; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/Map.png', 'core')}}" alt="Advanced Search Spatial Filter"/>


To draw a region on the map:
<ol>
    <li>pen the Advanced Search popout and ensure you are on the ‘Location’ tab.</li>
    <li>Use the map navigation tools on the left hand side of the map until you have the required map view.</li>
    <li>Select the box tool (<img style="width:22px; margin-left:auto; margin-right:auto" src="{{asset_url('images/help/BoxTool.png', 'core')}}" alt="Box Tool"/>).</li>
    <li>Click on the map and drag the mouse to draw a rectangle.</li>
    <li>Release the mouse to finish. If there are records with location information available for your selection a red marker will be displayed for the first 15 records.</li>
    <li>Click the ‘Search’ button to execute the search.</li>
    </ol>

<p><i>Note to change or redraw a region simply carry out the above steps again.</i></p>

