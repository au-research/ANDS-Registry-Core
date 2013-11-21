<?php $this->load->view('rda_header');?>
<div class="container less_padding" ng-app="portal_theme">
	<div class="breadcrumb">
		<?php echo anchor('/', 'Home', array('class'=>'crumb')); ?> / 
		<?php echo anchor('/theme_page/', 'Themes', array('class'=>'crumb')); ?>
	</div>
	<div class="item-view-inner">
		<div class="page-title" id="pageTitle"><h1>Themes</h1></div>
		<div class="post clear">
			<?php if(sizeof($index['items']) > 0): ?>
			<?php foreach($index['items'] as $page): ?>
			<div class="theme-page-item">
				<?php $img_src = ($page['img_src']?$page['img_src']:'http://placehold.it/350x150&text=No+Cover+Image'); ?>
				<a href="<?php echo portal_url('theme/'.$page['slug']);?>"><img class="cover"src="<?php echo $img_src;?>" alt=""></a>
				<h4 class="theme-page-title"><?php echo anchor('theme_page/view/'.$page['slug'], $page['title']); ?></h4>
			</div>
			<?php endforeach; ?>
			<?php else: ?>
			<b>There are no theme pages set up</b>
			<?php endif; ?>
		</div>
	</div>
	
	<div class="container_clear"></div>
</div>
<?php $this->load->view('rda_footer');?>