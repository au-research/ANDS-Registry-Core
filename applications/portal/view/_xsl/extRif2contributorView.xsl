<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dataSource" select="//extRif:extendedMetadata/extRif:dataSourceKey"/>
    <xsl:param name="dateCreated"/>
    <xsl:param name="base_url" select="'https://test.ands.org.au/orca/'"/>  
    <xsl:param name="orca_view"/>  
    <xsl:param name="theGroup"/>
    <xsl:param name="key"/>        
    <xsl:variable name="objectClass" >
        <xsl:choose>
            <xsl:when test="//ro:collection">Collection</xsl:when>
            <xsl:when test="//ro:activity">Activity</xsl:when>
            <xsl:when test="//ro:party">Party</xsl:when>
            <xsl:when test="//ro:service">Service</xsl:when>            
        </xsl:choose>       
    </xsl:variable>
    <xsl:variable name="objectClassType" >
      <xsl:choose>
         <xsl:when test="//ro:collection">collections</xsl:when>
         <xsl:when test="//ro:activity">activities</xsl:when>
         <xsl:when test="//ro:party/@type='group'">party_multi</xsl:when>
         <xsl:when test="//ro:party/@type='person'">party_one</xsl:when>		
         <xsl:when test="//ro:party">party_multi</xsl:when>	
         <xsl:when test="//ro:service">services</xsl:when>	
     </xsl:choose>		
 </xsl:variable>			

    <xsl:template match="ro:registryObject">
        <!--  We will first set up the breadcrumb menu for the page -->   
     <span id="originating_source" class="hide"><xsl:value-of select="$dataSource"/></span>

    <xsl:variable name="group">	
      <xsl:choose>
          <xsl:when test="string-length(./@group)>30">
              <xsl:value-of select="substring(./@group,0,30)"/>...
          </xsl:when>
          <xsl:otherwise>
              <xsl:value-of select="./@group"/>
          </xsl:otherwise>
      </xsl:choose>
  </xsl:variable>	

  <xsl:variable name="theTitle">	
      <xsl:choose>
          <xsl:when test="string-length(/extRif:extendedMetadata/extRif:displayTitle)>30">
              <xsl:value-of select="substring(/extRif:extendedMetadata/extRif:displayTitle,0,30)"/>...
          </xsl:when>
          <xsl:otherwise>
              <xsl:value-of select="/extRif:extendedMetadata/extRif:displayTitle"/>
          </xsl:otherwise>
      </xsl:choose>
  </xsl:variable>

   <div class="breadcrumb">
    <a href="{$base_url}">Home </a>
        <xsl:text>  /  </xsl:text>
    <a>
    <xsl:attribute name="href">
        <xsl:value-of select="$base_url"/><xsl:value-of select="//extRif:extendedMetadata/extRif:slug"/>
    </xsl:attribute>
    <xsl:value-of select="$group"/></a>
       <xsl:text>  /  </xsl:text>
       <a>
      <xsl:attribute name="href">
        <xsl:value-of select="$base_url"/>search/#!/group=<xsl:value-of select="./@group"/>/tab=<xsl:value-of select="translate($objectClass,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')"/>
      </xsl:attribute>
      <xsl:attribute name="class">crumb</xsl:attribute>
      <xsl:value-of select="$objectClass"/>
    </a>
  </div>	

            <!--  the following hidden elements dfine content to be used in further ajax calls -->
            <div id="registryObjectMetadata" class="hide">
                <div id="group_value"><xsl:value-of select="@group"/></div>
                <div id="datasource_key"><xsl:value-of select="$dataSource"/></div>
                <div id="key_value"></div>
                <div id="class"><xsl:value-of select="$objectClass"/></div>       
                <span id="key"><xsl:value-of select="ro:key"/></span>
                <span id="status"><xsl:value-of select="//extRif:extendedMetadata/extRif:status"/></span>
                <span id="slug"><xsl:value-of select="//extRif:extendedMetadata/extRif:slug"/></span>
                <span id="registry_object_id"><xsl:value-of select="//extRif:extendedMetadata/extRif:id"/></span>
                <span id="class_type"><xsl:value-of select="$objectClassType"/></span>
            </div>
			

        <xsl:apply-templates select="ro:party"/>
    
    </xsl:template>

    <xsl:template match="ro:party">

         <div class="main" id="item-view-inner" itemscope="" itemType="http://schema.org/Thing">

	 	<!--div id="tag_view" class="hide">
	 	<span id="tag_lists">
			<xsl:for-each select="//extRif:tags/extRif:tag">
				<xsl:if test="position()&gt;1">	| </xsl:if>
		 		<span class="tag_text"> <xsl:value-of select="."/></span>
			</xsl:for-each>
			</span>
		<div id="add_tag_button"><input type="button" id="tag_add" value="Add Tag"/></div> 
			<div id="add_tag_form">
			<div id='tag_close'> <br /></div>
				<p><input type="text" name="new_tag" id="new_tag" value=""/><br />
				<span id='tagError' class='tagFormError'><br /></span></p>
				 <div id="captcha_id"></div>
				<p><input type="button" id="tag_submit" name="tag_submit" value="Submit"/></p>
			</div>
		</div-->    

      <xsl:choose>

       <xsl:when test="../extRif:extendedMetadata/extRif:displayTitle!=''">
          <xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayTitle"/>	
      </xsl:when>
      <xsl:otherwise>
 

   <div class="page_title" id="displaytitle">
       <h1><xsl:value-of select="../ro:key"/>.....</h1>



      </div>



