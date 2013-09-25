<?php $this->load->view('rda_header');?>
<div class="container">
<?php
if (isset($topic))
{
?>


<div class="topic_view">

	<div class="main">
		<h2><?=$topic['name'];?></h2>
		<?=$topic['html'];?>
	</div>
	<div class="sidebar">
		



<?php


/*
foreach($manual_boxes AS $box)
{

	echo "<div class='right-box'>" .
			"<h2>".$box['heading']."</h2>" .
			"<ul>";
	foreach ($box['items'] AS $item):
			echo "<li><a target='_blank' href='" . $item['url'] . "'>" . $item['title'] . "</a></li>";
	endforeach;

	echo 	"</ul>" .
		 "</div>";
}
?>*/
?>

		<?php foreach($topic['auto_boxes'] AS $name => $box_cfg): ?>

			<script src='//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js'></script>
			<script type="text/javascript"><!--
	
			<?php
				foreach ($box_cfg AS $name => $param)
				{
					echo "ands_search_" . $name . " = \"" . rawurlencode($param) . "\";\n";
				}

				echo 'ands_search_service_point = "'.registry_url('services/api/getMetadata.json/').'";'."\n";
				echo 'ands_search_portal_url = "'.base_url().'"'."\n";
			?>
			//--></script>
			<script type="text/javascript" src="<?=asset_url('js/search_result_widget.js', 'core');?>"></script>

			<br/>
	
		<?php endforeach; ?>

		<?php foreach($topic['manual_boxes'] AS $box_cfg): ?>
			<div class="ands_widget_wrapper">
				<h4><?=$box_cfg['heading'];?></h4>
			
			<ul>
			<?php
				foreach ($box_cfg['items'] AS $item):
					echo "<li><a target='_blank' href='" . $item['url'] . "'>" . $item['title'] . "</a></li>";
				endforeach;
			?>
			</ul>

			</div>

			<br/>
		<?php endforeach; ?>

	</div>

</div>


<div class="container_clear"></div>



<?php
}
else
{
	// Error:
	echo "The topic you requested does not exist!";
}
?>

</div>
<?php $this->load->view('rda_footer');?>