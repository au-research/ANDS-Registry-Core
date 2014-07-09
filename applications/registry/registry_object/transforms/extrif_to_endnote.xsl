<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output omit-xml-declaration="yes" indent="yes"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dateRequested"/>
    <xsl:param name="dateHarvested"/>
    <xsl:param name="portal_url"/>
    <xsl:template match="/">
    <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="ro:registryObject"/>

    <xsl:template match="ro:registryObject[ro:collection]">
        <xsl:variable name="sourceUrl">
            <xsl:call-template name="getSourceURL"/>
        </xsl:variable>
        <xsl:variable name="DOI">
            <xsl:call-template name="getDOI"/>
        </xsl:variable>

<xsl:text>Provider: Australian National Data Service
Database: Research Data Australia
Content:text/plain; charset="utf-8"


TY  - DATA
</xsl:text>
<xsl:text>Y2  - </xsl:text><xsl:value-of  select="$dateRequested"/><xsl:text>
</xsl:text>
<xsl:if test="$DOI != ''">
    <xsl:text>DO  - </xsl:text><xsl:value-of select="$DOI"/><xsl:text>
</xsl:text>
</xsl:if>


 <xsl:variable name="publishedDate">
        <xsl:call-template name="getPublishedDate"/>
</xsl:variable>
<xsl:if test="$publishedDate != ''">
    <xsl:text>PY  - </xsl:text><xsl:value-of select="substring($publishedDate,1,4)"/><xsl:text>
</xsl:text>
</xsl:if>


<xsl:choose>
    <!-- see if we have citationMetadatata -->
  <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor">
        <xsl:apply-templates select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor"/>
  </xsl:when>

     <!-- otherwise anonymus -->
  <xsl:otherwise>
        <xsl:text>%%%AU  - Anonymous
</xsl:text>
  </xsl:otherwise>
</xsl:choose>

<xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
        <xsl:if test="$sourceUrl != ''">
<xsl:text>UR  - </xsl:text><xsl:value-of select="$sourceUrl"/>
<xsl:text>
</xsl:text></xsl:if>
<xsl:if test="extRif:extendedMetadata/extRif:dataSourceTitle">
<xsl:text>PB  - </xsl:text>
                                <xsl:choose>
                                    <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher">
                                        <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="@group"/>
                                    </xsl:otherwise>
                                </xsl:choose>
<xsl:text>
</xsl:text>
</xsl:if>
<xsl:variable name="createdDate">
    <xsl:call-template name="getCreatedDate"/>
</xsl:variable>
<xsl:if test="$createdDate != ''">
    <xsl:text>DA  - </xsl:text><xsl:value-of select="$createdDate"/><xsl:text>
</xsl:text>
</xsl:if>

<xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version">
    <xsl:text>ET  - </xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version"/><xsl:text>
</xsl:text>
</xsl:if>

<xsl:text>LA  - English
</xsl:text>



 <xsl:choose>
     <xsl:when test="extRif:extendedMetadata/extRif:right">
          <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='rightsStatement'] | extRif:extendedMetadata/extRif:right[@type='accessRights'] | extRif:extendedMetadata/extRif:right[@type='rights']"/>
          <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='licence']"/>
      </xsl:when>
</xsl:choose>

<xsl:if test="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved">
      <xsl:apply-templates select="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved"/>
</xsl:if>

<xsl:if test="ro:collection/ro:coverage/ro:spatial">
      <xsl:apply-templates select="ro:collection/ro:coverage/ro:spatial" />
</xsl:if>


<xsl:if test="ro:collection/ro:description[@type='note']">
<xsl:for-each select="ro:collection/ro:description[@type='note']">
<xsl:text>N1  - </xsl:text><xsl:value-of select="normalize-space(.)" /><xsl:text>
</xsl:text>
</xsl:for-each>
</xsl:if>

<xsl:if test="ro:collection/ro:coverage/ro:temporal/ro:date">
     <xsl:apply-templates select="ro:collection/ro:coverage/ro:temporal/ro:date"/>
