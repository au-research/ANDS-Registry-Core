$(function(){
	$('#default_settings_orcid').orcid_widget();

	$('#custom_settings_orcid').orcid_widget({
		pre_lookup: true,
		pre_open_search:true,
		lookup_text: 'Custom Lookup',
		search_text: 'Custom Search',
		before_html: 'Enter Here: ',
		auto_close_search: true
	});
});