<?php

class CERIF_2_RIFCS extends Crosswalk{

	const  RIFCS_WRAPPER="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n
			<registryObjects xmlns=\"http://ands.org.au/standards/rif-cs/registryObjects\"
			xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
			xsi:schemaLocation=\"http://ands.org.au/standards/rif-cs/registryObjects
			http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd\">
			</registryObjects>";
	private $cerif = null;
	private $rifcs = null;
	private $cfData = array("cfPAddr"=>array());
	//	private $ignored_tags = array("cfPers");

	function __construct(){
		$this -> rifcs =  simplexml_load_string($this::RIFCS_WRAPPER);
		require_once(REGISTRY_APP_PATH . "core/crosswalks/_CFClasses.php");
	}

	public function identify()
	{
		return "CERIF to RIF-CS Crosswalk";
	}

	public function metadataFormat()
	{
		return "cerif2rifcs_v1";
	}

	public function payloadToRIFCS($payload)
	{
		$this -> cerif =  simplexml_load_string($payload);
		foreach ($this -> cerif -> children() as $node) {
			// 			if(array_search($node -> getName(), $this->ignored_tags) != FALSE)
			// 				 continue;
			$func_name = "process_".$node -> getName();
			if(call_user_func(array($this, $func_name ),$node) == FALSE)
				echo $func_name ." failed!";
		}
		return $this -> rifcs -> asXML();
	}

	private function add_related_object($cerifRelObj, $key, $parent){
		foreach ($cerifRelObj as $cerifRelObj){
			if ($cerifRelObj->getName() != "") {
				$relatedObj = $parent->addChild("relatedObject");
				$key = $relatedObj->addChild("key", (string)$cerifRelObj->{$key});
				$relation = $relatedObj->addChild("relation");
				$relation -> addAttribute("type",  CFClasses::$MAP[(string)$cerifRelObj -> cfClassSchemeId]);
				$relation -> addChild("description", CFClasses::$MAP[(string)$cerifRelObj -> cfClassId]);
			}
		}
	}

	private function process_cfPAddr($node){
		if(isset($this->cfData ["cfPAddr"][(string)$node -> cfPAddrId])){
			$phyAddr = $this->cfData ["cfPAddr"][(string)$node -> cfPAddrId];

			foreach ($node->children() as $child){
				if ($child -> getName() != "cfPAddrId")
					$phyAddr -> addChild("addressPart", (string)$child)-> addAttribute("type",$child -> getName());

			}
		}
		return true;
	}

	private function get_new_registry_object($key){
		$regObj = $this->rifcs -> addChild("registryObject");
		$regObj->addAttribute("group", "CERIF");
		$regObj -> addChild("key",(string)$key);
		$regObj -> addChild("originatingSource", "CERIF");
		return $regObj;
	}