</xsl:if>
     <xsl:if test="ro:collection/ro:description">
         <xsl:text>AB  - </xsl:text>
             <xsl:choose>
                 <xsl:when test="ro:collection/ro:description[@type = 'full']">
                     <xsl:apply-templates select="ro:collection/ro:description[@type = 'full']"/>
                 </xsl:when>
                 <xsl:when test="ro:collection/ro:description[@type = 'brief']">
                     <xsl:apply-templates select="ro:collection/ro:description[@type = 'brief']"/>
                 </xsl:when>
             </xsl:choose>
             <xsl:apply-templates select="ro:collection/ro:description[@type = 'significanceStatement']"/>
             <xsl:apply-templates select="ro:collection/ro:description[@type = 'notes']"/>
             <xsl:apply-templates select="ro:collection/ro:description[@type = 'lineage']"/><xsl:text>
</xsl:text>


</xsl:if>

<xsl:if test="ro:collection/ro:relatedObject/ro:relation[@type = 'isOutputOf']">
     <xsl:apply-templates select="ro:collection/ro:relatedObject/ro:relation[@type = 'isOutputOf']" mode="fundingInfo"/><xsl:text>
</xsl:text>
</xsl:if>

<xsl:text>ER -
</xsl:text>

</xsl:template>

<xsl:template match="text()">
    <xsl:value-of select="normalize-space(.)"></xsl:value-of>
</xsl:template>

<xsl:template match="extRif:displayTitle">
   <xsl:text>TI  - </xsl:text><xsl:value-of select="."/><xsl:text>
</xsl:text>
</xsl:template>


<xsl:template match="extRif:right[@type='rightsStatement' and text()] | extRif:right[@type='accessRights' and text()] | extRif:right[@type='rights' and text()]">
        <xsl:text>C5  - </xsl:text><xsl:value-of select="normalize-space(.)"/><xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="extRif:right[@type='licence']">
        <xsl:text>C5  - </xsl:text><xsl:value-of select="normalize-space(.)"/><xsl:text>
</xsl:text>
</xsl:template>

 <xsl:template name="getDOI">
    <xsl:variable name="DOI">
            <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='doi']"/>
            </xsl:when>
            </xsl:choose>
     </xsl:variable>
     <xsl:choose>
          <xsl:when test="contains($DOI,'doi.org/')">
                <xsl:value-of select="substring-after($DOI,'doi.org/')"/>
          </xsl:when>
          <xsl:otherwise>
               <xsl:value-of select="$DOI"/>
          </xsl:otherwise>
      </xsl:choose>
</xsl:template>

    <xsl:template name="getSourceURL">
        <xsl:variable name="sourceUrl">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi'],'doi.org/'))>1">
                        <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi'],'doi.org/')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>

            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'],'hdl:'))>0">
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'],'hdl:')"/>
                    </xsl:when>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'],'hdl.handle.net/'))>0">
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'],'hdl.handle.net/')"/>
                    </xsl:when>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle'],'http:'))>0">
                        <xsl:text></xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri'],'http'))>0">
                        <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://</xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl'],'purl.org/'))>0">
                        <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl'],'purl.org/')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='doi']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='doi'],'doi.org/'))>1">
                        <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:identifier[@type='doi'],'doi.org/')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="ro:collection/ro:identifier[@type='doi']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='handle']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='handle'],'hdl:'))>0">
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:identifier[@type='handle'],'hdl:')"/>
                    </xsl:when>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='handle'],'hdl.handle.net/'))>0">
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:identifier[@type='handle'],'hdl.handle.net/')"/>
                    </xsl:when>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='handle'],'http:'))>0">
                        <xsl:text></xsl:text><xsl:value-of select="ro:collection/ro:identifier[@type='handle']"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="ro:collection/ro:identifier[@type='handle']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='uri']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='uri'],'http'))>0">
                        <xsl:value-of select="ro:collection/ro:identifier[@type='uri']"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://</xsl:text><xsl:value-of select="ro:collection/ro:identifier[@type='uri']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='purl']">
                <xsl:choose>
                    <xsl:when test="string-length(substring-after(ro:collection/ro:identifier[@type='purl'],'purl.org/'))>0">
                        <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="substring-after(ro:collection/ro:identifier[@type='purl'],'purl.org/')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="ro:collection/ro:identifier[@type='purl']"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:location/ro:address/ro:electronic[@type='url']">
                <xsl:value-of select="ro:collection/ro:location/ro:address/ro:electronic[@type='url']"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$portal_url"/>
            </xsl:otherwise>
        </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="$sourceUrl"/>
    </xsl:template>

