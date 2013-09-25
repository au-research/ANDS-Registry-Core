<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rif="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:oai="http://www.openarchives.org/OAI/2.0/" exclude-result-prefixes="oai xsi" version="1.0">
	<xsl:output indent="yes"/>
	<xsl:strip-space elements="*"/>
	<xsl:template match="/">
		<xsl:element name="registryObjects" xmlns="http://ands.org.au/standards/rif-cs/registryObjects" >
			<xsl:attribute name="xsi:schemaLocation">http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/schema/registryObjects.xsd</xsl:attribute>
			<xsl:apply-templates select="//rif:registryObject"/>
		</xsl:element>
	</xsl:template>
	<!-- Process Location elements -->
	<xsl:template match="rif:location[position() = 1]">
		<!-- if "@type" isn't "coverage" or hasn't got a "@type" then just copy them to the result tree -->
		<xsl:apply-templates select="../rif:location[@type !='coverage'] | ../rif:location[not(@type)]"
			mode="location"/>
		<!-- if "@type" is "coverage" create new coverage element for them -->
		<xsl:apply-templates select="../rif:location[@type='coverage']" mode="coverage"/>
		<!-- convert description[@type='temporal'] into coverage/temporal/text -->
		<xsl:apply-templates select="../rif:description[@type='temporal']" mode="coverage"/>
	</xsl:template>

	<xsl:template match="rif:location"/>

	<xsl:template match="rif:location" mode="location">
		<xsl:copy>
			<xsl:apply-templates select="@* | node()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="rif:location" mode="coverage">
		<!-- if location element contains a spatial element then create "coverage/spatial and copy the content" -->
		<xsl:element name="coverage" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:if test="rif:spatial">
				<xsl:apply-templates select="rif:spatial"/>
			</xsl:if>
			<!-- if location element has "@dateFrom or @dateTo" then create "coverage/temporal" -->
			<xsl:if test="@dateFrom | @dateTo">
				<xsl:element name="temporal" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
					<xsl:if test="@dateFrom">
						<xsl:element name="date" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
							<xsl:attribute name="type">dateFrom</xsl:attribute>
							<xsl:attribute name="dateFormat">UTC</xsl:attribute>
							<xsl:value-of select="@dateFrom"/>
						</xsl:element>
					</xsl:if>
					<xsl:if test="@dateTo">
						<xsl:element name="date" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
							<xsl:attribute name="type">dateTo</xsl:attribute>
							<xsl:attribute name="dateFormat">UTC</xsl:attribute>
							<xsl:value-of select="@dateTo"/>
						</xsl:element>
					</xsl:if>
				</xsl:element>
			</xsl:if>
		</xsl:element>
	</xsl:template>

	<!-- rif:electronic[@type='fax'] | rif:electronic[@type='voice'] is moved to physical/addressPart[@type='faxNumber/telephoneNumber'] -->

	<xsl:template match="rif:electronic[@type='fax'] | rif:electronic[@type='voice']">
		<xsl:element name="physical" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:element name="addressPart" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
				<xsl:attribute name="type">
					<xsl:if test="@type = 'voice'">
						<xsl:text>telephoneNumber</xsl:text>
					</xsl:if>
					<xsl:if test="@type = 'fax'">
						<xsl:text>faxNumber</xsl:text>
					</xsl:if>
				</xsl:attribute>
				<xsl:choose>
					<xsl:when test="contains(rif:value , ':')">
						<xsl:value-of select="substring-after(rif:value, ':')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="rif:value"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
		</xsl:element>
	</xsl:template>


	<!-- for people who read the old guide -->

	<!-- remove the @dateformat from the temporal element and append them to all of its date children -->

	<xsl:template match="rif:temporal/@dateFormat"/>

	<xsl:template match="rif:electronic[not(rif:value)]"/>

	<xsl:template match="rif:temporal/rif:date[not(@dateFormat)]">
		<xsl:copy>
			<xsl:apply-templates select="@*"/>
			<xsl:if test="parent::rif:temporal/@dateFormat">
				<xsl:attribute name="dateFormat">
					<xsl:value-of select="parent::rif:temporal/@dateFormat"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="text()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="rif:temporal/rif:date/@type[. = 'from']">
		<xsl:attribute name="type">dateFrom</xsl:attribute>
	</xsl:template>
	
	<xsl:template match="rif:temporal/rif:date/@type[. = 'to']">
		<xsl:attribute name="type">dateTo</xsl:attribute>
	</xsl:template>


	<xsl:template match="rif:description[@type='temporal']"/>

	<xsl:template match="rif:description" mode="coverage">
		<xsl:element name="coverage" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:element name="temporal" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
				<xsl:element name="text" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
					<xsl:value-of select="text()"/>
				</xsl:element>
			</xsl:element>
		</xsl:element>

	</xsl:template>

	<xsl:template match="rif:relatedInfo[not(rif:identifier)]">
		<xsl:element name="relatedInfo" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
			<xsl:element name="identifier" xmlns="http://ands.org.au/standards/rif-cs/registryObjects">
				<xsl:attribute name="type">uri</xsl:attribute>
				<xsl:value-of select="text()"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<!-- copy all other nodes and attributes -->
	<xsl:template match="@* | node()">
		<xsl:copy>
			<xsl:apply-templates select="@* | node()"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>
