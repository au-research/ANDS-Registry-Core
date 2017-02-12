<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="dc">

    <xsl:template match="/">
        <registryObjects xmlns="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd">
            <xsl:apply-templates select="//oai:record"/>
        </registryObjects>
    </xsl:template>

    <xsl:template match="oai:record">
        <xsl:variable name="key" select="oai:header/oai:identifier/text()"/>
        <xsl:variable name="class" select="substring-after(oai:header/oai:setSpec[starts-with(text(),'class:')]/text(),'class:')"/>
            <xsl:apply-templates select="oai:metadata/dc:dc">
                <xsl:with-param name="key" select="$key"/>
                <xsl:with-param name="class" select="$class"/>
            </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="oai:setSpec">
        <xsl:variable name="key" select="oai:header/oai:identifier/text()"/>
        <xsl:variable name="class" select="oai:header/oai:identifier/text()"/>
        <xsl:apply-templates select="oai:metadata/dc:dc">
            <xsl:with-param name="key" select="$key"/>
        </xsl:apply-templates>
    </xsl:template>

    <xsl:template match="dc:dc">
        <xsl:param name="key" select="'unkown-key'"/>
        <xsl:param name="class" select="'collection'"/>
        <registryObject xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:attribute name="group"><xsl:value-of select="dc:publisher"/></xsl:attribute>
            <key xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
                <xsl:value-of select="$key"/>
            </key>
            <originatingSource><xsl:value-of select="dc:source"/></originatingSource>
            <xsl:element name="{$class}">
                <xsl:attribute name="type"><xsl:value-of select="dc:type"/></xsl:attribute>
                <xsl:apply-templates/>
            </xsl:element>
            
        </registryObject>
    </xsl:template>


    <xsl:template match="dc:title">
        <name type="full" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <namePart>
                <xsl:value-of select="."/>
            </namePart>
        </name>
    </xsl:template>

    <xsl:template match="dc:identifier">
        <xsl:variable name="identifier">
            <xsl:choose>
                <xsl:when test="contains(text(), ')')">
                    <xsl:value-of select="substring-after(text(),') ')"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="text()"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="identifierType">
            <xsl:choose>
                <xsl:when test="contains(text(), ')')">
                    <xsl:value-of select="substring-after(substring-before(text(),')'),'(')"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="'uri'"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <identifier type="{$identifierType}" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:value-of select="$identifier"/>
        </identifier>
    </xsl:template>

    <xsl:template match="dc:contributor">
        <relatedObject xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:variable name="relatedKey">
                <xsl:choose>
                    <xsl:when test="contains(text(), '(')">
                        <xsl:value-of select="substring-before(text(),' (')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="text()"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <xsl:variable name="relationType">
                <xsl:choose>
                    <xsl:when test="contains(text(), '(')">
                        <xsl:value-of select="substring-before(substring-after(text(),' ('),')')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="'hasAssociationWith'"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <key><xsl:value-of select="$relatedKey"/></key>
            <relation type="{$relationType}"/>
        </relatedObject>
    </xsl:template>

    <xsl:template match="dc:description">
        <description type="full" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:value-of select="."/>
        </description>
    </xsl:template>

    <xsl:template match="dc:rights">
        <description type="right" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:value-of select="."/>
        </description>
    </xsl:template>

    <xsl:template match="dc:coverage[starts-with(.,'Temporal:')]">
        <coverage xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <temporal type="text">
                <xsl:value-of select="substring-after(.,'Temporal: ')"/>
            </temporal>
        </coverage>
    </xsl:template>

    <xsl:template match="dc:coverage[starts-with(.,'Spatial: ')]">
        <coverage xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <spatial type="text">
                <xsl:value-of select="substring-after(.,'Spatial:')"/>
            </spatial>
        </coverage>
    </xsl:template>

    <xsl:template match="dc:subject">
        <subject type="local" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
            <xsl:value-of select="."/>
        </subject>
    </xsl:template>
    
    <xsl:template match="node() | text() | @*"/>

</xsl:stylesheet>