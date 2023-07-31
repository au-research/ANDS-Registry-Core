<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
    <xsl:strip-space elements="*"/>
    <xsl:template match="/schema/fields">
        <xsl:apply-templates select="field" />
    </xsl:template>
    <xsl:variable name="noshow_list" select="'::s_list_title::alt_list_title::alt_display_title::'"/>
    <xsl:template match="//field">
        
        <xsl:if test="not(contains($noshow_list,concat('::',@name,'::')))">
            
        <xsl:value-of select="@name"/> <xsl:text> - </xsl:text><xsl:value-of
            select="preceding-sibling::comment()[1]" /> <xsl:text> 
            </xsl:text>
            
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
