
<div class="box-content" id="recentRecordsDashboard">
		<?php
			if(sizeof($recent_records)>0){
				foreach($recent_records AS $record)
				{
					?>
				<a href="<?=registry_url('registry_object/view/'.$record->registry_object_id);?>">
					<img class="class_icon pull-left" style="width:20px;padding-right:10px;" src="<?=registry_url('assets/img/party.png');?>">
					
					<div class="pull-left" style="line-height:10px;">
					<small>
							<?=ellipsis($record->title,55);?>
							<small class="clearfix muted">Updated <?=timeAgo($record->updated);?></small>
					</small>
					</div>
					<span class="tag pull-right status_<?=$record->status;?>"><?=readable($record->status,true);?></span>								</a>
				<br class="clear"/>
		<?php
				}

			}else{
				echo '<i>No records have been updated in the past 7 days.</i>';
			}
		?>
</div>
