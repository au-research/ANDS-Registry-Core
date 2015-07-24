<?php $this->load->view('header'); ?>
    <div class="content-header">
        <h1>Analytics</h1>
    </div>
    <div id="breadcrumb" style="clear:both;">
        <?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
        <a href="#/" class="current">Analytics</a>
    </div>
    <div class="container-fluid" ng-app="analytic_app" >
        <div class="row-fluid">
            <div class="span12">
                <?php $this->load->view('filters') ?>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span8">
                <?php $this->load->view('rda_stats') ?>
            </div>
            <div class="span4">
                <?php $this->load->view('doi_stats') ?>
            </div>
        </div>
    </div>
<?php $this->load->view('footer'); ?>