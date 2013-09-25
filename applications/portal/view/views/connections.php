<?php

	$connDiv = '';
	$conn = array();
	$count = array();

	if (isset($connections_contents))
	{
		$from_class = $connections_contents['class'];
		foreach($connections_contents['connections'] as $classes)
		{
			foreach($classes as $classname => $class)
			{
				// XXX: handle count greater than X
				if (strpos($classname, "_count")) {
					$count[$classname] = $class;
					continue;
				}

				foreach ($class AS $entry)
				{
					if(isset($entry['class']))
					{
						// Link connections to PUBLISHED objects to their SLUG for SEOness...
						$logo = '';
						if(isset($entry['logo'])){

							if (!in_array($entry['logo'], $this->config->item('banned_images')))
							{
								$logo = '<img class="related_logo" src="'.$entry['logo'].'"/>';
							}
						}
						if ($entry['status'] == PUBLISHED){
							$url = base_url() . $entry['slug'];
							$preview = 'slug='.$entry['slug'];
						}
						else{
							$url = base_url() . "view/?id=" . $entry['registry_object_id'];
							$preview = 'draft_id='.$entry['registry_object_id'];
						}

						//relationship
						if(isset($entry['relation_type'])){
							$relationship = format_relationship($from_class, $entry['relation_type'], $entry['origin']);
						}else{
							$relationship = 'Contributor';
						}	

						$suffix = "";
						if ( in_array($relationship, array('Principal investigator', 'principalInvestigatorOf')) )
						{
							$suffix = " <sup><small class='lightgrey'>(PI)</small></sup>";
						}
						if(isset($entry['relation_description']))
						{
							$relDesc = ' relation_description="'.$entry['relation_description'].'" ';
						}else{
							$relDesc = '';
						}

						if(isset($entry['relation_url']))
						{
							$relUrl = ' relation_url="'.$entry['relation_url'].'" ';
						}else{
							$relUrl = '';
						}

						if(!isset($conn[$entry['class']])){
							$conn[$entry['class']] = $logo.'<p class="'.$entry['class'].' preview_connection"><a href="'.$url.'" '.$preview.' relation_type="'.$relationship.'" '.$relDesc.' '.$relUrl.'>'.$entry['title'].$suffix.'</a></p>';
						}else{
							$conn[$entry['class']] .= $logo.'<p class="'.$entry['class'].' preview_connection"><a href="'.$url.'" '.$preview.' relation_type="'.$relationship.'" '.$relDesc.' '.$relUrl.'>'.$entry['title'].$suffix.'</a></p>';
						}
					}
				}
			}
		}

		foreach($conn as $connections => $value)
		{
			$footer = '';
			switch($connections){
				case "contributor":
					$heading = "<h3>Contributed by</h3>";
					break;
				case "party":
					$heading = "<h3>Researchers</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Researchers</a></p>';
					}
					break;					
				case "party_one":
					$heading = "<h3>Researchers</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Researchers</a></p>';
					}
					break;	
				case "party_multi":
					$heading = "<h3>Research Groups</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Research Groups</a></p>';
					}
					break;	
				case "activity":
					$heading = "<h3>Activities</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Activities</a></p>';
					}
					break;	
				case "service":
					$heading = "<h3>Services</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Services</a></p>';
					}
					break;
				case "collection":
					$heading = "<h3>Collections</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Collections</a></p>';
					}
					break;	
				default:
					$heading = 	"<h3>".$connections."</h3>";	
					break;																			
			}
			$connDiv .= $heading;
			$connDiv .= $value;	
			$connDiv .= $footer;
		}
	}

	// Only display if there are actually some connections to show...
	if ($connDiv)
	{
		echo "<h2>Connections</h2>";
		echo $connDiv;
		echo "<p><br/></p>";
	}

?>