</xsl:otherwise> 

</xsl:choose>    


<!-- DISPLAY ALTERNATE TITLES/NAMES -->
<xsl:apply-templates select="ro:name[@type='alternative']/ro:displayTitle"/>

<div class="post">

    <!-- DISPLAY DESCRIPTIONS -->


            <div class="descriptions" style="position:relative;clear:both;">
            	<xsl:if test="../extRif:extendedMetadata/extRif:description/@type='brief'"><h2>Overview</h2></xsl:if>
				<xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'brief']" mode="content"/>
				<xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'full']" mode="content"/>
            	<xsl:if test="../extRif:extendedMetadata/extRif:description/@type='researchAreas'"><h2>Research and Key Research Areas</h2></xsl:if>				
				<xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'researchAreas']" mode="content"/>
				 <h2>Research Data Profile</h2>
                 %%%%CANNED_TEXT%%%%
				<xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'researchDataProfile']" mode="content"/>	
	            <xsl:if test="../extRif:extendedMetadata/extRif:description/@type='researchSupport'"><h2>Research Support</h2></xsl:if>						
				<xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'researchSupport']" mode="content"/>							
            </div>   

  <xsl:if test="ro:identifier">
            <div style="position:relative;clear:both;"><p><b>Identifiers:</b></p>
           	 	<div id="identifiers">

    			<p> 	
    			<xsl:apply-templates select="ro:identifier[@type='doi']" mode = "doi"/>
    			<xsl:apply-templates select="ro:identifier[@type='ark']" mode = "ark"/>    	
     			<xsl:apply-templates select="ro:identifier[@type='AU-ANL:PEAU']" mode = "nla"/>  
     			<xsl:apply-templates select="ro:identifier[@type='handle']" mode = "handle"/>   
     			<xsl:apply-templates select="ro:identifier[@type='purl']" mode = "purl"/>
    			<xsl:apply-templates select="ro:identifier[@type='uri']" mode = "uri"/> 
 				<xsl:apply-templates select="ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>											   	
   				</p>
	
            	</div>
            </div>
        </xsl:if>   

 </div>

</div>	


      	<!--
          REMOVED BY BEN - NOT SURE WHAT'S GOING ON HERE, BUT IT BREAKS THE LAYOUT COMPLETELY
        
        <div id="item-view-inner" class="clearfix">
	
        <div class="clearfix"></div>  

        <xsl:apply-templates select="ro:name[@type='alternative']/ro:displayTitle"/>
  
		<div style="position:relative;clear:both;">
		<hr class="grey"/>
		<p>
			<div class='rss_icon' style="margin-top:2px;"></div> Subscribe to a feed of collections from this contributor. 
			<a>
			<xsl:attribute name="href">
				<xsl:value-of select="$base_url"/>
				<xsl:text>search/rss/?q=*:*&amp;classFilter=collection&amp;typeFilter=All&amp;groupFilter=</xsl:text>
				<xsl:value-of select="//@group"/>			
				<xsl:text>&amp;subjectFilter=All&amp;licenceFilter=All&amp;subscriptionType=rss</xsl:text> 
			</xsl:attribute>
			RSS
			</a>
			/
			<a> 
			<xsl:attribute name="href">
				<xsl:value-of select="$base_url"/>
				<xsl:text>search/atom/?q=*:*&amp;classFilter=collection&amp;typeFilter=All&amp;groupFilter=</xsl:text>
				<xsl:value-of select="//@group"/>			
				<xsl:text>&amp;subjectFilter=All&amp;licenceFilter=All&amp;subscriptionType=atom</xsl:text> 
			</xsl:attribute>			
			ATOM
			</a>
		</p>
       	</div>
     	</div> -->
     	
        <!--  we will now transform the rights handside stuff -->



