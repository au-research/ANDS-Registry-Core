<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  version="1.0">
<xsl:output indent="yes"/>
<xsl:strip-space elements="*"/>
  
    <xsl:template match='/'>
        <registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">
            <xsl:apply-templates select="//node()[local-name() = 'registryObject']"/>
        </registryObjects>
    </xsl:template>
 
    <xsl:template match="*">
    	<xsl:choose>
    		<xsl:when test="local-name() = 'registryObjects'">
              	<xsl:apply-templates select="node()"/>
    		</xsl:when>
            <xsl:when test="local-name() = 'registryObject'">
                <xsl:element name="{local-name()}" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
                    <xsl:apply-templates select="@group | node()"/>
                </xsl:element>
            </xsl:when>
            <xsl:when test="local-name() = 'annotations'">
                <xsl:copy-of select="."/>
            </xsl:when>
    		<xsl:otherwise>
            	<xsl:element name="{local-name()}" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
              		<xsl:apply-templates select="@*|node()"/>
            	</xsl:element>
    		</xsl:otherwise>
    	</xsl:choose>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:attribute name="{local-name()}">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@xml:lang">
        <xsl:attribute name="xml:lang">
          <xsl:value-of select="."/>
        </xsl:attribute>
    </xsl:template>

    <xsl:template match="@xsi:schemaLocation"/>

    
</xsl:stylesheet>
