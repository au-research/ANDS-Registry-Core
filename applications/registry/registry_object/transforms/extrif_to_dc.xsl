<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>   
    


    <xsl:template match="ro:registryObject">
        <dc xmlns="http://purl.org/dc/elements/1.1/">
	        <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
            <publisher xmlns="http://purl.org/dc/elements/1.1/">
                <xsl:value-of select="@group"/>
            </publisher>
            <source xmlns="http://purl.org/dc/elements/1.1/">
                <xsl:value-of select="ro:originatingSource"/>
            </source>
            <identifier xmlns="http://purl.org/dc/elements/1.1/">
                <xsl:value-of select="concat($base_url,extRif:extendedMetadata/extRif:slug)"/>
            </identifier>  
            <xsl:apply-templates select="extRif:extendedMetadata/extRif:related_object[extRif:related_object_class = 'party']"/>
            <xsl:apply-templates select="ro:collection | ro:party | ro:activity | ro:service"/>
            <xsl:apply-templates select="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved"/>
        </dc>
    </xsl:template> 

  
    <xsl:template match="extRif:displayTitle">
        <title xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="."/>
        </title>   
    </xsl:template>
   
    <xsl:template match="ro:collection | ro:party | ro:activity | ro:service">
        <type xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="@type"/>
        </type>
        <xsl:apply-templates select="ro:identifier"/>
        <xsl:apply-templates select="ro:relatedInfo"/>
        <xsl:apply-templates select="ro:description"/>   
        <xsl:apply-templates select="ro:coverage"/>  	
    </xsl:template>

    <xsl:template match="ro:relatedInfo">
        <xsl:apply-templates select="ro:identifier"/>
    </xsl:template>


    <xsl:template match="extRif:related_object">
        <contributor xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="concat(extRif:related_object_display_title,' (',extRif:related_object_relation,')') "/>
        </contributor>   
    </xsl:template>

    <xsl:template match="extRif:subject_resolved">
        <subject xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:value-of select="."/>
        </subject>   
    </xsl:template>

    <xsl:template match="ro:coverage">
        <xsl:apply-templates select="ro:spatial"/>
        <xsl:apply-templates select="ro:temporal"/>
    </xsl:template>

    <xsl:template match="ro:spatial">
        <coverage xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:text>Spatial: </xsl:text><xsl:value-of select="."/>
        </coverage>  
    </xsl:template>  

    <xsl:template match="ro:temporal">
        <coverage xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:text>Temporal: </xsl:text><xsl:value-of select="extRif:friendly_date"/>
        </coverage>        
    </xsl:template>

    <xsl:template match="ro:description">
        <xsl:choose>
            <xsl:when test="@type = 'rights' or @type = 'accessRights'">
                <rights xmlns="http://purl.org/dc/elements/1.1/">
                    <xsl:value-of select="."/>
                </rights> 
            </xsl:when>
            <xsl:otherwise>
                <description xmlns="http://purl.org/dc/elements/1.1/">
                    <xsl:value-of select="."/>
                </description> 
            </xsl:otherwise> 
        </xsl:choose> 
    </xsl:template>
    
    <xsl:template match="ro:identifier">
        <identifier xmlns="http://purl.org/dc/elements/1.1/">
            <xsl:choose>
                <xsl:when test="@type = 'handle'">
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
                </xsl:when>
                <xsl:when test="@type = 'doi'">
                    <xsl:choose>       
                        <xsl:when test="string-length(substring-after(.,'doi.org/'))>1">
                            <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="substring-after(.,'doi.org/')"/>
                        </xsl:when>          
                        <xsl:otherwise>
                            <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="."/>
                        </xsl:otherwise>   
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="@type = 'orcid'">
                    <xsl:choose>       
                        <xsl:when test="string-length(substring-after(.,'orcid.org/'))>1">
                            <xsl:text>http://orcid.org/</xsl:text><xsl:value-of select="substring-after(.,'orcid.org/')"/>
                        </xsl:when>          
                        <xsl:otherwise>
                            <xsl:text>http://orcid.org/</xsl:text><xsl:value-of select="."/>
                        </xsl:otherwise>   
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="@type = 'uri' or @type = 'url'">
                    <xsl:value-of select="."/>                        
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="concat('(',@type,') ',.)"/>                        
                </xsl:otherwise>
            </xsl:choose>
        </identifier>
    </xsl:template>
    
</xsl:stylesheet>