<!--  we will now transform the rights handside stuff -->
<div class="sidebar">
<h3 id="draft_status" class="hide" style="color:#FF6688;">DRAFT PREVIEW</h3>
 		<xsl:if test="ro:location/ro:address/ro:electronic/@type='url' or ro:location/ro:address/ro:electronic/@type='email'  or ro:location/ro:address/ro:physical">		
		    <div class="right-box">
			<h2>Contact</h2>
			<div class="limitHeight300">
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='url'">
				<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic"/></p>	
	 		</xsl:if>
	 				
		 	<xsl:if test="ro:location/ro:address/ro:electronic/@type='email' or ro:location/ro:address/ro:physical">
		 		<xsl:if test="ro:location/ro:address/ro:electronic/@type='email'">
					<p><xsl:apply-templates select="ro:location/ro:address/ro:electronic/@type"/></p>	
				</xsl:if>
		
		 		<xsl:if test="ro:location/ro:address/ro:physical">
					<p>
					<xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='telephoneNumber'">
						<xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='telephoneNumber']">
							<p>Ph:	<xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
						</xsl:for-each>
					</xsl:if>
						
					<xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='faxNumber'">
						<xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='faxNumber']">
							<p>Fax:<xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
						</xsl:for-each>
					</xsl:if>	
					
					<xsl:apply-templates select="ro:location/ro:address/ro:physical"/></p>	
				</xsl:if>				
	 		</xsl:if>			
			                        
			</div>
		</div>					
		</xsl:if>
				
			<!-- Registry Content -->
      				%%%%CONTENTS%%%%			

			<!-- Top 5 Most Visited Collections: -->
		<!--	<div class="right-box" id="visitedRightBox">
	
			<h3>Top 5 Most Visited Collections</h3>
			<div id="collectionsVisited">
			%%%%COLLECTIONS_VISITED%%%%
			</div>
			</div> -->
			
				<!-- Top 5 Most Cited Collections: -->
		<!--	<div class="right-box" id="citedRightBox">
	
			<h3>Top 5 Most Cited Collections</h3>
			<div id="collectionsCited">
				%%%%COLLECTIONS_CITED%%%%
			</div>
			</div>		-->		

</div> 
<div class="container_clear"></div>
<div class="border"></div>











                 				
        
    </xsl:template>

<!--  the following templates will format the view page content -->
<xsl:template match="extRif:displayTitle">   

<!-- <div class="right_icon">
   <img class="icon-heading">
     <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
     <xsl:text>assets/core/images/icons/</xsl:text>
     <xsl:value-of select="$objectClassType"/>
     <xsl:text>.png</xsl:text></xsl:attribute>
     <xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
 </img>
</div>   -->



    <div class="page_title"  id="displaytitle">
      
       <h1><xsl:value-of select="."/><xsl:apply-templates select="../extRif:logo"/></h1>
       <xsl:for-each select="//ro:existenceDates">
          <xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if>
          - 
          <xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if><br/>
      </xsl:for-each>     
  </div>			


</xsl:template>
    
<xsl:template match="extRif:logo">
    <xsl:if test="text() != ''">
      <img id="party_logo" class="logo right" style="max-width:130px;height:auto;max-height:none">
      	<xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
      	<xsl:attribute name="alt">Contributor Logo</xsl:attribute>
      </img>
    </xsl:if>  
</xsl:template> 

<xsl:template match="ro:name[@type='alternative']/ro:displayTitle">   
    <p class="alt_displayTitle"><xsl:value-of select="."/></p>
</xsl:template> 
  
    	    	 	 	



    <xsl:template match="ro:title">
        <xsl:value-of select="."/>    
    </xsl:template>

 
  <xsl:template match="ro:identifier" mode="ark">
ARK: 
      			    <xsl:variable name="theidentifier">    			
    				<xsl:choose>	
    			    	<xsl:when test="string-length(substring-after(.,'http://'))>0">
    			     		<xsl:value-of select="(substring-after(.,'http://'))"/>
    			     	</xsl:when>	    							
	     	
    			     	<xsl:otherwise>
    			     		<xsl:value-of select="."/>
    			     	</xsl:otherwise>		
    				</xsl:choose>
 					</xsl:variable>  
 					<xsl:if test="string-length(substring-after(.,'/ark:/'))>0">    			     
    				<a>
    				<xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a>
    				</xsl:if>
    				<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
    					<xsl:value-of select="."/>
    				</xsl:if>
    				 <br />		 
