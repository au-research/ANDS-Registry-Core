<?php

class Registry_fetch extends CI_Model
{

	function transformExtrifToHTMLStandardRecord($extrif)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);

		// Add a level of entity decoding here 
		return html_entity_decode($this->_transformByXSL($extrif, 'extRif2view.xsl', $xsl_args));
	}

	function transformExtrifToHTMLPreview($extrif, $lite=false)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);
		if($lite){
			return html_entity_decode($this->_transformByXSL($extrif, 'extRif2previewLite.xsl', $xsl_args));
		}else{
			return html_entity_decode($this->_transformByXSL($extrif, 'extRif2preview.xsl', $xsl_args));
		}
	}

	function transformExtrifToHTMLContributorRecord($extrif)
	{
		$xsl_args = array(
			'base_url' => base_url(),
		);

		return html_entity_decode($this->_transformByXSL($extrif, 'extRif2contributorView.xsl', $xsl_args));
	}

	private function _transformByXSL ($XML, $xslt_filename, $args = array())
	{
		$xsl = new DomDocument();
		$document = new DomDocument();
		$document->loadXML($XML, LIBXML_NOENT);
		$xsl->load(REGISTRY_APP_PATH . 'view/_xsl/'. $xslt_filename);
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xsl);

		foreach ($args AS $arg_name => $arg)
		{
			$proc->setParameter('', $arg_name, $arg);
		}

		$transformResult = $proc->transformToXML($document);	
		return $transformResult;
	}

	


	function fetchExtrifBySlug($slug)
	{
		$url = $this->config->item('registry_endpoint') . "getRegistryObject/?slug=" . $slug;

		$contents = json_decode(@file_get_contents($url), true);


		if (isset($contents['data']))
		{
			return $contents;
		}
		else if (isset($contents['redirect_registry_object_slug']))
		{
			header("Location: " . portal_url($contents['redirect_registry_object_slug']));
			exit();
		}
		else if (isset($contents['previously_valid_title']))
		{
			// Should throw a soft 404...
			throw new SlugNoLongerValidException($contents['previously_valid_title']);
		}
		else
		{
			if($contents['message'])
			{
				if($contents['message']=='404')
				{
					throw new PageNotValidException($contents['message']);
				}
				throw new Exception("Error whilst fetching registry object: " . $contents['message']);
			}
			else
			{
				throw new Exception("Error whilst fetching registry object: No response from registry when requesting this SLUG");
			}
		}
	}

	function fetchExtrifByID($id)
	{
		$url = $this->config->item('registry_endpoint') . "getRegistryObject/?registry_object_id=" . $id;
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['data']))
		{
			return $contents;
		}
		else
		{
			if($contents['message']=='404')
			{
				throw new PageNotValidException($contents['message']);
			}
			throw new ErrorException("Error whilst fetching registry object: " . $contents['message']);
		}
	}

	function fetchConnectionsBySlug($slug, $limit=5, $offset=0, $type_filter=null)
	{
		$url = $this->config->item('registry_endpoint') . "getConnections/?slug=" . $slug;
		if($limit!=6) $url.='&limit='.$limit;
		if($offset!=0) $url.='&offset='.$offset;
		if($type_filter!=null) $url.='&type_filter='.$type_filter;

		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['connections']))
		{
			return $contents;
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connections: " . $contents['message']);
		}
	}

	function fetchConnectionsByID($id, $limit=5, $offset=0, $type_filter=null)
	{
		$url = $this->config->item('registry_endpoint') . "getConnections/?registry_object_id=" . $id;
		if($limit!=6) $url.='&limit='.$limit;
		if($offset!=0) $url.='&offset='.$offset;
		if($type_filter!=null) $url.='&type_filter='.$type_filter;
		
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['connections']))
		{
			return $contents;
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connections: " . $contents['message']);
		}
	}

	function fetchSuggestedLinksBySlug($slug, $type, $start, $rows)
	{
		$url = $this->config->item('registry_endpoint') . "getSuggestedLinks/?slug=" . $slug . "&suggestor=" . $type . "&start=$start&rows=$rows";
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['links']))
		{
			return $contents['links'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object suggested links by slug: " . $contents['message']);
		}
	}

	function fetchSuggestedLinksByID($id, $type, $start, $rows)
	{
		$url = $this->config->item('registry_endpoint') . "getSuggestedLinks/?id=" . $id . "&suggestor=" . $type . "&start=".$start."&rows=".$rows;

		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['links']))
		{
			return $contents['links'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object suggested links by ID: " .$url."  ". $contents['message']);
		}
	}

	function fetchAncestryGraphBySLUG($slug)
	{
		$url = $this->config->item('registry_endpoint') . "getAncestryGraph/?slug=".$slug;
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['trees']))
		{
			return $contents['trees'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connection graph: " . $contents['message']);
		}
	}

	function fetchAncestryGraphByID($id)
	{
		$url = $this->config->item('registry_endpoint') . "getAncestryGraph/?registry_object_id=" . $id;
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['trees']))
		{
			return $contents['trees'];
		}
		else
		{
			throw new Exception("Error whilst fetching registry object connection graph: " . $contents['message']);
		}
	}

	function fetchContributorPageByID($id, $published_only = true)
	{
		$url = $this->config->item('registry_endpoint') . "getContributorPage/?registry_object_id=" . $id . "&published_only=" . (string) $published_only;
		$contents = json_decode(@file_get_contents($url), true);
		if (isset($contents['data']))
		{
			return $contents;
		}
		else
		{
			throw new ErrorException("Error whilst fetching contributor page details: " . $contents['message']);
		}
	}


	function fetchContributorData($group)
	{
		$url = $this->config->item('registry_endpoint') . "getContributorData/?slug=".$group;


 		$facetsForGroup = json_decode(@file_get_contents($url), true);

 		if (isset($facetsForGroup['contents']))
		{
			return $facetsForGroup;
		}
		else
		{
			var_dump($url);
			throw new ErrorException("Error whilst fetching contributor page details: " . $facetsForGroup['message']);
		}
	
		return $facetsForGroup;
	}
	function fetchContributorText($group)
	{
		$url = $this->config->item('registry_endpoint') . "getContributorText/?slug=".$group;

 		$cannedText = json_decode(@file_get_contents($url), true);

 		if (isset($cannedText['theText']))
		{
			return $cannedText;
		}
		else
		{
			throw new ErrorException("Error whilst fetching contributor page details: " . var_export($cannedText,true));
		}
	
		return $cannedText;
	}

