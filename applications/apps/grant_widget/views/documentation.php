<?php $this->load->view('header');?>
<div class="content-header">
    <h1>ANDS - Grant Widget</h1>
</div>
<div id="breadcrumb">
    <?php echo anchor(registry_url(), '<i class="icon-home"></i> Home', array('tip'=>'Go to Home')); ?>
    <?php echo anchor('/grant_widget', 'Grant Widget', array('class'=>'current')) ?>
</div>
<div class="container-fluid">
    <div class="widget-box">
        <div class="widget-title">
            <h5>Grant Widget</h5>
        </div>
        <div class="widget-content">

            <div class="alert alert-info">
                <b>Developer Zone</b>
                <p>Some basic web development knowledge may be needed to implement this widget</p>
            </div>

            <form action="" class="form-inline well">
                <h1>Grant Widget</h1>
                <input type="text" name="name" value="" size="40" class="grant_widget"/>
            </form>
            <?php echo anchor(apps_url('grant_widget/download/'), '<i class="icon-white icon-download"></i> Download Now', array('class'=>'btn btn-large btn-success')) ?>

            <h2>What is this widget?</h2>
            <p>
                The ANDS Grant Widget allows you to verify a research grant ID against the grant information supplied by research funders to ANDS, or alternatively to search for a grant using keywords in the following fields - Title, Lead Institution, Investigrators, Principal Investigator, Description.
            </p>
            <p>
                This widget is a jQuery plugin with extensible options over styling and functionality. Hence the widget is dependent on the jQuery plugin to function correctly.
            </p>
            <h2>How to use this widget</h2>
            <p>
                Put the following code snippet into your document's &lt;head&gt; segment
                <pre class="prettyprint pre-scrollable">
&lt;script src='http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'&gt;&lt;/script&gt;
&lt;script type="text/javascript" src="<?php echo apps_url('assets/grant_widget/css/grant_widget.js');?>"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/grant_widget/css/grant_widget.css');?>" /&gt;
                </pre>
            </p>
            <p>
                You can init the plugin simply by
                <pre class="prettyprint">
&lt;input type="text" class="grant_widget"&gt;
                </pre>
                The plugin will automatically bind to all elements that have the class of <code>.grant_widget</code>
            </p>


            <p>You can also manually trigger the plugin with</p>
            <pre class="prettyprint">
$('input').grant_widget();
            </pre>

            <h2>Demo</h2>
            
            <form action="" class="form-inline">
                <h4>Default Settings</h4>
                <pre class="prettyprint">
&lt;input type="text" name="name" id="default_settings_grant" value="" size="40" class=""/&gt;
                </pre>
                <pre class="prettyprint">
$('#default_settings_grant').grant_widget();
                </pre>
                <input type="text" name="name" id="default_settings_grant" value="1031221" size="40" class=""/>
            </form>
            <hr>
            <form action="" class="form-inline">
                <h4>Custom Settings</h4>
                <pre class="prettyprint">
&lt;input type="text" name="name" id="custom_settings_grant" value="grant_id" size="40" class=""/&gt;
                </pre>
                <pre class="prettyprint">
$('#custom_settings_grant').grant_widget({
    pre_lookup: true,
    pre_open_search:true,
    lookup_text: 'Custom Lookup',
    search_text: 'Custom Search',
    before_html: 'Enter Here: ',
    auto_close_search: true,
    funder_lists: true,
    funders: '{"funder_list":["Australian Research Council","National Health and Medical Research Council"]}',
    search_fields: '{"search_fields":["title","person","institution","description","id"]}'
});
                </pre>
                <input type="text" name="name" id="custom_settings_grant" value="1031221" size="40" class=""/>
            </form>

            <h2>Configurations</h2>

<?php 
    $config = array(
        array('search_endpoint', 'http://researchdata.ands.org.au/registry/services/api/getGrants/', 'JSONP search API for Grants'),
        array('lookup_endpoint','http://researchdata.ands.org.au/registry/services/api/getGrants/?id=','JSONP API for Grants Lookup service'),
        array('pre_lookup', 'false','Automatically Do a lookup on the current value of the input field'),
        array('search','true', 'Display Search Button, enable searching functionality'),
        array('pre_open_search','false','Open Search Box by default'),
        array('search_text','&lt;i class="icon-search"&gt;&lt;/i&gt; Search','Text to display on the open search box button'),
        array('search_class','grant_search btn btn-small','CSS class to apply on the open search box button'),
        array('tooltip','Boolean to have hover tool tip or not','false,'),
        array('lookup','true','Display the Lookup button, enable Lookup functionality'),
        array('lookup_text', 'Look up','Text for the lookup button'),
        array('lookup_class', 'grant_lookup btn btn-small','Lookup button CSS class'),
        array('before_html:' ,'&lt;span class="grant_before_html"&gt;grant title &lt;/span&gt;', 'Text to display before the input field'),
        array('wrap_html', '&lt;div class="grant_wrapper"&gt;&lt;/div&gt;', 'The wrapping HTML to cover the input fields'),
        array('result_success_class', 'grant_success_div', 'CSS class for the div displaying successful lookup'),
        array('result_error_class', 'grant_error_div', 'CSS class for the div displaying error lookup'),
        array('search_div_class', 'grant_search_div', 'CSS class for the searching div'),
        array('nohits_msg', '&lt;p&gt;No matches found &lt;p&gt;','Message to display when no result or an error returns'),
        array('query_text', 'Search Query:', 'Text displaying before the search query'),
        array('search_text_btn', 'Search', 'Text display for the search button'),
        array('close_search_text_btn', '[x]','Text display for the closing search div button'),
        array('lookup_error_handler', 'false', 'overwrite function for error lookup <code>function(xhr, message)</code>'),
        array('lookup_success_handler', 'false', 'overwrite function when a successful lookup returns <code>function(data, obj, settings)</code>'),
        array('lookup_success_hook', 'false', 'a function hook after a successful lookup returns <code>function()</code>'),
        array('auto_close_search', 'false', 'boolean, To automatically close the search box after a value is selected'),
        array('funder_lists', 'false', 'boolean, To allow for user provided lists of funders to search against'),
        array('funders','', 'json string providing list of funders in the funding_list element eg. funders: &#39;{"funder_list":["Australian Research Council","National Health and Medical Research Council"]}&#39;'),
        array('search_fields','', 'json string providing list of searchable fields of the api &#39;{"search_fields":["title","person","institution","description","id"]} &#39;')
    );
?>

            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <td>Property</td><td>Defaults</td><td>Description</td>
                </thead>
                <tbody>
                    <?php foreach($config as $c): ?>
                    <tr>
                        <td><code><?php echo $c[0] ?></code></td>
                        <td><code><?php echo $c[1] ?></code></td>
                        <td><?php echo $c[2] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>License</h2>
            <p>
                Apache License, Version 2.0: <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>
            </p>

            
        </div>
    </div>
</div>
<?php $this->load->view('footer');?>