</xsl:template>
 <xsl:template match="ro:identifier" mode="nla">
 NLA: 
       			    <xsl:variable name="theidentifier">    			
    				<xsl:choose>				
    			    	<xsl:when test="string-length(substring-after(.,'nla.gov.au/'))>0">
    			     		<xsl:value-of select="substring-after(.,'nla.gov.au/')"/>
    			     	</xsl:when>		     	
    			     	<xsl:otherwise>
    			     		<xsl:value-of select="."/>
    			     	</xsl:otherwise>		
    				</xsl:choose>
 					</xsl:variable>  
 					<xsl:if test="string-length(substring-after(.,'nla.party'))>0">		
    				<a>
    				<xsl:attribute name="href"><xsl:text>http://nla.gov.au/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 	<br />
  				</xsl:if> 
  					<xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">		
   				
    				<xsl:value-of select="."/>
    			<br />
  				</xsl:if> 
 </xsl:template>
 <xsl:template match="ro:identifier" mode="doi">   					
DOI: 
        			    <xsl:variable name="theidentifier">    			
    				<xsl:choose>				
    			    	<xsl:when test="string-length(substring-after(.,'doi.org/'))>0">
    			     		<xsl:value-of select="substring-after(.,'doi.org/')"/>
    			     	</xsl:when>		     	
    			     	<xsl:otherwise>
    			     		<xsl:value-of select="."/>
    			     	</xsl:otherwise>		
    				</xsl:choose>
 					</xsl:variable>   	  
  					<xsl:if test="string-length(substring-after(.,'10.'))>0">		
      				<a>
    				<xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 		 <br />
  				</xsl:if> 
  					<xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
   				
    				<xsl:value-of select="."/>
    			<br />
  				</xsl:if> 					 			

    			
 </xsl:template>
 <xsl:template match="ro:identifier" mode="handle">      			