function fetchContributorDataById($id)
	{
		$url = $this->config->item('registry_endpoint') . "getContributorData/?id=".$id;


 		$facetsForGroup = json_decode(@file_get_contents($url), true);

 		if (isset($facetsForGroup['contents']))
		{
			return $facetsForGroup;
		}
		else
		{
			var_dump($url);
			throw new ErrorException("Error whilst fetching contributor page details: " . $facetsForGroup['message']);
		}
	
		return $facetsForGroup;
	}
	function fetchContributorTextById($id)
	{
		$url = $this->config->item('registry_endpoint') . "getContributorText/?id=".$id;

 		$cannedText = json_decode(@file_get_contents($url), true);

 		if (isset($cannedText['theText']))
		{
			return $cannedText;
		}
		else
		{
			throw new ErrorException("Error whilst fetching contributor page details: " . var_export($cannedText,true));
		}
	
		return $cannedText;
	}

	function getSlugFromKey($key)
	{
		$url = $this->config->item('registry_endpoint') . "getSlugFromKey/?key=" . $key;
 		$response = json_decode(@file_get_contents($url), true);
 		if (isset($response[0]))
 		{
 			return $response[0];
 		}
 		else
 		{
 			return NULL;
 		}
	}

	function fetchInstitutionalPages(){
		$url = $this->config->item('registry_endpoint') . "getInstitutionals";
		$response = json_decode(@file_get_contents($url), true);
		return $response;
	}

}
class PageNotValidException extends Exception {}
class SlugNoLongerValidException extends Exception {}