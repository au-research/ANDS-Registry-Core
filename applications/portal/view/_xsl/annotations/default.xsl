<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif ro">
	<xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>

	<xsl:template match="extRif:annotations">
	    <xsl:apply-templates select="extRif:digitalAssets"/>
	</xsl:template>

	<xsl:template match="extRif:digitalAssets">
		<xsl:if test="extRif:dataAsset[@visibility='public'] or extRif:dataAsset[@visibility=''] or extRif:dataAsset[not(@visibility)]">
			<div class="right-box">
				<h2>Data</h2>
				<xsl:apply-templates select="extRif:dataAsset"/>
			</div>
		</xsl:if>

		<xsl:if test="extRif:dataAsset/extRif:supportedBy and (extRif:dataAsset[@visibility='public'] or extRif:dataAsset[@visibility=''] or extRif:dataAsset[@visibility='serviceOnly'] or extRif:dataAsset[not(@visibility)])">
			<div class="right-box">
				<h2>Data Tools</h2>
				<xsl:apply-templates select="extRif:dataAsset/extRif:supportedBy"/>
			</div>
		</xsl:if>
		<p><br/></p>
	</xsl:template>

	<xsl:template match="extRif:dataAsset">
		<xsl:if test="@visibility='public' or not(@visibility) or @visibility=''">
		<div class="limitHeight300">
			<p>
			<xsl:apply-templates select="extRif:url"/>
			</p>
		</div>
		</xsl:if>
	</xsl:template>

	<xsl:template match="extRif:url[parent::extRif:dataAsset]">
		<a href="{.}">

			<img>
	       <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
		       <xsl:text>assets/core/images/icons/external_link.png</xsl:text>
		   </xsl:attribute>
		   <xsl:attribute name="alt">External Link</xsl:attribute>
	  </img>
	  <xsl:text>&amp;nbsp;</xsl:text>


			<xsl:choose>
				<xsl:when test="../extRif:title">
					<xsl:apply-templates select="../extRif:title"/>
				</xsl:when>
				<xsl:otherwise>
					 <xsl:choose>
				      <xsl:when test="string-length(.)>30">
					<xsl:value-of select="substring(.,0,30)"/>...
				    </xsl:when>
				    <xsl:otherwise>
					<xsl:value-of select="."/>
				    </xsl:otherwise>
				</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:if test="../extRif:filename | @format | ../extRif:fileSize">
				<br/>
				<span class="small darkgrey">
					<xsl:if test="../extRif:filename">
						<xsl:apply-templates select="../extRif:filename"/>&amp;nbsp;
					</xsl:if>
					<xsl:if test="@format">
						<xsl:value-of select="concat('in ', @format, ' format ')"/>
					</xsl:if>
					<xsl:if test="../extRif:filesize">
						<xsl:apply-templates select="../extRif:filesize"/>
					</xsl:if>
				</span>
			</xsl:if>

		</a><br/>
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
		<a href="{.}" target="_blank">
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
			<p class="small">
			<xsl:choose>
				<xsl:when test="../extRif:title">
					<xsl:apply-templates select="../extRif:title" />
				</xsl:when>
				<xsl:otherwise>
					(load using external data tool)
				</xsl:otherwise>
			</xsl:choose>
			</p>
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