Handle: 
      			    <xsl:variable name="theidentifier">    			
    				<xsl:choose>
     			    	<xsl:when test="string-length(substring-after(.,'hdl:'))>0">
    			     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl:')"/>
    			     	</xsl:when> 
      			    	<xsl:when test="string-length(substring-after(.,'hdl.handle.net/'))>0">
    			     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl.handle.net/')"/>
    			     	</xsl:when>   			     	     				
    			    	<xsl:when test="string-length(substring-after(.,'http:'))>0">
    			     		<xsl:text></xsl:text><xsl:value-of select="."/>
    			     	</xsl:when>    										     	
    			     	<xsl:otherwise>
    			     		<xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="."/>
    			     	</xsl:otherwise>		
    				</xsl:choose>
 					</xsl:variable>
    			     
    				<a>
    				<xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    				<xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
    				<xsl:value-of select="."/>
    				</a> 	 
    			<br />
 </xsl:template>
 <xsl:template match="ro:identifier" mode="purl">     			
 	PURL: 
    <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
    		<xsl:value-of select="substring-after(.,'purl.org/')"/>
    	</xsl:when>		     	
    	<xsl:otherwise>
    		<xsl:value-of select="."/>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	   			
    <a>
    <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
    </a>  
    	<br /> 
  </xsl:template>
  <xsl:template match="ro:identifier" mode="uri">     			
 	URI: 
   <xsl:variable name="theidentifier">    			
    <xsl:choose>				
    	<xsl:when test="string-length(substring-after(.,'http'))>0">
    		<xsl:value-of select="."/>
    	</xsl:when>		     	
    	<xsl:otherwise>
    		http://<xsl:value-of select="."/>
    	</xsl:otherwise>		
    </xsl:choose>
 	</xsl:variable>   	        			
    <a>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>  
    </a>   		 
   	<br />
  </xsl:template> 
 <xsl:template match="ro:identifier" mode="other">     			 			 	    			 			
   <!--  <xsl:attribute name="name"><xsl:value-of select="./@type"/></xsl:attribute>  -->
   <xsl:choose>
   <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
 		<xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
   </xsl:when>
    <xsl:when test="./@type='local'">
 		Local: <xsl:value-of select="."/>    
   </xsl:when>  
   <xsl:otherwise>
	<xsl:value-of select="./@type"/>: <xsl:value-of select="."/>
	</xsl:otherwise>
	</xsl:choose>
	<br />
  </xsl:template>  
  
  <!--   <xsl:template match="ro:citationInfo/ro:fullCitation">
        <p><xsl:value-of select="."/></p>
        <span class="Z3988">    
        <xsl:attribute name="title">
        <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
        <xsl:text>&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
        <xsl:text>&amp;rfr_id=info%3Asid%2FANDS</xsl:text>
        <xsl:text>&amp;rft.title=</xsl:text><xsl:value-of select="//ro:displayTitle"/>
        <xsl:text>&amp;rft.description=</xsl:text><xsl:value-of select="."/>
        </xsl:attribute>
        </span>
                <span class="Z3988">
        </span>     
    </xsl:template>
                
    <xsl:template match="ro:citationInfo/ro:citationMetadata">
     <p>
        <xsl:if test="./ro:contributor">
            <xsl:apply-templates select="ro:contributor"/>
        </xsl:if>
        <xsl:if test="./ro:date">
        (
            <xsl:apply-templates select="//ro:citationMetadata/ro:date"/>               
        )           
        </xsl:if>   
        <xsl:if test="./ro:title != ''">
            <xsl:text> </xsl:text>
            <xsl:value-of select="./ro:title"/>.
        </xsl:if>
        <xsl:if test="./ro:edition != ''">
            <xsl:text> </xsl:text>
            <xsl:value-of select="./ro:edition"/>.
        </xsl:if>   
        <xsl:if test="./ro:placePublished != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:placePublished"/>.
        </xsl:if>
        <xsl:if test="./ro:publisher != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:publisher"/>.
        </xsl:if>        
        <xsl:if test="./ro:url != ''">
            <xsl:text> </xsl:text>      
            <xsl:value-of select="./ro:url"/>
        </xsl:if>
        <xsl:if test="./ro:context != ''">
            <xsl:text> </xsl:text>      
            , <xsl:value-of select="./ro:context"/>
        </xsl:if>
        <xsl:if test="./ro:identifier != ''">,         
        	<xsl:apply-templates select="./ro:identifier[@type = 'doi']"  mode="doi"/>	
         	<xsl:apply-templates select="./ro:identifier[@type = 'uri']"  mode="uri"/>	 
         	<xsl:apply-templates select="./ro:identifier[@type = 'URL']"  mode="uri"/>	
           	<xsl:apply-templates select="./ro:identifier[@type = 'url']"  mode="uri"/>	  
            <xsl:apply-templates select="./ro:identifier[@type = 'purl']"  mode="purl"/>	  
            <xsl:apply-templates select="./ro:identifier[@type = 'handle']"  mode="handle"/>	
            <xsl:apply-templates select="./ro:identifier[@type = 'AU-ANL:PEAU']"  mode="nla"/>
            <xsl:apply-templates select="./ro:identifier[@type = 'ark']"  mode="ark"/>  
            <xsl:apply-templates select="./ro:identifier[@type != 'doi' and @type != 'uri' and @type != 'URL' and @type != 'url' and @type != 'purl' and @type != 'handle' and @type != 'AU-ANL:PEAU' and @type != 'ark']"  mode="other"/>				
        </xsl:if>
     	</p>
     	<span class="Z3988">   
        	<xsl:attribute name="title">
        	<xsl:text>ctx_ver=Z39.88-2004</xsl:text>
        	<xsl:text>&amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
        	<xsl:text>&amp;rfr_id=info%3Asid%2FANDS</xsl:text>
        	<xsl:text>&amp;rft.contributor=</xsl:text><xsl:apply-templates select="ro:contributor"/>
        	<xsl:text>&amp;rft.title=</xsl:text><xsl:value-of select="./ro:title"/> 
        	<xsl:text>&amp;rft.place=</xsl:text><xsl:value-of select="./ro:placePublished"/>
        	<xsl:text>&amp;rft_id=</xsl:text><xsl:value-of select="./ro:url"/>
        	<xsl:text>&amp;rft.edition=</xsl:text><xsl:value-of select="./ro:edition"/>.
        	<xsl:text>&amp;rft.description=</xsl:text><xsl:value-of select="./ro:context"/>
        	</xsl:attribute>
    	</span>
    	<span class="Z3988">
    	</span>                                                     
    </xsl:template> 
    
    <xsl:template match="ro:contributor">       
        <xsl:if test="./ro:namePart/@type='family'">
            <xsl:value-of select="./ro:namePart[@type='family']"/>,
        </xsl:if>
        <xsl:if test="./ro:namePart/@type='given'">
            <xsl:value-of select="./ro:namePart[@type='given']"/>.
        </xsl:if>
                <xsl:if test="./ro:namePart/@type='initial' and not(./ro:namePart/@type='given')">
            <xsl:value-of select="./ro:namePart[@type='initial']"/>.
        </xsl:if>   
    </xsl:template> 

    <xsl:template match="//ro:citationInfo/ro:citationMetadata/ro:date">
        <xsl:if test="position()>1">
            <xsl:text>,</xsl:text>
        </xsl:if>       
        <xsl:value-of select="."/> 
    </xsl:template> --> 
    
	<xsl:template match="ro:location/ro:address/ro:electronic">
		<xsl:if test="./@type='url'">
		<xsl:variable name="url">
		
		<xsl:choose>
		<xsl:when test="string-length(.)>30">
		<xsl:value-of select="substring(.,0,30)"/>...
		</xsl:when>
		<xsl:otherwise>
		<xsl:value-of select="."/>
		</xsl:otherwise>
		</xsl:choose>
		</xsl:variable>	
			<a><xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute><xsl:attribute name="target">_blank</xsl:attribute><xsl:value-of select="$url"/></a><br />
		</xsl:if>		
	</xsl:template>
	
	<xsl:template match="ro:location/ro:address/ro:physical">
		<p>
			<xsl:choose>
				<xsl:when test = "./ro:addressPart or ./ro:addressPart!=''">
				
						<xsl:apply-templates select="./ro:addressPart[@type='fullname']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='organizationname']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='buildingorpropertyname']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='flatorunitnumber']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='floororlevelnumber']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='lotnumber']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='housenumber']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='streetname']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumberprefix']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbervalue']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='postaldeliverynumbersuffix']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='addressline']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='suburborplaceorlocality']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='stateorterritory']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='postcode']"/>	
						<xsl:apply-templates select="./ro:addressPart[@type='country']"/>		
						<xsl:apply-templates select="./ro:addressPart[@type='locationdescriptor']"/>
						<xsl:apply-templates select="./ro:addressPart[@type='deliverypointidentifier']"/>	
												
						<xsl:apply-templates select="./ro:addressPart[not(@type='organizationname' or @type='fullname' or @type='buildingorpropertyname' or @type='flatorunitnumber' or @type='floororlevelnumber' or @type='lotnumber' or @type='housenumber' or @type='streetname' or @type='postaldeliverynumberprefix' or @type='postaldeliverynumbervalue' or @type='postaldeliverynumbersuffix' or @type='addressline' or @type='suburborplaceorlocality' or @type='stateorterritory' or @type='country' or @type='locationdescriptor' or @type='deliverypointidentifier' or @type='postcode' or @type='telephoneNumber' or @type='faxNumber' )]"/>	
						<!--xsl:apply-templates select="./ro:addressPart[not(@type='addressLine') or @type!='deliveryPointIdentifier' or @type='locationDescriptor' or @type='country' or @type='stateOrTerritory' or @type='suburbOrPlaceOrLocality' or @type='suburbOrPlaceOrLocality' or @type='addressLine' or @type='postalDeliveryNumberSuffix])"/-->
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="." disable-output-escaping="yes"/><br />			
				</xsl:otherwise>
			</xsl:choose>	
		</p>
	</xsl:template>	

	<xsl:template match="ro:addressPart">			
			<xsl:value-of select="." disable-output-escaping="yes"/><br />
	</xsl:template>
		
<!--  	<xsl:template match="extRif:rights">
			
			<xsl:if test="./@type='rights'"><h4>Rights statement</h4></xsl:if>
			<xsl:if test="./@type='accessRights'"><h4>Access rights</h4></xsl:if>
			<xsl:if test="./@type='licence'"><h4>Licence</h4></xsl:if>				
			<p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
			<xsl:if test="./@rightsUri"><br />
			<a target="_blank">
			<xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a>
			</xsl:if>	
			</p>		
	</xsl:template>-->
	<xsl:template match="extRif:description" mode="content">     
        <div><xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>

           <p><xsl:value-of select="." disable-output-escaping="yes"/></p>
        </div>
	</xsl:template> 
	
	
	
	<xsl:template match="ro:location/ro:address/ro:electronic/@type">		
		<xsl:if test=".='email'">	
	  		<xsl:value-of select=".."/><br />
		</xsl:if>				
	</xsl:template>  
	      
</xsl:stylesheet>
