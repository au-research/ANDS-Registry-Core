<p>The Advanced Search popout allows you to build/refine complex queries all in a single tabbed popout. From within the Advanced Search you can construct boolean searches and apply one or more filter categories to your search.
</p>
<p>Note that there is no defined order to the tabs in the Advanced Search and you can apply the filters in any order you choose. Where there are multiple options for a filter category e.g. (Subjects) the options & record counts displayed are based on your your query. Each time you switch tabs the available filter options and record counts are updated to reflect any changes on the previous tab.</p>

<h4>Reviewing your Advanced Search</h4>
<p>As you build/refine your search in the Advanced Search popout, you can review the entire search and the number of results which will be returned by selecting the ‘Review’ tab. The tab also allows you to modify your search by removing filters.</p>

<h4>Search Terms Query Constructor</h4>
<p>The Query Constructor provides a way of searching for records using multiple search term combinations and Boolean operators. </p>
<h5>Query Rows</h5>
<p>The advanced queries created using the Query Constructor are comprised of Rows. Each Row consists of a Field, Condition Operator and a Value. The Value tells the search what to look for, the Field tells the search where to look, and the Condition Operator tells the search whether a record should ‘Contain’ or ‘Exclude’ the Value.
</p>
<ul>
	<li>Multiple search terms entered into a single Condition Value are treated by the search as being separated by the Boolean operator AND.</li>
	<li>The search terms are treated as case insensitive E.g. ‘Rain’ is the same as ‘rain’.</li>
	<li>Exact phrases can also be entered into Condition Values by using quotes " " E.g. "ice sheets"</li>
	<li>The ? symbol can be used to perform a single character wildcard search. E.g. Organi?ations. Note that the wildcard can only be used for single</li>
	<li>The * symbol can be used to perform multiple character wildcard search. E.g. Extend*</li>
</ul>
<p class="small muted">Note: Wildcard characters can be applied to single search terms, but not to search phrases.</p>
<h5>Boolean Operators</h5>
<p>The Query Constructor supports the use of the Boolean operators ‘AND’ & ‘OR’ between Query Rows. The operators are applied at the search level, meaning all Query Rows are separated by the same Boolean value. Changing the Boolean value between two Query Rows will change the value between all Query Rows.
</p>
<h5>Example - Constructing an Advanced Query</h5>
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

<h4>Subject Filter</h4>
<p>The Subject tab allows you to refine your search by selecting subjects which have been used to describe data records. The default subject vocabulary in Research Data Australia, and the one which is used consistently by data providers, is the ANZSRC Field of Research. Other supported subject vocabularies are also available and can be selected by using the drop down displayed at the top of the tab (note that these can take a little while to load). 
</p>
<p>Subject vocabularies are displayed as browsable hierarchical trees. Subject literals displayed as green links can be clicked to display or hide child subjects. </p>
<p>Subjects can be added or removed from your search by using the checkbox displayed with each subject literal. Multiple subjects can be selected within a single subject vocabulary and also across vocabularies.
</p>
<p>The number of records with a subject will be displayed at the end of each subject literal E.g ‘Economics (30)’. Note that because the relationships between records and subjects are many to many, the counts displayed with the subjects will not necessarily match the count of records returned by your search. For example you may see 3 subjects all showing a (1) beside them. This could resolve to a single record containing all 3 of the subjects. Where no records exist with a subject value a (0) will be displayed with the literal. </p>