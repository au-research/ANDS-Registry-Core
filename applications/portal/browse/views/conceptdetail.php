<h2><?php echo $vocab;?> / <?php echo $prefLabel;?></h2>

<?php $p = $r->{'result'}->{'primaryTopic'};?>

<table class="vocab-info-table">
	<tr>
		<td width="150px">Prefered Label</td><td><?php echo $prefLabel;?></td>
	</tr>

	<tr>
		<td>Field of Research Code</td><td><?php echo $notation;?></td>
	</tr>

	<tr class="vocabulary_metadata">
		<td>URI</td><td><a href="<?php echo $uri;?>"><?php echo $uri;?></a></td>
	</tr>
	<tr class="vocabulary_metadata">
		<td>Resolve URL</td><td><a href="<?php echo $p->{'isPrimaryTopicOf'};?>"><?php echo $p->{'isPrimaryTopicOf'};?></a></td>
	</tr>

	<?php if(isset($p->{'broader'})):?>
	<tr class="vocabulary_metadata">
		<td>Broader Concept</td>
		<td><a href="<?php echo $p->{'broader'}->{'_about'};?>"><?php echo $p->{'broader'}->{'_about'};?></a></td>
	</tr>
	<?php endif;?>
	<?php if(isset($p->{'narrower'})):?>
	<tr class="vocabulary_metadata">
		<td>Narrower Concepts</td>
		<td>
			<?php
				if(is_array($p->{'narrower'})){
					foreach($p->{'narrower'} as $narrower){
						echo '<a href="'.$narrower->{'_about'}.'">'.$narrower->{'_about'}.'</a><br/>';
					}
				}else{
					echo '<a href="'.$p->{'narrower'}->{'_about'}.'">'.$p->{'narrower'}->{'_about'}.'</a><br/>';
				}
			?>
		</td>
	</tr>
	<?php endif;?>

</table>

<a href="javascript:;" id="show_vocab_metadata_link">show vocabulary metadata</a>

<div id="vocab_uri" class="hide"><?php echo $uri;?></div>
<div id="vocab_search_result">Loading search result...</div>