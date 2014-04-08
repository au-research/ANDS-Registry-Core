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
						$iirId = '';
						if(isset($entry['logo'])){

							if (!in_array($entry['logo'], $this->config->item('banned_images')))
							{
								$logo = '<img class="related_logo" src="'.$entry['logo'].'"/>';
							}
						}
						
						if(isset($entry['identifier_relation_id']) && $entry['registry_object_id'] == null)
						{
							$url = $entry['relation_url'];
							$preview = "";
							$iirId = 'identifier_relation_id='.$entry['identifier_relation_id'];
						}
						else if ($entry['status'] == PUBLISHED){
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
						if ($entry['class'] == "party") {
							$entry['class'] = "party_one";
							if(isset($count['party_one_count'])) {
								$count['party_one_count']++;
							}else $count['party_one_count'] = 1;
						}

						if(!isset($conn[$entry['class']])){
							$conn[$entry['class']] = $logo.'<p class="'.$entry['class'].' preview_connection"><a href="'.$url.'" '.$preview.' relation_type="'.$relationship.'" '.$relDesc.' '.$relUrl.' '.$iirId.'>'.$entry['title'].$suffix.'</a></p>';
						}else{
							$conn[$entry['class']] .= $logo.'<p class="'.$entry['class'].' preview_connection"><a href="'.$url.'" '.$preview.' relation_type="'.$relationship.'" '.$relDesc.' '.$relUrl.' '.$iirId.'>'.$entry['title'].$suffix.'</a></p>';
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
					$heading = "<h3>People</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" class="view_all_connection" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' People</a></p>';
					}
					break;					
				case "party_one":
					$heading = "<h3>People</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' People</a></p>';
					}
					break;	
				case "party_multi":
					$heading = "<h3>Organisations & Groups</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Organisations & Groups</a></p>';
					}
					break;	
				case "activity":
					$heading = "<h3>Activities</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Activities</a></p>';
					}
					break;	
				case "service":
					$heading = "<h3>Services</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Services</a></p>';
					}
					break;
				case "collection":
					$heading = "<h3>Collections</h3>";
					if($count[$connections.'_count'] > 5){
						$footer = '<p><a href="javascript:;" relation_type="'.$connections.'" ng-click="open($event)" ro_slug="'.$ro_slug.'" ro_id="'.$ro_id.'">View All '.$count[$connections.'_count']. ' Collections</a></p>';
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
		$this->load->view('connections_layout');
	}

?>
