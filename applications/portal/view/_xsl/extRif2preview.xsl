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
    <xsl:variable name="object_identifier" >
      <xsl:choose>
             <xsl:when test="//extRif:extendedMetadata/extRif:status = 'PUBLISHED'"> <xsl:value-of select="//extRif:extendedMetadata/extRif:slug"/> </xsl:when>
             <xsl:when test="//extRif:extendedMetadata/extRif:status != 'PUBLISHED'"> <xsl:value-of select="//extRif:extendedMetadata/extRif:id"/> </xsl:when>
      </xsl:choose>
 </xsl:variable>
 
 <xsl:template match="ro:registryObject">

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

    <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>

  </xsl:template>

  <xsl:template match="ro:collection | ro:activity | ro:party | ro:service">

       <div class="previewItemHeader ">
                <xsl:attribute name="class">
               <xsl:text>previewItemHeader</xsl:text><xsl:text> </xsl:text> <xsl:text>previewItemHeader</xsl:text><xsl:value-of select="$object_identifier"/>
           </xsl:attribute>
        PREVIEW
        </div>

            <div itemscope="" itemType="http://schema.org/Thing">

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
          <a href="" class="viewRecordLink"><h3 itemprop="name"><xsl:value-of select="../ro:key"/></h3></a>
        </div>
      </xsl:otherwise> 
</xsl:choose>    

<!-- DISPLAY LOGO -->
<xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayLogo"/>

<!-- DISPLAY ALTERNATE TITLES/NAMES -->
<xsl:apply-templates select="ro:name[@type='alternative']/ro:displayTitle"/>

<div class="post">

    <!-- DISPLAY DESCRIPTIONS -->
    <xsl:if test="../extRif:extendedMetadata/extRif:description">

        <div class="descriptions" style="position:relative;clear:both;">
          <small>
            
            <xsl:choose>
              <xsl:when test="../extRif:extendedMetadata/extRif:description[@type= 'brief']">
                  <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'brief']" mode="content"/>
              </xsl:when>
              <xsl:otherwise>
                <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'full']" mode="content"/>
              </xsl:otherwise>
            </xsl:choose>

          </small>
        </div>
        
    </xsl:if>
    <a href="javascript:void(0);" class="showall_descriptions hide">More...</a>
    
    </div>

    <a href="">
      <xsl:attribute name="class">
        <xsl:text>viewRecord viewRecordLink viewRecordLink</xsl:text><xsl:value-of select="$object_identifier"/>
      </xsl:attribute>View Full Record</a> 
</div>


</xsl:template>

<!--  the following templates will format the view page content -->
<xsl:template match="extRif:displayTitle">   

  <div class="right_icon">
     <a class="viewRecord" href="">
      <xsl:attribute name="title"><xsl:value-of select="$objectClassType"/></xsl:attribute>

      <img class="icon-heading">
       <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
       <xsl:text>assets/core/images/icons/</xsl:text>
       <xsl:value-of select="$objectClassType"/>
       <xsl:text>.png</xsl:text></xsl:attribute>
       <xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
       <xsl:attribute name="style"><xsl:text>width:50%; float:right;</xsl:text></xsl:attribute>
     </img>
   </a>
  </div>   



    <div class="page_title" id="displaytitle">
       <h4><a href="" class="viewRecord"><xsl:value-of select="."/></a></h4>
    </div>			


</xsl:template>

<xsl:template match="extRif:displayLogo">
    <xsl:if test="extRif:displayLogo/text() != ''">
        <div>
            <img id="party_logo" style="max-width:130px;max-height:none;height:auto">
            	<xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
            	<xsl:attribute name="alt">Party Logo</xsl:attribute>
            </img>
        </div>
    </xsl:if>  
</xsl:template> 

<xsl:template match="ro:name[@type='alternative']/ro:displayTitle">   
    <p class="alt_displayTitle"><xsl:value-of select="."/></p>
</xsl:template> 

<xsl:template match="ro:title">
    <xsl:value-of select="."/>    
</xsl:template>

<xsl:template match="ro:relatedInfo/ro:notes">
    <xsl:value-of select="."/>   
