
<?php
$this->load->library('user_agent');
$useIFrame = true;
if ($this->agent->is_browser('Chrome'))
{
    $useIFrame = false;
}

?>
<ul style="list-style-type: none;">
    <li><a href="#performing_search">Performing a Search</a></li>
    <li><a href="#refining_search">Refining a Search – Filters, Keywords &amp; Multi-select</a></li>
    <li><a href="#clearing_search">Clearing a Search</a></li>
    <li><a href="#understaning_search_result">Understanding Your Search Results</a></li>
</ul>
<br/>
<h3 id="search_video">How to search in Research Data Australia</h3>
<br/>

@if($useIFrame)
<iframe width="560" height="315" src="https://www.youtube.com/embed/MZGb2tqF2Pw" frameborder="0" allowfullscreen></iframe>
@else
<object width="560" height="315">
    <param name="movie" value="https://www.youtube.com/v/MZGb2tqF2Pw?version=3&hl=en_US"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed
        src="https://www.youtube.com/v/MZGb2tqF2Pw?version=3&hl=en_US"
        type="application/x-shockwave-flash" width="560" height="315"
        allowscriptaccess="always"
        allowfullscreen="true">
    </embed>
</object>
@endif
<br/><br/>
<h3 id="performing_search">Performing a Search</h3>
<br/><img style="width:460px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SearchBar.png', 'core')}}" alt="Search Bar"/><br/>
<p>To perform a search simply type your search terms into the Search Bar displayed at the top of the page and click the ‘Search’ button. The search will be executed and you will be navigated to the Search Results page where you can further refine your search. If you would like to be more precise on where to look for your search terms you can use the dropdown displayed with the Search Bar to select a specific field to search within. The following search fields are available:</p>
<ul>
    <li>Title - The search will attempt to locate your search terms in the title of each record.
    </li>
    <li>Identifier - The search will attempt to locate your search terms in the identifiers assigned to each record.
    </li>
    <li>Related People - The search will attempt to locate your search terms in the names of people related to each record.
    </li>
    <li>Related Organisations - The search will attempt to locate your search terms in the names of organisations or institutions related to each record.
    </li>
    <li>Description - The search will attempt to locate your search terms in the description of each record.
    </li>
</ul>

<p>Exact phrases can also be entered into the Search Bar by using quotes " " E.g. "ice sheets"
</p>

<p>More complex search term queries can be conducted using the Advanced Search. Please refer to the Advanced Search tab above.</p>

<h3 id="refining_search">Refining a Search – Filters, Keywords &amp; Multi-select</h3>
<br/><img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/Filters.png', 'core')}}" alt="Filters"/><br/>
<p>Once you have executed a search and are on the Search Results page, you can further refine your search by adding additional keywords and filters which are displayed on the left hand side of the page under the ‘Refine search results’ section. Adding a filter will restrict your search to only records which contain the filter value.</p>
<p>Note that the only filter value which is mandatory for a data record is the ‘Data Provider’ value. All other values are optional.</p>

