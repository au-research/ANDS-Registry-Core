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
    <div class="ro_preview">
      <div class="ro_preview_header">
        <a href="{$base_url}{extRif:extendedMetadata/extRif:slug}">
          <img class="icon-heading">
             <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
             <xsl:text>assets/core/images/icons/</xsl:text>
             <xsl:value-of select="$objectClassType"/>
             <xsl:text>.png</xsl:text></xsl:attribute>
             <xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
            <!--  <xsl:attribute name="style"><xsl:text>width:50%; float:right;</xsl:text></xsl:attribute> -->
         </img>
        </a>
        <div class="title"><a href="{$base_url}{extRif:extendedMetadata/extRif:slug}"><xsl:value-of select="extRif:extendedMetadata/extRif:displayTitle"/></a></div>
       <div class="clear"></div>
      </div> 
      <!-- <div class="ro_preview_description">
        <xsl:value-of select="extRif:extendedMetadata/extRif:the_description" disable-output-escaping="yes"/>
      </div>  -->
    </div> 
  </xsl:template>

</xsl:stylesheet>