</xsl:template> 


<xsl:template match="ro:coverage/extRif:spatial/extRif:coords">
  <xsl:if test="not(./@type) or (./@type!= 'text' and ./@type!= 'dcmiPoint')">
    <p class="coverage" name="{@type}"><xsl:value-of select="."/></p>
</xsl:if>
</xsl:template>
<xsl:template match="ro:location/extRif:spatial/extRif:coords">
  <xsl:if test="not(./@type) or (./@type!= 'text' and ./@type!= 'dcmiPoint')">
    <p class="coverage" name="{@type}"><xsl:value-of select="."/></p>
</xsl:if>
</xsl:template>   
<xsl:template match="extRif:center">
    <p class="spatial_coverage_center"><xsl:value-of select="."/></p>
</xsl:template>

<xsl:template match="ro:date">  
    <xsl:if test="./@type = 'dateFrom'">
        From 
    </xsl:if>
    <xsl:if test="./@type = 'dateTo'">
        to  
    </xsl:if>     
    <xsl:value-of select="."/>       
</xsl:template> 
<xsl:template match="ro:location[@datefrom!=''] | ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">  
   <xsl:if test="./@datefrom != ''">
    From         <xsl:value-of select="./@datefrom"/>     
</xsl:if>     
<xsl:if test="./@dateFrom != ''">
    From         <xsl:value-of select="./@dateFrom"/>     
</xsl:if>
<xsl:if test="./@dateTo != ''">
    to         <xsl:value-of select="./@dateTo"/><br />   
</xsl:if>       

</xsl:template>   
<xsl:template match="ro:coverage/ro:temporal/ro:text">
   <xsl:value-of select="."/>   <br />
</xsl:template>
<xsl:template match="ro:coverage/ro:temporal/ro:date">
 <xsl:if test="./@type = 'datefrom'">
    From         <xsl:value-of select="."/>     
</xsl:if>
<xsl:if test="./@type = 'dateFrom'">
    From         <xsl:value-of select="."/>     
</xsl:if>
<xsl:if test="./@type = 'dateTo'">
    to         <xsl:value-of select="."/><br/>
</xsl:if> 
</xsl:template>

<xsl:template match="ro:subject">   
    <a href="javascript:void(0);" class="subjectFilter" id="{@extRif:resolvedValue}" title="{.}">
        <xsl:choose>
            <xsl:when test="@extRif:resolvedValue != ''">
                <xsl:value-of select="@extRif:resolvedValue"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="."/>
            </xsl:otherwise>
        </xsl:choose>
    </a>      
</xsl:template>

<xsl:template match="ro:relatedInfo">
    <p>

      <xsl:if test="./ro:title">
          <xsl:value-of select="./ro:title"/><br/>
      </xsl:if>
      <xsl:apply-templates select="./ro:identifier[@type='doi']" mode = "doi"/>
      <xsl:apply-templates select="./ro:identifier[@type='ark']" mode = "ark"/>    	
      <xsl:apply-templates select="./ro:identifier[@type='AU-ANL:PEAU']" mode = "nla"/>  
      <xsl:apply-templates select="./ro:identifier[@type='handle']" mode = "handle"/>   
      <xsl:apply-templates select="./ro:identifier[@type='purl']" mode = "purl"/>
      <xsl:apply-templates select="./ro:identifier[@type='uri']" mode = "uri"/> 
      <xsl:apply-templates select="./ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>			            	

      <xsl:if test="./ro:format">
       <xsl:apply-templates select="./ro:format"/>
   </xsl:if>
   <xsl:if test="./ro:notes">
       <xsl:apply-templates select="./ro:notes"/>
   </xsl:if>
