<?xml version="1.0"?>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:param name="dataSource"/>
    <xsl:param name="reverseLinks"/>
    <xsl:param name="output" select="'script'"/>
    <xsl:param name="relatedObjectClassesStr" select="''" />

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="ro:registryObjects">
	    <xsl:choose>
		    <xsl:when test="$output = 'script'">
		        <!-- <script> -->
					<xsl:apply-templates select="ro:registryObject"/>
					<!-- <xsl:text>$("#tab_preview").delay(1100).show();</xsl:text> -->
				<!-- </script> -->
		    </xsl:when>
		    <xsl:otherwise>
		    	<div class="quality-test-results">
		    		<xsl:apply-templates select="ro:registryObject"/>
		    	</div>
		    </xsl:otherwise>
	    </xsl:choose>
    </xsl:template>
     
    <!-- REGISTRY OBJECT CHECKS -->
    <xsl:template match="ro:registryObject">
        <xsl:if test="string-length(ro:collection/@type) = 0 and string-length(ro:activity/@type) = 0 and string-length(ro:party/@type) = 0 and string-length(ro:service/@type) = 0">
           <xsl:choose>
			    <xsl:when test="$output = 'script'">
					<xsl:text>SetErrors("tab_mandatoryInformation_type","Type must be specified");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Registry Object Type must be specified</span>
			    </xsl:otherwise>
	    	</xsl:choose>           
        </xsl:if>
        <xsl:if test="string-length(ro:collection/@type) &gt; 32 or string-length(ro:activity/@type) &gt; 32 or string-length(ro:party/@type) &gt; 32 or string-length(ro:service/@type) &gt; 32">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_type","Type must be less than 32 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Registry Object Type must be less than 32 characters</span>
			    </xsl:otherwise>
	    	</xsl:choose>          
        </xsl:if>
        <xsl:if test="string-length($dataSource) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_dataSource","A Data Source must be selected for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A Data Source must be selected for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:key) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_key","A valid key must be specified for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A valid key must be specified for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:key) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_key","Key must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Key must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@group) = 0">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_group","A group must be specified for this record");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A group must be specified for this record</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@group) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("tab_mandatoryInformation_group","A group must be less then 512 character");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A group must be less then 512 character</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service" />
    </xsl:template>
    
    <!--  COLLECTION/PARTY/ACTIVITY LEVEL CHECKS -->
    <xsl:template match="ro:collection">
    <xsl:variable name="CP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Collection, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="CA_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Activity to the Collection, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_names","At least one primary name is required for the Collection record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Collection record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_descriptions_rights","At least one description (brief and/or full) is required for the Collection.","REQ_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description (brief and/or full) is required for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
       <xsl:if test="not(ro:description[@type='rights']) and not(ro:description[@type='accessRights']) and not(ro:rights)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_descriptions_rights","At least one description of the rights, licences or access rights relating to the Collection is required.","REQ_RIGHT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description of the rights, licences or access rights relating to the Collection is required.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:location/ro:address)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_locations","At least one location address is required for the Collection.","REQ_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one location address is required for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>  
        <xsl:if test="not(ro:dates/ro:date)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_dates","At least one dates element is recommended for the Collection.","REC_DATES");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one dates element is recommended for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity']) and $output = 'script'">
            <xsl:text>SetInfos("tab_relatedObjects","The Collection must be related to at least one Activity record where available.</xsl:text><xsl:value-of select="$CA_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_ACTIVITY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity']) and $output = 'html'">
			<span class="info">The Collection must be related to at least one Activity record where available.<xsl:value-of select="$CA_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetWarnings("tab_relatedObjects","The Collection must be related to at least one Party record.</xsl:text><xsl:value-of select="$CP_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_PARTY");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="warning">The Collection must be related to at least one Party record.<xsl:value-of select="$CP_roError_cont"/></span>
        </xsl:if>
  	
        <xsl:if test="not(ro:identifier)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_identifiers","At least one identifier is recommended for the Collection.","REC_IDENTIFIER");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one identifier is recommended for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_subjects","At least one subject (e.g. anzsrc-for code) is recommended for the Collection.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Collection.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:coverage/ro:spatial)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_coverages","At least one spatial coverage for the Collection is recommended.","REC_SPATIAL_COVERAGE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one spatial coverage for the Collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:coverage/ro:temporal/ro:date[@type='dateFrom']) and not(ro:coverage/ro:temporal/ro:date[@type = 'dateTo'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_coverages","At least one temporal coverage entry for the collection is recommended.","REC_TEMPORAL_COVERAGE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one temporal coverage entry for the collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
         <xsl:if test="not(ro:citationInfo)">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_citationInfos","Citation data for the collection is recommended.","REC_CITATION");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Citation data for the collection is recommended.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>         
        <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:accessPolicy | ro:rights | ro:existenceDates | ro:citationInfo | ro:dates"/>
   </xsl:template>
    
    <xsl:template match="ro:party">
    <xsl:variable name="PC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Party, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="PA_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Activity to the Party, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>

	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_names","At least one primary name is required for the Party record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Party record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:identifier)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_identifiers","At least one identifier is recommended for the Party.","REC_IDENTIFIER");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one identifier is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_locations","At least one location address is recommended for the Party.","REC_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one location address is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
               
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity']) and $output = 'script'">
            <xsl:text>SetInfos("tab_relatedObjects","It is recommended that the Party be related to at least one Activity record.</xsl:text><xsl:value-of select="$PA_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_ACTIVITY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Activity') or ro:relatedObject/ro:key[@roclass = 'Activity'] or ro:relatedObject/ro:key[@roclass = 'activity']) and $output = 'html'">
			<span class="info">It is recommended that the Party be related to at least one Activity record.<xsl:value-of select="$PA_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetWarnings("tab_relatedObjects","The Party must be related to at least one Collection record.</xsl:text><xsl:value-of select="$PC_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="warning">The Party must be related to at least one Collection record.<xsl:value-of select="$PC_roError_cont"/></span>
        </xsl:if>
                      
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_descriptions_rights","At least one description (brief and/or full) is recommended for the Party.","REC_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one description (brief and/or full) is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_subjects","At least one subject (e.g. anzsrc-for code) is recommended for the Party.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:existenceDates)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_existencedates","Existence dates are recommended for the Party.","REC_EXISTENCEDATE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Existence dates are recommended for the Party.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>        
        <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:relatedInfo | ro:rights | ro:existenceDates  | ro:dates"/>
    </xsl:template>
    
    
    <xsl:template match="ro:activity">
    <xsl:variable name="AC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Activity, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="AP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Activity, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_names","At least one primary name is required for the Activity record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Activity record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_descriptions_rights","At least one description (brief and/or full) is required for the Activity.","REQ_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one description (brief and/or full) is required for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_locations","At least one location address is recommended for the Activity.","REC_LOCATION_ADDRESS");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one location address is recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetWarnings("tab_relatedObjects","The Activity must be related to at least one Party record.</xsl:text><xsl:value-of select="$AP_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_PARTY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="warning">The Activity must be related to at least one Party record.<xsl:value-of select="$AP_roError_cont"/></span>
        </xsl:if>
              
       <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetInfos("tab_relatedObjects","The Activity must be related to at least one Collection record if available.</xsl:text><xsl:value-of select="$AC_roError_cont"/><xsl:text>","REC_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="info">The Activity must be related to at least one Collection record if available.<xsl:value-of select="$AC_roError_cont"/></span>
        </xsl:if>             
        <xsl:if test="not(ro:subject) or not(ro:subject[string-length(.) &gt; 0] and ro:subject[string-length(@type) &gt; 0])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_subjects","At least one subject (e.g. anzsrc-for code) is recommended for the Activity.","REC_SUBJECT");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one subject (e.g. anzsrc-for code) is recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:existenceDates)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_existencedates","Existence dates are recommended for the Activity.","REC_EXISTENCEDATE");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">Existence dates are recommended for the Activity.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>              
         <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:relatedInfo | ro:rights | ro:existenceDates  | ro:dates"/>
    </xsl:template>
    
    
    <xsl:template match="ro:service">
    <xsl:variable name="SC_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Collection to the Service, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
    <xsl:variable name="SP_roError_cont">
	<xsl:if test="$reverseLinks = 'true'">
	<xsl:text> &lt;i&gt;If you have created the relationship from the Party to the Service, please ignore this message.&lt;/i&gt;</xsl:text>
	</xsl:if>
    </xsl:variable>
	<xsl:if test="not(ro:name[@type='primary'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_names","At least one primary name is required for the Service record.","REQ_PRIMARY_NAME");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one primary name is required for the Service record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party']) and $output = 'script'">
            <xsl:text>SetInfos("tab_relatedObjects","It is recommended that the Service be related to at least one Party record.</xsl:text><xsl:value-of select="$SP_roError_cont"/><xsl:text>", "REC_RELATED_OBJECT_PARTY");</xsl:text>
		</xsl:if>
		
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Party') or ro:relatedObject/ro:key[@roclass = 'Party'] or ro:relatedObject/ro:key[@roclass = 'party']) and $output = 'html'">
			<span class="info">It is recommended that the Service be related to at least one Party record.<xsl:value-of select="$SP_roError_cont"/></span>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection']) and $output = 'script'">
            <xsl:text>SetWarnings("tab_relatedObjects","The Service must be related to at least one Collection record.</xsl:text><xsl:value-of select="$SC_roError_cont"/><xsl:text>","REQ_RELATED_OBJECT_COLLECTION");</xsl:text>
        </xsl:if>
        
        <xsl:if test="not(contains($relatedObjectClassesStr, 'Collection') or ro:relatedObject/ro:key[@roclass = 'Collection'] or ro:relatedObject/ro:key[@roclass = 'collection']) and $output = 'html'">
			<span class="warning">The Service must be related to at least one Collection record.<xsl:value-of select="$SC_roError_cont"/></span>
        </xsl:if> 
               
        <xsl:if test="not(ro:description[@type='brief']) and not(ro:description[@type='full'])">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_descriptions_rights","At least one description (brief and/or full) is recommended for the Service.","REC_DESCRIPTION_FULL");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one description (brief and/or full) is recommended for the Service.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        
        <xsl:if test="not(ro:location/ro:address/ro:electronic)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetInfos("tab_locations","At least one electronic address is required for the Service if available.","REC_LOCATION_ADDRESS_ELECTRONIC");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="info">At least one electronic address is required for the Service if available.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>    
     <!--    
        <xsl:if test="not(ro:accessPolicy)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("tab_accessPolicy","At least one Access Policy URL is recommended for the Service record.","REQ_ACCESS_POLICY");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">At least one Access Policy URL is recommended for the Service record.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if> --> 
        
         <xsl:apply-templates select="ro:description | ro:coverage | ro:location | ro:name | ro:identifier | ro:subject | ro:relatedObject | ro:relatedInfo | ro:accessPolicy | ro:rights | ro:existenceDates  | ro:dates"/>
    </xsl:template>
    
    <!-- SERVICE LEVEL CHECKS -->
    
    <!--  SUBJECT CHECKS -->
    <xsl:template match="ro:subject">
        <xsl:choose>
            <xsl:when test="string-length(@type) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	                	<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Type must be less than 512 characters.","type");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="warning">Subject Type must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(@type) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Subject Type must be specified. &lt;span&gt;(e.g. 'anzsrc-for')&lt;/span&gt;","type");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject Type must be specified.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
        	<xsl:when test="string-length(@termIdentifier) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	                	<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Term Identifier must be less than 512 characters.","value");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject Term Identifier must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
            <xsl:when test="string-length(.) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Subject must be less than 512 characters.","value");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="warning">Subject must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(.) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
               			 <xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Subject Value must be entered. &lt;span&gt;(e.g. '0302' (A 4 digit ANZSRC Field of Research code))&lt;/span&gt;","value");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Subject must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    
    <!-- DESCRIPTION CHECKS -->
    <xsl:template match="ro:collection/ro:description | ro:party/ro:description | ro:activity/ro:description | ro:service/ro:description">
        <xsl:choose>
            <xsl:when test="string-length(@type) &gt; 512">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Type must be less than 512 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description Type must be less than 512 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(@type) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Description Type must be specified. &lt;span&gt;(e.g. 'full')&lt;/span&gt;");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description Type must be specified.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
        </xsl:choose>
        <xsl:choose>
            <xsl:when test="string-length(.) &gt; 12000">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
               			 <xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Description Value must be less than 12000 characters.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="warning">Description must be less than 12000 characters.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <xsl:when test="string-length(.) = 0">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Description Value must be entered. ");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when>
            <!-- xsl:when test="string-length(.) &lt; 9">
                <xsl:choose>
				    <xsl:when test="$output = 'script'">
                		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Description Value must be 9 characters or more.");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Description must be 9 characters or more.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
            </xsl:when-->
        </xsl:choose>
    </xsl:template>
    
    
    <!-- NAME CHECKS -->
    <xsl:template match="ro:name">
        <xsl:if test="not(ro:namePart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Each Name must have at least one Name Part.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Each Name must have at least one Name Part.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <!-- 
        <xsl:if test="string-length(@type) = 0">
            <xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name must have a type");</xsl:text>
        </xsl:if>
        -->
        <xsl:if test="string-length(@type) &gt; 512">
        	<xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Name Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:namePart" />
    </xsl:template>
    
    <xsl:template match="ro:namePart">
        <!--xsl:if test="string-length(@type) = 0 and ancestor::ro:party[@type = 'person']">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Type must be specified.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Name Part must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if-->
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
					<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name Part type must be less than 512 characters","type");</xsl:text>          							
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Name Part type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>	    	
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script' and ancestor::ro:activity">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Value must be entered. &lt;span&gt;(e.g. 'Study of bacteria growth in Lake Macquarie 2010-2011')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:collection">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Value must be entered. &lt;span&gt;(e.g. 'Effects of Nicotine on the Human Body')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:service">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Value must be entered. &lt;span&gt;(e.g. 'Australian Mammal Identification Portal')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:when test="$output = 'script' and ancestor::ro:party">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Value must be entered. &lt;span&gt; E.g. 'John')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Name Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Name Part must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!--  IDENTIFIER CHECKS -->
    <xsl:template match="ro:identifier">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Identifier must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Identifier Value must be entered. &lt;span&gt;(e.g. '10.1234/5678' (a DOI))&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Identifier Type must be specified. &lt;span&gt;(e.g. 'doi')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Identifier must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <!-- LOCATION CHECKS -->
    <xsl:template match="ro:location">   
    	<xsl:apply-templates select="ro:address | ro:spatial"/>
    </xsl:template>
    
    <xsl:template match="ro:address">
    	<xsl:apply-templates select="ro:electronic | ro:physical"/>   	
    </xsl:template>
    
    <xsl:template match="ro:electronic">
        <xsl:if test="string-length(ro:value) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Electronic Address must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Electronic Address must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(ro:value) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Electronic Address Value must be entered. &lt;span&gt;(e.g. 'john.doe@example.com' (An email address) )&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Electronic Address Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Type  must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:apply-templates select="ro:arg"/>
    </xsl:template>
   
   
       <xsl:template match="ro:physical">
        <xsl:if test="string-length(@lang) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Physical Address Lang Attribute must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Physical Address Lang Attribute must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:addressPart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Physical Address must have at least one Address Part.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Physical Address must have at least one Address Part.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
	    			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Electronic Address Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:apply-templates select="ro:addressPart"/>
    </xsl:template>
   
   
    <xsl:template match="ro:addressPart">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Address Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Address Part must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Address Part Value must be entered.&lt;span&gt;(e.g. '123 Example Street' (An address line))&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Address Part Type must be specified.&lt;span&gt;(e.g. 'addressLine')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Address Part Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Address Part Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
   <xsl:template match="ro:coverage">
    	<xsl:apply-templates select="ro:temporal| ro:spatial"/>
    </xsl:template>
    
    <xsl:template match="ro:arg">
    	<xsl:if test="not(@required = 'true' or @required = 'false')">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Required must be either true or false.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Required must be either true or false.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(ro:name) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Electronic Address Arg. Name must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Argument Type must be specified. &lt;span&gt;(e.g. 'string')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Arg. Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Argument Value must be entered. &lt;span&gt;(e.g. 'http://www.example.com/createRecord')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Electronic Address Argument must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    	<xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Argument value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Argument value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
  
    
    <!-- RELATED OBJECT CHECKS -->
    <xsl:template match="ro:relatedObject">
        <xsl:if test="not(ro:key)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Key of the Related Object must be specified.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Key of the Related Object must be specified.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:relation)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Object must have a relation specified.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Object must have a relation specified.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:key | ro:relation"/>
    </xsl:template>
  
    <xsl:template match="ro:relatedObject/ro:key">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Key must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Object Key must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Related Object Key must be entered. &lt;span&gt;(e.g. 'exampleKey.1')&lt;/span&gt;","key");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Object Key must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relation">
    
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Relation Type must be specified. &lt;span&gt;(e.g. 'isOwnedBy')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Each Relation type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Relation type must be less than 512 characters","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Relation type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:description | ro:url"/>
    </xsl:template>
    
    <xsl:template match="ro:relation/ro:description">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Relation Description must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Relation Description must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relation/ro:url">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Relation URL must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Relation URL must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!-- RELATED INFO CHECKS -->
    <xsl:template match="ro:relatedInfo">
        <!--xsl:if test="string-length(@type) = 0">
            <xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Each Related Information must have a Type specified");</xsl:text>
        </xsl:if-->
        <xsl:if test="string-length(@type) &gt; 64">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Information Type must be less than 64 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Information Type must be less than 64 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:identifier | ro:relation | ro:title | ro:notes | ro:format"/>
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/ro:identifier">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Related Info Identifier Value must be entered. &lt;span&gt;(e.g. '9780471418450' (an ISBN))&lt;/span&gt;","identifier");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Info Identifier value must be less than 512 characters.","identifier");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Related Info Identifier Type must be specified. &lt;span&gt;(e.g. 'isbn')&lt;/span&gt;","identifier_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Info Identifier Type must be less than 512 characters.","identifier_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/ro:title">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Info Title must be less than 512 characters.","title");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Title must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ro:relatedInfo/ro:format">
        <xsl:if test="not(ro:identifier)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Info Format must have an Identifier");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Format must have an Identifier.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:identifier"/>
    </xsl:template>

    <xsl:template match="ro:format/ro:identifier">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Format Identifier Value must be entered. &lt;span&gt;(e.g. 'application/xml)&lt;/span&gt;","format_identifier");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A Format Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Format Identifier Value must be less than 512 characters.","format_identifier");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Identifier value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Format Identifier Type must be specified. &lt;span&gt;(e.g. 'mediaType')&lt;/span&gt;","format_identifier_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A Format Identifier must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Format Identifier Type must be less than 512 characters.","format_identifier_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">A Format Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ro:relatedInfo/ro:notes">
        <xsl:if test="string-length(.) &gt; 4000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Related Info Notes must be less than 4000 characters.","notes");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Related Info Notes must be less than 4000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <!--  SERVICE ACCESS POLICY CHECKS -->
    <xsl:template match="ro:accessPolicy">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Access Policy value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Policy value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Access Policy must have a value.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Policy must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
     <xsl:template match="ro:coverage/ro:temporal">
        <xsl:apply-templates select="ro:date|ro:text" /> 
     </xsl:template>
    
    <xsl:template match="ro:coverage/ro:temporal/ro:date">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Temporal Coverage Date must have a value.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Temporal Coverage Date value must be less than 512 characters.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Temporal Coverage Date value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Temporal Coverage Date Type must be specified. &lt;span&gt;(e.g. 'dateFrom')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a type.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Temporal Coverage Date type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Temporal Coverage Date Format must be specified. &lt;span&gt;(e.g. 'W3CDTF')&lt;/span&gt;","dateFormat");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date must have a dateFormat.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Temporal Coverage Date dateFormat must be less than 512 characters.","dateFormat");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Temporal Coverage Date dateFormat must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:coverage/ro:temporal/ro:text">
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Temporal Coverage Text value must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Temporal Coverage Text value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:coverage/ro:spatial">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Spatial Coverage Value must be entered.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Spatial Coverage Type must be specified. &lt;span&gt;(e.g. 'gml')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Spatial Coverage Type value must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Coverage Type value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:location/ro:spatial">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Spatial Location Value must be entered.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Spatial Location Type must be specified. &lt;span&gt;(e.g. 'gml')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Spatial Location Type value must be less than 512 characters","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Spatial Location Type value must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationInfo">
    	<xsl:apply-templates select="ro:fullCitation | ro:citationMetadata"/>
    </xsl:template>
    
    <xsl:template match="ro:fullCitation">
    	<xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Full Citation Value must be entered. &lt;span&gt;(e.g. 'Australian Bureau of Agricultural and Resource Economics 2001, Aquaculture development in Australia: a review of key economic issues, ABARE, Canberra.')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Full Citation must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Full Citation must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Full Citation must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@style) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Full Citation Style must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Full Citation Style must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates">
		<xsl:apply-templates select="ro:startDate | ro:endDate"/>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates/ro:startDate">

		<xsl:if test="string-length(@dateFormat) &gt; 0">
	    	<xsl:if test="string-length(.) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Existence Date must have a value.","value");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Existence Start Date must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
	        </xsl:if>
	    </xsl:if>

        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Existence Date must be less than 512 characters.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>

        <xsl:if test="string-length(.) &gt; 0">
	        <xsl:if test="string-length(@dateFormat) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Date Format must be specified. &lt;span&gt;(e.g. 'W3CDTF')&lt;/span&gt;","dateFormat");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Existence Start Date Format must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
	        </xsl:if>
	    </xsl:if>

        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Format must be less than 512 characters.","dateFormat");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence Start Date Format must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:existenceDates/ro:endDate">
    	<xsl:if test="string-length(@dateFormat) &gt; 0">
	    	<xsl:if test="string-length(.) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Existence Date must have a value.","value");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Existence End Date must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
	        </xsl:if>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Existence Date must be less than 512 characters.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Existence End Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>

		<xsl:if test="string-length(.) &gt; 0">
	        <xsl:if test="string-length(@dateFormat) = 0">
	            <xsl:choose>
				    <xsl:when test="$output = 'script'">
	           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Date Format must be specified. &lt;span&gt;(e.g. 'W3CDTF')&lt;/span&gt;","dateFormat");</xsl:text>
				    </xsl:when>
				    <xsl:otherwise>
						<span class="error">Existence End Date Format must have a value.</span>
				    </xsl:otherwise>
		    	</xsl:choose>
	        </xsl:if>
	    </xsl:if>

        <xsl:if test="string-length(@dateFormat) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Format must be less than 512 characters.","dateFormat");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Existence End Date Format must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    
    <xsl:template match="ro:rights">
		<xsl:apply-templates select="ro:rightsStatement | ro:licence | ro:accessRights"/>
    </xsl:template>


    <xsl:template match="ro:dates">
		<xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Dates Type must be specified. &lt;span&gt;(e.g. 'dc.created')&lt;/span&gt;","dates_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Dates Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:date)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Dates Must have at least one Date Value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Dates Must have at least one Date Value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:date"/>
    </xsl:template>

    <xsl:template match="ro:date">
		<xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Type must be specified. &lt;span&gt;(e.g. 'dateFrom')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Dates Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Must have a Value","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Date Must have a Value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>


    <xsl:template match="ro:temporal/ro:date">
		<xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Type must be specified. &lt;span&gt;(e.g. 'dateFrom')&lt;/span&gt;","date_type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Dates Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Must have a Value","date_value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Date Must have a Value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
        
    <xsl:template match="ro:rights/ro:rightsStatement">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Rights Statement must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Rights Statement must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Rights Statement Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:rights/ro:licence">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Licence must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Licence must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Licence Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:rights/ro:accessRights">
        <xsl:if test="string-length(.) &gt; 12000">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Access Rights must be less than 12000 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Access Rights must be less than 12000 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@rightsUri) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Rights URI must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Access Rights URI must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata">
        <xsl:if test="not(ro:contributor)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Citation Metadata must have at least one Contributor.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata must have at least one Contributor.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
		<xsl:apply-templates select="ro:identifier | ro:contributor | ro:title | ro:publisher | ro:date"/>
    </xsl:template>
    
    
    <xsl:template match="ro:citationMetadata/ro:identifier">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Identifier Value must be entered. &lt;span&gt;(e.g. 'exampleHandle/1234' (A handle))&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Identifier must be less than 512 characters.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","An Identifier Type must be specified. &lt;span&gt;(e.g. 'handle')&lt;/span&gt;","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Identifier Type must be less than 512 characters.","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Identifier Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
 
    <xsl:template match="ro:citationMetadata/ro:contributor">
    	<xsl:if test="not(number(@seq)) and @seq != ''">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Sequence must be a number.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Sequence must be a number.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="not(ro:namePart)">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
           			<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Contributor must have at least one namepart.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor must have at least one namepart.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:apply-templates select="ro:namePart"/>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:contributor/ro:namePart">
         <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Name Part Value must be entered. &lt;span&gt;(e.g. 'John Doe')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Name Part must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name Part must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Contributor Name Part must be less than 512 character.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Name Part Type must be less than 512 characters","type");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Contributor Name Part Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:date">
        <xsl:if test="string-length(.) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date must have a value.","citation_date");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(.) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date must be less than 512 characters.","citation_date");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Date must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) = 0">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Date Type must be specified. &lt;span&gt;(e.g. 'publicationDate')&lt;/span&gt;","citation_date");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date Type must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
        <xsl:if test="string-length(@type) &gt; 512">
            <xsl:choose>
			    <xsl:when test="$output = 'script'">
            		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Date Type must be less than 512 characters.","citation_date");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Date Type must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:title">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Title must be entered. &lt;span&gt;(e.g. 'Aquaculture development in Australia')&lt;/span&gt;","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Title must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Title must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Title must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:url">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A URL must be entered. &lt;span&gt;(e.g. 'http://www.example.com')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata URL must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","URL must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata URL must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:context">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Context must be entered. &lt;span&gt;(e.g. 'Aquaculture development database')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Context must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Context must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Context must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:version">
	  <!--  <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Version must be entered. &lt;span&gt;(e.g. '2nd edition')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Version must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if> -->
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Version must be less than 512 characters");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Version must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:publisher">
    	<xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Citation Metadata Publisher must have a Value.","value");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Publisher must have a Value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Publisher must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Publisher must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="ro:citationMetadata/ro:placePublished">
	    <xsl:if test="string-length(.) = 0">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetErrors("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","A Place Published must be entered. &lt;span&gt;(e.g. 'Sydney, Australia')&lt;/span&gt;");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="error">Citation Metadata Place Published must have a value.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
	    <xsl:if test="string-length(.) &gt; 512">
	        <xsl:choose>
			    <xsl:when test="$output = 'script'">
	        		<xsl:text>SetWarnings("</xsl:text><xsl:value-of select="@field_id"/><xsl:text>","Place Published must be less than 512 characters.");</xsl:text>
			    </xsl:when>
			    <xsl:otherwise>
					<span class="warning">Citation Metadata Place Published must be less than 512 characters.</span>
			    </xsl:otherwise>
	    	</xsl:choose>
	    </xsl:if>
    </xsl:template>
    
    <xsl:template match="@* | node()" />
    
    
</xsl:stylesheet>
