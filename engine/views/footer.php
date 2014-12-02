<?php
/**
 * Core Template File (footer)
 *
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/
 * @package ands/
 *
 */
// Variable defaults
$js_lib = !isset($js_lib) ? array() : $js_lib;
$base_url = str_replace('/apps','/registry',base_url());
?>
<div id="page-footer" class="clearfix">&nbsp;
</div>





    <!-- Mustache Template that should be used everywhere-->
    <div id="error-template" class="hide">
        <div class="alert alert-error">{{{.}}}</div>
    </div>

    <!-- The javascripts Libraries
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script>
        localStorage.clear();
        var base_url = '<?php echo $base_url;?>';
        var portal_url = '<?php echo portal_url();?>';
        var apps_url = '<?php echo apps_url();?>';
        var real_base_url = "<?php echo $this->config->item('default_base_url');?>";
        var suffix = '<?php echo url_suffix();?>';
        var editor = '';
        //urchin code
        <?php echo urchin_for($this->config->item('svc_urchin_id')); ?>
    </script>

    <script type="text/javascript" src="<?php echo$base_url;?>assets/js/arms.scripts.js"></script>

    <?php foreach($js_lib as $lib):?>
            
        <?php if($lib=='graph'):?>
            <!--[if lt IE 9]><script language="javascript" type="text/javascript" src="excanvas.js"></script><![endif]-->
            <script language="javascript" type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/jquery.jqplot.min.js"></script>
            <script language="javascript" type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.highlighter.min.js"></script>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/jqplot/plugins/jqplot.cursor.min.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/jqplot/jquery.jqplot.css" />


        <?php elseif($lib=='googleapi'):?>
            <script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script type="text/javascript">
                localGoogle = google;
                google.load("visualization", "1", {packages:["corechart"]});
            </script>

        <?php elseif($lib=='tinymce'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/tinymce/tinymce.min.js"></script>
            <script>
               var editor = 'tinymce';
            </script>

        <?php elseif($lib=='datepicker'):?>
            <script type="text/javascript" src="<?php echo $base_url;?>assets/lib/bootstrap_datepicker/js/bootstrap-datepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/bootstrap_datepicker/css/datepicker.css" />

        <?php elseif($lib=='ands_datepicker'):?>
            <script type="text/javascript" src="<?php echo apps_url('assets/datepicker_tz_widget/js/ands_datetimepicker.js');?>"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/datepicker_tz_widget/css/ands_datetimepicker.css');?>" />

        <?php elseif($lib=='ands_datetimepicker_widget'):?>
            <link href="<?php echo apps_url('assets/datepicker_tz_widget/css/ands_datetimepicker.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/datepicker_tz_widget/js/ands_datetimepicker.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='prettyprint'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/prettyprint/pretty.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo$base_url;?>assets/lib/prettyprint/pretty.css" />

        <?php elseif($lib=='dataTables'):?>
            <script type="text/javascript" src="<?php echo$base_url;?>assets/lib/dataTable/js/jquery.dataTables.js"></script>

        <?php elseif($lib=='abs_sdmx_querytool'):?>
            <script type="text/javascript" src="<?php echo apps_url('assets/abs_sdmx_querytool/js/abs_sdmx_querytool.js') ?>"></script>

        <?php elseif($lib=='context_menu'):?>
            <script src="<?php echo asset_url('lib/bootstrap-contextmenu.js', 'base'); ?>" type="text/javascript"></script>

        <?php elseif($lib=='vocab_widget'):?>
            <link rel="stylesheet" type="text/css" href="<?php echo apps_url('assets/vocab_widget/css/vocab_widget.css'); ?>">
            <script src="<?php echo apps_url('assets/vocab_widget/js/vocab_widget.js'); ?>"></script>

       <?php elseif($lib=='orcid_widget'):?>
            <link href="<?php echo apps_url('assets/orcid_widget/css/orcid_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/orcid_widget/js/orcid_widget.js');?>" type="text/javascript"></script>

       <?php elseif($lib=='grant_widget'):?>
            <link href="<?php echo apps_url('assets/grant_widget/css/grant_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/grant_widget/js/grant_widget.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='location_capture_widget'):?>
            <link href="<?php echo apps_url('assets/location_capture_widget/css/location_capture_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/location_capture_widget/js/location_capture_widget.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='rosearch_widget'):?>
            <link href="<?php echo apps_url('assets/registry_object_search/css/rosearch_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/registry_object_search/js/rosearch_widget.js');?>" type="text/javascript"></script>

        <?php elseif($lib=='registry_widget'):?>
            <link href="<?php echo apps_url('assets/registry_widget/css/registry_widget.css');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo apps_url('assets/registry_widget/js/registry_widget.js');?>" type="text/javascript"></script>

         <?php elseif($lib=='statistics'):?>
            <script src="<?php echo str_replace('/apps','/applications/apps',base_url());?>statistics/assets/js/statistics.js" type="text/javascript"></script>

        <?php elseif($lib=='bootstro'):?>
            <link href="<?php echo base_url();?>assets/lib/bootstro/bootstro.min.css" rel="stylesheet" type="text/css">
            <script src="<?php echo base_url();?>assets/lib/bootstro/bootstro.min.js" type="text/javascript"></script>

        <?php elseif($lib=='google_map'):?>
            <script src="https://maps.googleapis.com/maps/api/js?libraries=drawing&amp;sensor=false" type="text/javascript"></script>

        <?php elseif($lib=='select2'):?>
            <link href="<?php echo asset_url('lib/select2/select2.css', 'base');?>" rel="stylesheet" type="text/css">
            <link href="<?php echo asset_url('lib/select2/select2-bootstrap.css', 'base');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo asset_url('lib/select2/select2.min.js', 'base');?>" type="text/javascript"></script>

        <?php elseif($lib=='angular'):?>
            <script type="text/javascript" src="<?php echo asset_url('lib/angular.min.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/angular-slugify.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/sortable.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/tinymce.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/angular-sanitize-1.0.1.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/select2.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/ui-bootstrap-tpls-0.6.0.min.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/ui-utils.min.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/angular.datatables.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/portal-filters.js', 'base') ?>"></script>

        <?php elseif($lib=='angular129'): ?>
            <script type="text/javascript" src="<?php echo asset_url('lib/angular129.min.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angular129-route.min.js', 'base') ?>"></script>
            <script type="text/javascript" src="<?php echo asset_url('lib/angular129-resource.min.js', 'base') ?>"></script>


        <?php elseif($lib=='colorbox'):?>
            <link href="<?php echo asset_url('lib/colorbox/colorbox.css', 'base');?>" rel="stylesheet" type="text/css">
            <script src="<?php echo asset_url('lib/colorbox/jquery.colorbox-min.js', 'base');?>" type="text/javascript"></script>

        <?php endif; ?>

    <?php endforeach;?>


	<!-- Module-specific styles and scripts -->
    <?php if (isset($scripts)): foreach($scripts as $script):?>
        <script src="<?php echo asset_url('js/' . $script);?>.js"></script>
    <?php endforeach; endif; ?>


	<!-- Bootstrap javascripts, need to be placed after all else -->
    <script src="<?php echo$base_url;?>assets/lib/twitter_bootstrap/js/bootstrap.js"></script>

  </body>
</html>
