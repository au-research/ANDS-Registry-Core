<?xml version="1.0"?>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:param name="relatedObjectClassesStr" select="'PartyCollectionActivityService'"/>
    

    <xsl:template match="/">   
    	<div id="qa_level_results" roKey="{ro:registryObjects/ro:registryObject/ro:key}">
			<xsl:apply-templates select="ro:registryObjects"/>
      	</div>
    </xsl:template>


    <xsl:template match="ro:registryObjects">

		<span class="qa_ok" level="1">Registry Objects</span>
		<xsl:apply-templates select="ro:registryObject"/>
    </xsl:template>
     
    <!-- REGISTRY OBJECT CHECKS -->
    <xsl:template match="ro:registryObject">
    <span class="qa_ok" level="1">Registry Object</span>

        <xsl:choose>
			<xsl:when test="string-length(ro:key) = 0 or string-length(ro:key) &gt; 512">
            	<span class="qa_error" level="1" field_id="errors_mandatoryInformation_key">A valid key must be specified for this record</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_key">A valid key must be specified for this record</span>
			</xsl:otherwise>
	    </xsl:choose>
	    
        <xsl:choose>
			<xsl:when test="string-length(@group) = 0 or string-length(@group) &gt; 512">
            	<span class="qa_error" level="1" field_id="errors_mandatoryInformation_group">A group must be specified for this record</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_group">A group must be specified for this record</span>
			</xsl:otherwise>
	    </xsl:choose>
     
        <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service" />
    </xsl:template>
    
    <!--  COLLECTION/PARTY/ACTIVITY LEVEL CHECKS -->
    <xsl:template match="ro:collection">
    	<span class="qa_ok" level="1">Collection</span>
    	<xsl:choose>
	        <xsl:when test="string-length(@type) = 0 or string-length(@type) &gt; 32">
				<span class="qa_error" level="1" field_id="errors_mandatoryInformation_type">Collection Type must be specified</span>        
	        </xsl:when>
	        <xsl:otherwise>
	        	<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_type">Type must be specified</span>
	        </xsl:otherwise>
        </xsl:choose>

		<xsl:choose>
	        <xsl:when test="not(ro:name[@type='primary'])">
            		<span class="qa_error" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Collection record.</span>
			</xsl:when>
			<xsl:otherwise>
					<span class="qa_ok" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Collection record.</span>
			</xsl:otherwise>
	    </xsl:choose>
	    
	    <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party'])">
				<span class="qa_error" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_PARTY">The Collection must be related to at least one Party record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_PARTY">The Collection must be related to at least one Party record.</span>
			</xsl:otherwise>
	   </xsl:choose>
 	        
        <xsl:choose>
	        <xsl:when test="not(ro:description[@type='brief'][string-length(.) &gt; 0]) and not(ro:description[@type='full'][string-length(.) &gt; 0])">
				<span class="qa_error" level="2" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is required for the Collection. </span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is required for the Collection. </span>
			</xsl:otherwise>
	    </xsl:choose>

       <xsl:choose>
	       	<xsl:when test="not(ro:description[@type='rights']) and not(ro:description[@type='accessRights']) and not(ro:rights)">
				<span class="qa_error" level="2" field_id="errors_description" qa_id="REQ_RIGHT">At least one description of the rights, licences or access rights relating to the Collection is required</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_description" qa_id="REQ_RIGHT">At least one description of the rights, licences or access rights relating to the Collection is required</span>
			</xsl:otherwise>
	   </xsl:choose>
        
        <xsl:choose>
	       	<xsl:when test="not(ro:location/ro:address)">
     			<span class="qa_error" level="2" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is required for the Collection.</span>
			</xsl:when>
			<xsl:otherwise>
     			<span class="qa_ok" level="2" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is required for the Collection.</span>
			</xsl:otherwise>
	   </xsl:choose>
         
        <xsl:choose>
	       	<xsl:when test="not(ro:identifier)">
				<span class="qa_error" level="3" field_id="errors_identifier" qa_id="REC_IDENTIFIER">At least one identifier is recommended for the Collection.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_identifier" qa_id="REC_IDENTIFIER">At least one identifier is recommended for the Collection.</span>
			</xsl:otherwise>
        </xsl:choose>
         
        <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity'])">
				<span class="qa_error" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_ACTIVITY">The Collection must be related to at least one Activity record where available.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_ACTIVITY">The Collection must be related to at least one Activity record where available.</span>
			</xsl:otherwise>
	   </xsl:choose>
        
        <xsl:choose>
	       	<xsl:when test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
				<span class="qa_error" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Collection.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Collection.</span>
			</xsl:otherwise>
        </xsl:choose>
        
        <xsl:choose>
	       	<xsl:when test="not(ro:coverage/ro:spatial)">
				<span class="qa_error" level="3" field_id="errors_coverage" qa_id="REC_SPATIAL_COVERAGE">At least one spatial coverage for the Collection is recommended.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_coverage" qa_id="REC_SPATIAL_COVERAGE">At least one spatial coverage for the Collection is recommended.</span>
			</xsl:otherwise>
	    </xsl:choose>
   
        
        <xsl:choose>
	       	<xsl:when test="not(ro:coverage/ro:temporal/ro:date[@type='dateFrom']) and not(ro:coverage/ro:temporal/ro:date[@type = 'dateTo'])">
				<span class="qa_error" level="3" field_id="errors_coverage" qa_id="REC_TEMPORAL_COVERAGE">At least one temporal coverage entry for the collection is recommended.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_coverage" qa_id="REC_TEMPORAL_COVERAGE">At least one temporal coverage entry for the collection is recommended.</span>
			</xsl:otherwise>
	   </xsl:choose>

        
	    <xsl:choose>
	       	<xsl:when test="not(ro:citationInfo)">
	       		<span class="qa_error" level="3" field_id="errors_citationInfo" qa_id="REC_CITATION">Citation data for the collection is recommended.</span>
			</xsl:when>
			<xsl:otherwise>
	       		<span class="qa_ok" level="3" field_id="errors_citationInfo" qa_id="REC_CITATION">Citation data for the collection is recommended.</span>
			</xsl:otherwise>
	    </xsl:choose>

    	<xsl:choose>
		    <xsl:when test="not(ro:dates/ro:date)">
		    	<span class="qa_error" level="3" field_id="errors_citationInfo" qa_id="REC_DATES">At least one dates element is recommended for the Collection.</span>
		    </xsl:when>
		    <xsl:otherwise>
		    	<span class="qa_ok" level="3" field_id="errors_citationInfo" qa_id="REC_DATES">At least one dates element is recommended for the Collection.</span>
		    </xsl:otherwise>
    	</xsl:choose>
	 </xsl:template>
    
    <xsl:template match="ro:party">
        <span class="qa_ok" level="1">Party</span>
        	
    	<xsl:choose>
	        <xsl:when test="string-length(@type) = 0 or string-length(@type) &gt; 32">
				<span class="qa_error" level="1" field_id="errors_mandatoryInformation_type">Party Type must be specified</span>        
	        </xsl:when>
	        <xsl:otherwise>
	        	<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_type">Party Type must be specified</span>
	        </xsl:otherwise>
        </xsl:choose>
   
    	<xsl:choose>
	        <xsl:when test="not(ro:name[@type='primary'])">
            		<span class="qa_error" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Party record.</span>
			</xsl:when>
			<xsl:otherwise>
					<span class="qa_ok" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Party record.</span>
			</xsl:otherwise>
	    </xsl:choose>
	    
	    <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection'])">
				<span class="qa_error" level="2" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_COLLECTION">The Party must be related to at least one Collection record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_COLLECTION">The Party must be related to at least one Collection record.</span>
			</xsl:otherwise>
	   </xsl:choose>
	          
        <xsl:choose>
	       	<xsl:when test="not(ro:identifier)">
				<span class="qa_error" level="3" field_id="errors_identifier" qa_id="REC_IDENTIFIER">At least one identifier is recommended for the Party.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_identifier" qa_id="REC_IDENTIFIER">At least one identifier is recommended for the Party.</span>
			</xsl:otherwise>
        </xsl:choose>
        
        <xsl:choose>
	       	<xsl:when test="not(ro:location/ro:address)">
     			<span class="qa_error" level="3" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is recommended for the Party.</span>
			</xsl:when>
			<xsl:otherwise>
     			<span class="qa_ok" level="3" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is recommended for the Party.</span>
			</xsl:otherwise>
	   </xsl:choose>
         
       <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity'])">
				<span class="qa_error" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_ACTIVITY">It is recommended that the Party be related to at least one Activity record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_ACTIVITY">It is recommended that the Party be related to at least one Activity record.</span>
			</xsl:otherwise>
	   </xsl:choose>

        <xsl:choose>
	        <xsl:when test="not(ro:description[@type='brief'][string-length(.) &gt; 0]) and not(ro:description[@type='full'][string-length(.) &gt; 0])">
				<span class="qa_error" level="3" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is recommended for the Party.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is recommended for the Party.</span>
			</xsl:otherwise>
	    </xsl:choose>

        <xsl:choose>
	       	<xsl:when test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
				<span class="qa_error" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Party.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Party.</span>
			</xsl:otherwise>
        </xsl:choose>

        <xsl:choose>
	       	<xsl:when test="not(ro:existenceDates)">
				<span class="qa_error" level="3" field_id="errors_existenceDates" qa_id="REC_EXISTENCEDATE">Existence dates are recommended for the Party.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_existenceDates" qa_id="REC_EXISTENCEDATE">Existence dates are recommended for the Party.</span>
			</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    
    <xsl:template match="ro:activity">
        <span class="qa_ok" level="1">Activity</span>
        	
    	<xsl:choose>
	        <xsl:when test="string-length(@type) = 0 or string-length(@type) &gt; 32">
				<span class="qa_error" level="1" field_id="errors_mandatoryInformation_type">Activity Type must be specified</span>        
	        </xsl:when>
	        <xsl:otherwise>
	        	<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_type">Activity Type must be specified</span>
	        </xsl:otherwise>
        </xsl:choose>
    
    	<xsl:choose>
	        <xsl:when test="not(ro:name[@type='primary'])">
            		<span class="qa_error" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Activity record.</span>
			</xsl:when>
			<xsl:otherwise>
					<span class="qa_ok" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Activity record.</span>
			</xsl:otherwise>
	    </xsl:choose>
        
		<xsl:choose>
	        <xsl:when test="not(ro:description[@type='brief'][string-length(.) &gt; 0]) and not(ro:description[@type='full'][string-length(.) &gt; 0])">
				<span class="qa_error" level="2" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is required for the Activity.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_description" qa_id="REQ_DESCRIPTION_FULL">At least one description (brief and/or full) is required for the Activity.</span>
			</xsl:otherwise>
	    </xsl:choose>

	     
           
        <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party'])">
				<span class="qa_error" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_PARTY">The Activity must be related to at least one Party record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_PARTY">The Activity must be related to at least one Party record.</span>
			</xsl:otherwise>
	   </xsl:choose>
	   
	   <xsl:choose>
	       	<xsl:when test="not(ro:location/ro:address)">
     			<span class="qa_error" level="3" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is recommended for the Activity.</span>
			</xsl:when>
			<xsl:otherwise>
     			<span class="qa_ok" level="3" field_id="errors_location" qa_id="REQ_LOCATION_ADDRESS">At least one location address is recommended for the Activity.</span>
			</xsl:otherwise>
	   </xsl:choose>
	   
	    <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection'])">
				<span class="qa_error" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_COLLECTION">The Activity must be related to at least one Collection record if available.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_COLLECTION">The Activity must be related to at least one Collection record if available.</span>
			</xsl:otherwise>
	   </xsl:choose>
	   
	   <xsl:choose>
	       	<xsl:when test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
				<span class="qa_error" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Activity.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_subject" qa_id="REC_SUBJECT">At least one subject (e.g. anzsrc-for code) is recommended for the Activity.</span>
			</xsl:otherwise>
        </xsl:choose>

        <xsl:choose>
	       	<xsl:when test="not(ro:existenceDates)">
				<span class="qa_error" level="3" field_id="errors_existenceDates" qa_id="REC_EXISTENCEDATE">Existence dates are recommended for the Activity.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_existenceDates" qa_id="REC_EXISTENCEDATE">Existence dates are recommended for the Activity.</span>
			</xsl:otherwise>
        </xsl:choose>
	</xsl:template>
    
    
    <xsl:template match="ro:service">
    
        <span class="qa_ok" level="1">Service</span>
        	
    	<xsl:choose>
	        <xsl:when test="string-length(@type) = 0 or string-length(@type) &gt; 32">
				<span class="qa_error" level="1" field_id="errors_mandatoryInformation_type">Service Type must be specified</span>        
	        </xsl:when>
	        <xsl:otherwise>
	        	<span class="qa_ok" level="1" field_id="errors_mandatoryInformation_type">Service Type must be specified</span>
	        </xsl:otherwise>
        </xsl:choose>
    	
    	<xsl:choose>
	        <xsl:when test="not(ro:name[@type='primary'])">
            		<span class="qa_error" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Service record.</span>
			</xsl:when>
			<xsl:otherwise>
					<span class="qa_ok" level="2" field_id="errors_name" qa_id="REQ_PRIMARY_NAME">At least one primary name is required for the Service record.</span>
			</xsl:otherwise>
	    </xsl:choose>    

        
  
	    <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection'])">
				<span class="qa_error" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_COLLECTION">The Service must be related to at least one Collection record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="2" field_id="errors_relatedObject" qa_id="REQ_RELATED_OBJECT_COLLECTION">The Service must be related to at least one Collection record.</span>
			</xsl:otherwise>
	   </xsl:choose>     

	   <xsl:choose>
	       	<xsl:when test="not(ro:location/ro:address/ro:electronic)">
     			<span class="qa_error" level="3" field_id="errors_location" qa_id="REC_LOCATION_ADDRESS_ELECTRONIC">At least one electronic address is required for the Service if available.</span>
			</xsl:when>
			<xsl:otherwise>
     			<span class="qa_ok" level="3" field_id="errors_location" qa_id="REC_LOCATION_ADDRESS_ELECTRONIC">At least one electronic address is required for the Service if available.</span>
			</xsl:otherwise>
	   </xsl:choose>
	    
       <xsl:choose>
	       	<xsl:when test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party'])">
				<span class="qa_error" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_PARTY">It is recommended that the Service be related to at least one Party record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_relatedObject" qa_id="REC_RELATED_OBJECT_PARTY">It is recommended that the Service be related to at least one Party record.</span>
			</xsl:otherwise>
	   </xsl:choose> 
	   
		<xsl:choose>
	        <xsl:when test="not(ro:description[@type='brief'][string-length(.) &gt; 0]) and not(ro:description[@type='full'][string-length(.) &gt; 0])">
				<span class="qa_error" level="3" field_id="errors_description" qa_id="REC_DESCRIPTION_FULL">At least one description (brief and/or full) is recommended for the Service.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_description" qa_id="REC_DESCRIPTION_FULL">At least one description (brief and/or full) is recommended for the Service.</span>
			</xsl:otherwise>
	    </xsl:choose>
	    
	    <xsl:choose>
	        <xsl:when test="not(ro:accessPolicy)">
				<span class="qa_error" level="3" field_id="errors_description" qa_id="REQ_ACCESS_POLICY">At least one Access Policy URL is recommended for the Service record.</span>
			</xsl:when>
			<xsl:otherwise>
				<span class="qa_ok" level="3" field_id="errors_description" qa_id="REQ_ACCESS_POLICY">At least one Access Policy URL is recommended for the Service record.</span>
			</xsl:otherwise>
	    </xsl:choose>
                       
    </xsl:template>
       
</xsl:stylesheet>