</p>        
</xsl:template>
<xsl:template match="ro:format">
    <xsl:apply-templates select="./ro:identifier[@type='Doi']" mode = "formatdoi"/>
    <xsl:apply-templates select="./ro:identifier[@type='Ark']" mode = "formatark"/>    	
    <xsl:apply-templates select="./ro:identifier[@type='AU-ANL:PEAU']" mode = "formatnla"/>  
    <xsl:apply-templates select="./ro:identifier[@type='Handle']" mode = "formathandle"/>   
    <xsl:apply-templates select="./ro:identifier[@type='Purl']" mode = "formatpurl"/>
    <xsl:apply-templates select="./ro:identifier[@type='Uri']" mode = "formaturi"/> 
    <xsl:apply-templates select="./ro:identifier[not(@type =  'Doi' or @type =  'Ark' or @type =  'AU-ANL:PEAU' or @type =  'Handle' or @type =  'Purl' or @type =  'Uri')]" mode="formatother"/>			            	                          	
</xsl:template>




<xsl:template match="ro:identifier" mode="ark">
    <p>
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
        <xsl:attribute name="class">identifier</xsl:attribute>
        <xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
        <xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
        <xsl:value-of select="."/>
    </a>
</xsl:if>
<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
  <a class="identifier"><xsl:value-of select="."/></a>
</xsl:if>
</p>	 

</xsl:template>
<xsl:template match="ro:identifier" mode="formatark">
    <p>
        Format ARK: 
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
        <xsl:attribute name="class">identifier</xsl:attribute>
        <xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
        <xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
        <xsl:value-of select="."/>
    </a>
</xsl:if>
<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
  <a class="identifier"><xsl:value-of select="."/></a>
</xsl:if>
</p>	 

</xsl:template>
<xsl:template match="ro:identifier" mode="nla">
    <p>
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
        <xsl:attribute name="class">identifier</xsl:attribute>
        <xsl:attribute name="href"><xsl:text>http://nla.gov.au/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
        <xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>    				
        <xsl:value-of select="."/>
    </a>
</xsl:if> 
<xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">		
  <a class="identifier"><xsl:value-of select="."/></a>
</xsl:if> 
</p>

</xsl:template>
<xsl:template match="ro:identifier" mode="doi">   		
    <p>			
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
            <xsl:attribute name="class">identifier</xsl:attribute>
            <xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
            <xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
            <xsl:value-of select="."/>
        </a>
    </xsl:if>

    <xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
       <a class="identifier"><xsl:value-of select="."/></a>
   </xsl:if> 	
</p>			 			


</xsl:template>

<xsl:template match="ro:identifier" mode="formatdoi">   		
    <p>			
        Format DOI: 
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
            <xsl:attribute name="class">identifier</xsl:attribute>
            <xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
            <xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
            <xsl:value-of select="."/>
        </a>
    </xsl:if>

    <xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
       <a class="identifier"><xsl:value-of select="."/></a>
   </xsl:if> 	
</p>			 			


</xsl:template>

<xsl:template match="ro:identifier" mode="handle">      
   <p>			
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
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
</a> 
</p>
</xsl:template>
<xsl:template match="extRif:identifier" mode="formathandle">      
   <p>			
      Format Handle: 
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
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
</a> 
</p>
</xsl:template>
<xsl:template match="ro:identifier" mode="purl">     
    <p>			
      PURL: 
      <xsl:variable name="theidentifier">    			
        <xsl:choose>				
           <xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
              <a class="identifier"><xsl:value-of select="substring-after(.,'purl.org/')"/></a>
          </xsl:when>		     	
          <xsl:otherwise>
              <a class="identifier"><xsl:value-of select="."/></a>
          </xsl:otherwise>		
      </xsl:choose>
  </xsl:variable>   	   			
  <a>
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
</a> 
</p>
</xsl:template>
<xsl:template match="extRif:identifier" mode="formatpurl">     
    <p>			
      Format PURL: 
      <xsl:variable name="theidentifier">    			
        <xsl:choose>				
           <xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
              <a class="identifier"><xsl:value-of select="substring-after(.,'purl.org/')"/></a>
          </xsl:when>		     	
          <xsl:otherwise>
              <a class="identifier"><xsl:value-of select="."/></a>
          </xsl:otherwise>		
      </xsl:choose>
  </xsl:variable>   	   			
  <a>
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
</a> 
</p>
</xsl:template>
<xsl:template match="ro:identifier" mode="uri">    
  <p> 			
      URI: 
      <xsl:variable name="theidentifier">    			
        <xsl:choose>				
           <xsl:when test="string-length(substring-after(.,'http'))>0">
              <a class="identifier"><xsl:value-of select="."/></a>
          </xsl:when>		     	
          <xsl:otherwise>
            <a>
                <xsl:attribute name="class">identifier</xsl:attribute>
                <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
                <xsl:value-of select="."/>
            </a>
        </xsl:otherwise>		
    </xsl:choose>