<xsl:template match="extRif:subject_resolved">
    <xsl:if test="string(number(.)) = 'NaN'">
           <xsl:text>KW  - </xsl:text><xsl:value-of select="." disable-output-escaping="yes"/><xsl:text>
</xsl:text>
     </xsl:if>
</xsl:template>

<xsl:template match="ro:spatial">
        <xsl:text>RI  - </xsl:text><xsl:value-of select="."/><xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="ro:date">
    <xsl:text>C1  - </xsl:text>
        <xsl:choose>
            <xsl:when test="@type='dateFrom'">
                <xsl:text>From </xsl:text>
                    <xsl:value-of select="."/>
<xsl:text>
</xsl:text>
                        </xsl:when>
            <xsl:when test="@type='dateTo'">
               <xsl:text> To </xsl:text>
                    <xsl:value-of select="."/>    <xsl:text>
</xsl:text>
            </xsl:when>
            <xsl:otherwise>

                    <xsl:value-of select="."/>
    <xsl:text>
</xsl:text>
            </xsl:otherwise>
        </xsl:choose>

</xsl:template>


<xsl:template match="ro:contributor">
    <xsl:text>AU  - </xsl:text>
        <xsl:variable name="title">
            <xsl:apply-templates select="ro:namePart[@type = 'family']"/>
            <xsl:apply-templates select="ro:namePart[@type = 'given']"/>
            <xsl:apply-templates select="ro:namePart[@type = 'title']"/>
            <xsl:apply-templates select="ro:namePart[@type = '' or not(@type) or @type= 'superior']"/>
        </xsl:variable>
        <xsl:value-of select="substring($title,1,string-length($title)-2)"/>
    <xsl:text>
</xsl:text>
</xsl:template>

<xsl:template match="ro:collection/ro:relatedObject/ro:relation[@type = 'hasPrincipalInvestigator'] | ro:collection/ro:relatedObject/ro:relation[@type  = 'principalInvestigator'] | ro:collection/ro:relatedObject/ro:relation[@type = 'author'] | ro:collection/ro:relatedObject/ro:relation[@type = 'coInvestigator'] | ro:collection/ro:relatedObject/ro:relation[@type = 'isOwnedBy'] | ro:collection/ro:relatedObject/ro:relation[@type = 'hasCollector']"  mode="author">

            <xsl:text>%%%AU - </xsl:text><xsl:value-of select="../ro:key"/><xsl:text> - AU%%%
</xsl:text>

</xsl:template>

<xsl:template match="ro:collection/ro:relatedObject/ro:relation[@type='isOutputOf']" mode="fundingInfo">
    <xsl:text>%%%A4 - </xsl:text><xsl:value-of select="../ro:key"/><xsl:text> - A4%%%</xsl:text>
</xsl:template>

<xsl:template match="ro:namePart">
        <xsl:value-of select="."/><xsl:text>, </xsl:text>
</xsl:template>

<xsl:template name="getCreatedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>
        </xsl:choose>
</xsl:template>
<xsl:template name="getPublishedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.issued']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.issued']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.available']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.available']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/@dateModified">
                <xsl:value-of select="substring(ro:collection/@dateModified,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/@dateAccessioned">
                <xsl:value-of select="substring(ro:collection/@dateAccessioned,1,4)"/>
            </xsl:when>
            <xsl:when test="$dateHarvested">
                <xsl:value-of select="substring($dateHarvested,1,4)" />
            </xsl:when>
        </xsl:choose>
</xsl:template>
</xsl:stylesheet>