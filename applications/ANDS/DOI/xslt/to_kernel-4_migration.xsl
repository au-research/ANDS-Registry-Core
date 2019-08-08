<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:exsl="http://exslt.org/common"
                exclude-result-prefixes="exsl" version="1.0">
    <xsl:output indent="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="*">
        <xsl:choose>
            <xsl:when test="local-name() = 'resource'">
                <resource xmlns="http://datacite.org/schema/kernel-4"
                          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                          xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd">
                    <xsl:apply-templates select="*"/>
                </resource>
            </xsl:when>
            <xsl:when test="local-name() = 'geoLocationPoint'">
                <xsl:choose>
                    <xsl:when test="count(*) &lt; 2">
                        <xsl:call-template name="doNewPoint"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:element name="{local-name()}"
                                     xmlns="http://datacite.org/schema/kernel-4">
                            <xsl:apply-templates select="@*|node()"/>
                        </xsl:element>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="local-name() = 'geoLocationBox'">
                <xsl:choose>
                    <xsl:when test="count(*) &lt; 2">
                        <xsl:call-template name="doNewBox"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:element name="{local-name()}"
                                     xmlns="http://datacite.org/schema/kernel-4">
                            <xsl:apply-templates select="@*|node()"/>
                        </xsl:element>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:element name="{local-name()}"
                             xmlns="http://datacite.org/schema/kernel-4">
                    <xsl:apply-templates select="@*|node()"/>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="@xsi:schemaLocation"/>

    <xsl:template match="text()">
        <xsl:copy-of select="."/>
    </xsl:template>

    <xsl:template match="@*">
        <xsl:copy>
            <xsl:value-of select="."/>
        </xsl:copy>
    </xsl:template>

    <xsl:template name="doNewBox">
        <xsl:element name="geoLocationBox"
                     xmlns="http://datacite.org/schema/kernel-4">
            <xsl:variable name="stringList">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="pText"
                                    select="normalize-space(string(.))"/>
                </xsl:call-template>
            </xsl:variable>
            <xsl:element name="westBoundLongitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[2]"/>
            </xsl:element>
            <xsl:element name="eastBoundLongitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[4]"/>
            </xsl:element>
            <xsl:element name="southBoundLatitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[1]"/>
            </xsl:element>
            <xsl:element name="northBoundLatitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[3]"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template name="doNewPoint">
        <xsl:element name="geoLocationPoint"
                     xmlns="http://datacite.org/schema/kernel-4">
            <xsl:variable name="stringList">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="pText"
                                    select="normalize-space(string(.))"/>
                </xsl:call-template>
            </xsl:variable>
            <xsl:element name="pointLongitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[2]"/>
            </xsl:element>
            <xsl:element name="pointLatitude">
                <xsl:value-of select="exsl:node-set($stringList)/token[1]"/>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template name="tokenize">
        <xsl:param name="pText"/>
        <xsl:param name="delimiter" select="' '"/>
        <xsl:if test="string-length($pText)">
            <xsl:variable name="substring"
                          select="substring-before($pText, $delimiter)"/>
            <xsl:choose>
                <xsl:when test="$substring = $delimiter"/>
                <xsl:when test="string-length($substring) = 0">
                    <token>
                        <xsl:value-of select="$pText"/>
                    </token>
                </xsl:when>
                <xsl:otherwise>
                    <token>
                        <xsl:value-of select="$substring"/>
                    </token>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:call-template name="tokenize">
                <xsl:with-param name="pText"
                                select="substring-after($pText, $delimiter)"/>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>