</xsl:variable>   	        			
<a>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>  
</a>   		 
</p>
</xsl:template> 
<xsl:template match="ro:identifier" mode="formaturi">    
  <p> 			
   Format	URI: 
   <xsl:variable name="theidentifier">    			
    <xsl:choose>				
     <xsl:when test="string-length(substring-after(.,'http'))>0">
      <a class="identifier"><xsl:value-of select="."/></a>
  </xsl:when>		     	
  <xsl:otherwise>
    <a>
        <xsl:attribute name="class">identifier</xsl:attribute>
        <xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
        <xsl:value-of select="."/>
    </a>
</xsl:otherwise>		
</xsl:choose>
</xsl:variable>   	        			
<a>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>  
</a>   		 
</p>
</xsl:template> 
<xsl:template match="ro:identifier" mode="other">   
   <p>  			 			 	    			 			
     <!--  <xsl:attribute name="name"><xsl:value-of select="./@type"/></xsl:attribute>  -->
     <xsl:choose>
         <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
             <xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
         </xsl:when>
         <xsl:when test="./@type='local'">
             Local: <a class="identifier"><xsl:value-of select="."/></a>
         </xsl:when>  
         <xsl:otherwise>
           <xsl:value-of select="./@type"/>: <a class="identifier"><xsl:value-of select="."/></a>
       </xsl:otherwise>
   </xsl:choose>
</p>
</xsl:template>  
<xsl:template match="ro:identifier" mode="formatother">   
   <p>  Format 			 			 	    			 			
     <!--  <xsl:attribute name="name"><xsl:value-of select="./@type"/></xsl:attribute>  -->
     <xsl:choose>
         <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
             <xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
         </xsl:when>
         <xsl:when test="./@type='local'">
             Local: <a class="identifier"><xsl:value-of select="."/></a>
         </xsl:when>  
         <xsl:otherwise>

           <xsl:value-of select="./@type"/>: <a class="identifier"><xsl:value-of select="."/></a>
       </xsl:otherwise>
   </xsl:choose>
</p>
</xsl:template>   

<xsl:template match="ro:citationInfo/ro:fullCitation">
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
    <xsl:if test="./ro:version != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="./ro:version"/>.
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
       <xsl:text>&amp;rft.edition=</xsl:text><xsl:value-of select="./ro:version"/>.
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
    <xsl:if test="./ro:namePart/@type='full'">
        <xsl:value-of select="./ro:namePart[@type='full']"/>.
    </xsl:if>
    <xsl:if test="./ro:namePart/@type=''">
        <xsl:value-of select="./ro:namePart[@type='']"/>.
    </xsl:if>         
    <xsl:if test="./ro:namePart[not (@type)]">
        <xsl:value-of select="./ro:namePart"/>.
    </xsl:if>                
</xsl:template> 

<xsl:template match="//ro:citationInfo/ro:citationMetadata/ro:date">
    <xsl:if test="position()>1">
        <xsl:text>,</xsl:text>
    </xsl:if>       
    <xsl:value-of select="."/> 
</xsl:template> 

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
    <a>
        <xsl:attribute name="href">
            <xsl:value-of select="."/>
        </xsl:attribute>
        <xsl:attribute name="class">recordOutBound</xsl:attribute>
        <xsl:attribute name="type">electronic_address</xsl:attribute>
        <xsl:attribute name="target">_blank</xsl:attribute><xsl:value-of select="$url"/>
    </a><br />
</xsl:if>
</xsl:template>

