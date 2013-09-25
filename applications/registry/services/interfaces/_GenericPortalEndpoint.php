<?php

interface GenericPortalEndpoint 
{
	
	// Bare minimum necessary to display a Registry Object that is in the registry
	public function getRegistryObject();
	public function getConnections();
	public function getSuggestedLinks();

}