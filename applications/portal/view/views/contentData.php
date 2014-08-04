<?php
	$contentDiv = '';
	$subjectDiv = '';
	$groupsDiv = '';
	$collectionsDiv = '';

	if (isset($contentData['contents']['contents']))
	{

		foreach($contentData['contents']['contents'] as $content=> $value)
		{

							$url = base_url() . "search/#!/tab=".$content."/group=".$contentData['contents']['contributor'] ;
					
							$contentDiv .= "<p><a href='".$url."'>".$value . " " . readable($content, ($value != 1)) . "</a></p>";	

		}
	}

	// Only display if there are actually some contemt to show...
	if ($contentDiv)
	{
		echo '<div class="right-box" id="contentRightBox">';
		echo "<h3>Registry Contents</h3>";
		echo $contentDiv;
		echo "</div>";		
	}

	if (isset($contentData['contents']['subjects']))
	{
		$subjectDiv .= "<ul class='limit5'>";
		foreach($contentData['contents']['subjects'] as $subject=> $value)
		{
							$url = portal_url().'search#!/tab=all/group='.$contentData['contents']['contributor'].'/s_subject_value_resolved='.rawurlencode($subject);
					
							$subjectDiv .= '<li><a href="'.$url.'" class="filter">'.$subject.' <i class="grey">('.$value.')</i></a></li>';		

		}
		$subjectDiv .= "</ul>";		
	}

	// Only display if there are actually some content to show...
	if ($subjectDiv)
	{
		echo '<div id="facet-result">';
		echo '<div class="widget">';
		echo '<h3 class="widget-title">Subjects Covered</h3>';
		echo $subjectDiv;
		echo "</div>";
		echo "</div>";		
	}


	if (isset($contentData['contents']['groups']))
	{

		foreach($contentData['contents']['groups'] as $group=>$value)
		{
							$url = base_url() . $value ;					
							$groupsDiv .= "<p><a href='".$url."'>".$group."</a></p>";		

		}
	}

	// Only display if there are actually some contemt to show...
	if ($groupsDiv) {
		echo '<div class="right-box" id="groupsRightBox">';
		echo "<h3>Organisations & Groups</h3>";
		echo $groupsDiv;
		echo "</div>";
	}

	if (isset($contentData['contents']['collections'])) {
		foreach($contentData['contents']['collections'] as $value) {
			$url = base_url() .$value['title'].'/'.$value['id'] ;
			$collectionsDiv .= "<p><a href='".$url."'>".$value['title']."</a></p>";
		}
	}

	// Only display if there are actually some contemt to show...
	if ($collectionsDiv) {
		echo '<div class="right-box" id="groupsRightBox">';
		echo "<h3>Last 5 Collections Added</h3>";
		echo $collectionsDiv;
		echo "</div>";
	}
	
?>