	private function process_cfPers($node){

		$regObj = $this->get_new_registry_object($node->cfPersId);
		$party = $regObj -> addChild("party"); // Create PARTY element in RIF-CS
		$party->addAttribute("type", "person");
		$party->addAttribute("dateModified", date("Y-m-d\TH:i:s\Z"));


		if ($node -> cfPers_PAddr->getName() != "") {			// Keep the party object reference in a map to make further process to get physicall location
			$addr = $party->addChild("location")->addChild("address");
			$phyAddr = $addr->addChild("physical");
			$phyAddr -> addAttribute("type",CFClasses::$MAP[(string)$node->cfPers_PAddr->cfClassId] );
			$this->cfData ["cfPAddr"][(string)$node -> cfPers_PAddr -> cfPAddrId] = $phyAddr;
		}

		if ($node -> cfPers_EAddr->getName() != "") {
			$eAddr = $addr->addChild("electronic");
			$eAddr-> addAttribute("type", CFClasses::$MAP[(string)$node->cfPers_EAddr->cfClassId]);
			$eAddr->addChild("value", (string)$node->cfPers_EAddr->cfEAddrId);
		}

		$this->add_related_object($node -> cfPers_OrgUnit, "cfOrgUnitId",$party);
		$this->add_related_object($node -> cfPers_Pers,"cfPersId2",$party);
		$this->add_related_object($node -> cfPers_ResPubl, "cfResPublId",$party);
		$this->add_related_object($node -> cfProj_Pers, "cfProjId",$party);
		$this->add_related_object($node -> cfPers_Event,"cfEventId",$party);
		$this->add_related_object($node -> cfPers_ResProd, "cfResProdId",$party);
		$this->add_related_object($node -> cfPers_ResPat, "cfResPatId",$party);
		$this->add_related_object($node -> cfPers_Srv, "cfSrvId",$party);


		$party->addChild("identifier", $node->cfPersId)-> addAttribute("type","local");

		foreach ($node -> cfPersName_Pers as $cfPersName_Pers) {
			$name = $party->addChild("name");
			$name -> addAttribute("dateFrom",(string)$cfPersName_Pers->cfStartDate);
			$name -> addAttribute("type", CFClasses::$MAP[(string)$cfPersName_Pers->cfClassId]);
			$name -> addChild("namePart",(string)$cfPersName_Pers->cfFamilyNames) -> addAttribute("type","family");
			$name -> addChild("namePart",(string)$cfPersName_Pers->cfFirstNames) -> addAttribute("type","given");
		}
		if($node->cfKeyw->getName()!="")
			$this-> add_subjects($node->cfKeyw, $party );

		return true;
	}

	private function process_cfEvent($node){
		return true;
	}

	private function process_cfSrv($node){
		return true;
	}

	private function add_subjects($cerif_kw_subj, $rifcs_elem){

		foreach (explode(";", (string)$cerif_kw_subj) as $kw)
			$rifcs_elem -> addChild("subject",$kw)->addAttribute("type", "Subject Keywords");
	}
	private function process_cfProj($node){

		$regObj = $this -> get_new_registry_object($node->cfProjId);
		$activity = $regObj -> addChild("activity"); // Create PARTY element in RIF-CS
		$activity->addAttribute("type", "project");
		$activity->addAttribute("dateModified", date("Y-m-d\TH:i:s\Z"));

		$name = $activity->addChild("name");
		$name -> addAttribute("type", "primary");
		$name -> addChild("namePart",(string)$node->cfTitle);

		$activity -> addChild("description", (string)$node->cfAbstr)->addAttribute("type", "brief");

		$this->add_related_object($node -> cfProj_Pers, "cfPersId",$activity);
		$this->add_related_object($node -> cfProj_OrgUnit, "cfOrgUnitId",$activity);
		$this->add_related_object($node -> cfProj_Fund, "cfFundId",$activity);
		$this->add_related_object($node -> cfProj_ResPubl, "cfResPublId",$activity);
		$this->add_related_object($node -> cfProj_Meas, "cfMeasId",$activity);

		$this-> add_subjects($node->cfKeyw, $activity );
		return true;
	}

	private function process_cfOrgUnit($node){
		return true;
	}

}


// function test(){

// // 	$cerifFile = "/Users/mahmoud/cfRMAS_HR.xml";
// 		$cerifFile = "/Users/mahmoud/cfRMAS_Project.xml";
// 	$cerif = file_get_contents($cerifFile);

// 	$cerif2rifcs = new CERIF_2_RIFCS();
// 	$rifcs = $cerif2rifcs -> payloadToRIFCS($cerif);

// // 	$myFile = "/Users/mahmoud/cfRMAS_HR.rifcs.xml";
// 	$myFile = "/Users/mahmoud/cfRMAS_Project.rifcs.xml";
// 	$fh = fopen($myFile, 'w') or die("can't open file");
// 	fwrite($fh, $rifcs);
// 	fclose($fh);
// }

// test();