<h4>Checkbox Filters & Multi-Select:</h4>
<p>The majority of the filters available to refine your search results are enabled via a checkbox. To add a filter to your search simply click the checkbox or label shown against it. Upon selecting the filter the search will be updated to reflect your selection.</p>
<p>Many of the checkbox filters available to refine your search results are multi-select, meaning you can select more than one filter in  a single category (e.g. 3  different Subjects).  To select multiple filters in a single filter category use the ‘View More’ link shown at the bottom of the filter list to access the Advanced Search popout. From within the Advanced Search you will be able to select multiple filters in the one category. More information can be found in the Advanced Search section below.</p>
<h4>Time Period filter:</h4>
<p>The Time Period filter allows you to restrict your search to only records which contain Temporal Coverage * information which falls within a specific year range. The filter has been implemented as a pair of text fields which allow you to enter a ‘From Year
and ‘To Year’. The placeholder text shown in the text fields indicates the available Temporal range you can search within.</p>
<p>To add a Time Period filter:
<ol><li>Simply enter a ‘From Year’ and a ‘To Year’ in the provided text fields. Note that open ranges can be specified by leaving one of the fields blank.
</li><li>Click the ‘Go’ button to apply the filter</li></ol></p>
<p><i>*Temporal Coverage = Time period during which data was collected or observations made.</i></p>
<h4>Keywords:</h4>
<p>Additional keywords can be added to your search by either adding more search terms to the Search Bar at the top of the page or by using the Keywords filter shown at the top of the ‘Refine search results’ section on the left hand side of the page.</p>
<h4 id="location_filer">Location filter:</h4>
<p>The Location filter allows you to restrict your search results to only records that have mappable location information described, which falls within a specified region. The Location filter is available through the Advanced Search. Please refer to the Advanced Search section below for more information.</p>
<h3 id="clearing_search">Clearing a Search</h3>
<br/><img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/ClearSearch.png', 'core')}}" alt="Clear Search"/><br/>
<p>To clear a search click the black ‘X’ displayed in the search bar or click the ‘Clear Search’ button displayed in the Current Search section displayed on the left hand side of the Search Results page.</p>
<h3 id="understaning_search_result">Understanding Your Search Results</h3>
<p>The search within Research Data Australia will return records matching your specified search parameters (terms and filters). By default the returned records will be sorted by ‘Relevance’, where each record in a search result is given a ranking based on how closely a record matches the entered parameters. The search ranking algorithm used in Research Data Australia for ‘Relevance’ is complex and cannot easily be described. Below is a very rough guide for how the default ranking and matching is achieved.</p>
<ul>
    <li>Searches for a given word in Research Data Australia will generate results of any records which contain the entire word or the "stem" of the word (a stem is generated by a built-in Solr  filter, which breaks down search terms into their "word stems", so "fishing" will match "fish")</li>
    <li>Searches for multiple terms (i.e. space-separated) are treated as disjunctive queries (so any matches on individual terms will be counted). Words enclosed in quotes are considered conjunctive and so the whole search term must match.</li>
    <li>Matches are performed against all the indexed fields for a record.</li>
    <li>Search terms discovered in the title or alternative title are ranked highest.</li>
    <li>Where multiple search terms have been entered, the distance (count of words) between these terms in a record affects the overall ranking.</li>
</ul>
<h4>Search Result Components</h4>
<br/><img style="width:560px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/SearchResult.png', 'core')}}" alt="Search Result"/><br/>
<p>For each record in the search results the Title, Data Provider and in context search term highlighting is displayed. Where no search terms have been provided for a search, the in context search term highlighting is replaced with a brief description for the record. Clicking on the Title will take you to the full record view.
    The in context highlighting provides users with an easy way of understanding why a record has been returned by a search. The indexed field where the match was made is displayed in brackets at the end of the context snippet e.g. <span style="color:grey">“(in Subject)”</span>. Up to 2 context snippets will be provided for each type of indexed field per record. For example the above image shows 2 snippets for the Description field.</p>
<h4>Sorting Your Search Results</h4>
<br/><img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/Sort.png', 'core')}}" alt="Sort"/><br/>
<p>As explained above the default sort order for search results is ‘Relevance’, where each record in a search result is given a ranking based on how closely a record matches the entered search parameters. The records are then sorted by ranking highest to lowest. The default sort order can be changed by using the ‘Sort by:’ dropdown displayed in the search results header.</p>
<p>The sort options are:</p>
<ul>
    <li>Relevance
    </li>
    <li>Title A-Z
    </li>
    <li>Title Z-A
    </li>
    <li>Date Added <span style="color:grey">(sorted newest to oldest)</span>
    </li>
</ul>
<h4>Number of Search Results per Page</h4>
<p>A default of 15 records are displayed per search result page. If there are more than 15 records, they will be displayed on subsequent pages. The number of records displayed per page can be changed by using the ‘Show:’ dropdown displayed in the search results header. The pagination links displayed at the top and bottom of the search results can then be used to navigate between pages.</p>
<br/><img style="width:260px; display:block; margin-left:auto; margin-right:auto"  src="{{asset_url('images/help/ShowPerPage.png', 'core')}}" alt="Show Per Page"/><br/>
