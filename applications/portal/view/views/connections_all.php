<?php
	/**
	 * There will be only 1 relation_type here
	 */
	$html = '';
	foreach($connections_contents[$relation_type] as $conn){
		$html.='<div class="ro_preview">';
		if($conn['registry_object_id'] === null && $conn['identifier_relation_id'] !== null)
		{
			$html.='<div class="ro_preview_header"><img class="icon-heading" src="'.base_url().'assets/core/images/icons/'.$conn['class'].'s.png"/><div class="title connection_preview_link" identifier_relation_id="'.$conn['identifier_relation_id'].'">'.$conn['title'].'</div><div class="clear"></div></div>';
			$html.='<div class="ro_preview_description hide"></div>';
		}
		else if($related_identity_type=='slug'){
			$relation_link = base_url().$conn['slug'];
			$html.='<div class="ro_preview_header"><img class="icon-heading" src="'.base_url().'assets/core/images/icons/'.$conn['class'].'s.png"/><div class="title">'.$conn['title'].'</div><div class="clear"></div></div>';
			$html.='<div class="ro_preview_description hide">'.html_entity_decode(html_entity_decode($conn['description'])).'<div class="ro_preview_footer"><a href="'.$relation_link.'">View Full RecordaSasASasASs</a></div></div>';
		}else{
			$relation_link = base_url().'?id='.$conn['registry_object_id'];
			$html.='<div class="ro_preview_header"><img class="icon-heading" src="'.base_url().'assets/core/images/icons/'.$conn['class'].'s.png"/><div class="title">'.$conn['title'].'</div><div class="clear"></div></div>';
			$html.='<div class="ro_preview_description hide">'.html_entity_decode(html_entity_decode($conn['description'])).'<div class="ro_preview_footer"><a href="'.$relation_link.'">View Full RecordaSasASasASs</a></div></div>';
		}
		
		$html.='</div>';
	}

	$currentPage = intval($currentPage);
	$nextPage = $currentPage+1;
	$prevPage = $currentPage-1;
	$html.= '<div class="pagination" style="display: block;">';
	$html.= '<div class="results_navi"><div class="results">'.$totalResults.' related</div>';
	$html.= '<div class="page_navi">Page: '.$currentPage.'/'.$totalPage .' | ';
	if($currentPage!=1){
		$html.='<a href="javascript:;" class="goto" page="'.$prevPage.'" relation_type="'.$relation_type.'" ro_slug="'.$slug.'" ro_id="'.$id.'">Prev</a>';
		$html.='<a href="javascript:;" class="goto" page="'.$nextPage.'" relation_type="'.$relation_type.'" ro_slug="'.$slug.'" ro_id="'.$id.'">Next</a>';
	}else if($currentPage==$totalPage && $currentPage > 1){
		$html.='<a href="javascript:;" class="goto" page="'.$prevPage.'" relation_type="'.$relation_type.'" ro_slug="'.$slug.'" ro_id="'.$id.'">Prev</a>';
	}else{
		$html.='<a href="javascript:;" class="goto" page="'.$nextPage.'" relation_type="'.$relation_type.'" ro_slug="'.$slug.'" ro_id="'.$id.'">Next</a>';
	}
	$html.= '</div><div class="clear"></div></div></div>';

	echo $html;
?>