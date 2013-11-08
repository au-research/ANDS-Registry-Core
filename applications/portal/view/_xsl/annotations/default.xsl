<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif ro">
	<xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>

	<xsl:template match="extRif:annotations">
	    <xsl:apply-templates select="extRif:digitalAssets"/>
	</xsl:template>

	<xsl:template match="extRif:digitalAssets">
		<div class="right-box">
			<h2>Data</h2>
			<xsl:apply-templates select="extRif:dataAsset"/>
		</div>
	</xsl:template>

	<xsl:template match="extRif:dataAsset">
		<div class="limitHeight300">
			<xsl:apply-templates select="extRif:url"/>
			<xsl:apply-templates select="extRif:supportedBy"/>
		</div>
	</xsl:template>

	<xsl:template match="extRif:url[parent::extRif:dataAsset]">
		<a href="{.}"><xsl:apply-templates select="../extRif:filename"/>&amp;nbsp;<xsl:apply-templates select="../extRif:title"/>&amp;nbsp;<xsl:value-of select="concat('in ', @format, ' format ')"/><xsl:apply-templates select="../extRif:fileSize"/></a><br/>
	</xsl:template>
	
	<xsl:template match="extRif:title[parent::extRif:dataAsset]">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="extRif:filename[parent::extRif:dataAsset]">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="extRif:filesize[parent::extRif:dataAsset]">
		(<xsl:value-of select="."/>)
	</xsl:template>
	
	<xsl:template match="extRif:notes[parent::extRif:dataAsset]">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="extRif:supportedBy">
		<xsl:choose>
			<xsl:when test="extRif:url">
				<xsl:apply-templates select="extRif:url"/>
			</xsl:when>
			<xsl:when test="extRif:key">
				<xsl:apply-templates select="extRif:key"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="extRif:logo"/>
			</xsl:otherwise>
		</xsl:choose>	
	</xsl:template>
		
	<xsl:template match="extRif:url[parent::extRif:supportedBy]">
		<a href="{.}">
			<xsl:choose>
				<xsl:when test="../extRif:logo">
					<xsl:apply-templates select="../extRif:logo"/>
				</xsl:when>
				<xsl:when test="../extRif:title">
					<xsl:apply-templates select="../extRif:title"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="."/>
				</xsl:otherwise>
			</xsl:choose>		
		</a>
	</xsl:template>
	
	<xsl:template match="extRif:key[parent::extRif:supportedBy]">
		<a href="?key={.}">
			<xsl:choose>
				<xsl:when test="../extRif:logo">
					<xsl:apply-templates select="../extRif:logo"/>
				</xsl:when>
				<xsl:when test="../extRif:title">
					<xsl:apply-templates select="../extRif:title"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="."/>
				</xsl:otherwise>
			</xsl:choose>		
		</a>
	</xsl:template>
	
	<xsl:template match="extRif:logo[parent::extRif:supportedBy]">
		<img class="logo" style="max-width:130px;max-height:none;height:auto" src="{.}">
			<xsl:apply-templates select="../extRif:title" mode="attribute"/>
		</img>
	</xsl:template>

	<xsl:template match="extRif:title" mode="title">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="extRif:title" mode="attribute">
		<xsl:attribute name="title">
			<xsl:value-of select="."/>		
		</xsl:attribute>
		<xsl:attribute name="alt">
			<xsl:value-of select="."/>		
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>