<xsl:template match="ro:location/ro:address/ro:physical">
  <p>
     <xsl:choose>
        <xsl:when test = "./ro:addressPart">
          <xsl:apply-templates select="./ro:addressPart[@type='fullName']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='organizationName']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='buildingOrPropertyName']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='flatOrUnitNumber']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='floorOrLevelNumber']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='lotNumber']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='houseNumber']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='streetName']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberPrefix']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberValue']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberSuffix']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='addressLine']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='suburbOrPlaceOrLocality']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='stateOrTerritory']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='postCode']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='country']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='locationDescriptor']"/>
          <xsl:apply-templates select="./ro:addressPart[@type='deliveryPointIdentifier']"/>					
          <xsl:apply-templates select="./ro:addressPart[not(@type='organizationName' or @type='fullName' or @type='buildingOrPropertyName' or @type='flatOrUnitNumber' or @type='floorOrLevelNumber' or @type='lotNumber' or @type='houseNumber' or @type='streetName' or @type='postalDeliveryNumberPrefix' or @type='postalDeliveryNumberValue' or @type='postalDeliveryNumberSuffix' or @type='addressLine' or @type='suburbOrPlaceOrLocality' or @type='stateOrTerritory' or @type='country' or @type='locationDescriptor' or @type='deliveryPointIdentifier' or @type='postCode' or @type='telephoneNumber' or @type='faxNumber')]"/>	

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

<xsl:template match="ro:rights[@type!='licence'] | ro:description[@type='rights'] | ro:description[@type='accessRights']">

 <xsl:if test="./@type='rights'"><h4>Rights statement</h4></xsl:if>
 <xsl:if test="./@type='accessRights'"><h4>Access rights</h4></xsl:if>
 <!-- ><xsl:if test="./@type='licence'"><h4>Licence</h4></xsl:if>	-->			
 <p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
 <xsl:if test="./@rightsUri"><p>
    <a target="_blank">
        <xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
    </xsl:if>
</p>	

</xsl:template>	
<xsl:template match="ro:rights[@type='licence']">
  <p class="rights">
     <xsl:if test="string-length(substring-after(./@licence_type,'CC-'))>0">
        <img id="licence_logo" style="width:130px;max-height:none;height:auto">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
            <xsl:text>/img/</xsl:text>
            <xsl:value-of select="./@licence_type"/>
            <xsl:text>.png</xsl:text></xsl:attribute>
            <xsl:attribute name="alt"><xsl:value-of select="./@licence_type"/></xsl:attribute>
        </img>
    </xsl:if>
    <xsl:if test="string-length(substring-after(./@licence_type,'CC-'))=0">	   
     <xsl:if test="./@licence_type='Unknown/Other' and .=''"><p>Unknown</p></xsl:if>
     <xsl:if test="./@licence_type!='Unknown/Other'"><p><xsl:value-of select="./@licence_type"/></p></xsl:if>
     <!--  <xsl:value-of select="./@licence_type"/> -->
 </xsl:if>
 <xsl:if test="."><p><xsl:value-of select="."/></p></xsl:if>
 <xsl:if test="./@rightsUri"><p>
    <a target="_blank">
        <xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
    </xsl:if>						
</p>		
</xsl:template>

<xsl:template match="ro:description" mode="content">     
    <div>
       <xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>
       <h4><xsl:value-of select="@type"/></h4>
       <p>
         <xsl:if test="@type='deliverymethod'">
             Delivery Method : 
         </xsl:if>
         <xsl:value-of select="." disable-output-escaping="yes"/></p>
     </div>
 </xsl:template> 



 <xsl:template match="ro:location/ro:address/ro:electronic/@type">		
  <xsl:if test=".='email'">	
   <xsl:value-of select=".."/><br />
</xsl:if>				
</xsl:template> 

<xsl:template match="extRif:dates">
  <tr><td><xsl:value-of select="./@etype"/>:  <td>    </td>  </td><td>

  <xsl:if test="./extRif:date/@type!='dateTo'"> 
      <xsl:value-of select="./extRif:date[@type!='dateTo']"/> 
  </xsl:if>      
  <xsl:if test="./extRif:date/@type='dateTo'"> to 
    <xsl:value-of select="./extRif:date[@type='dateTo']"/> 
</xsl:if>
</td></tr>
</xsl:template>

<xsl:template match="extRif:extendedMetadata" priority="-1" />


</xsl:stylesheet>