<?xml version="1.0"?>
<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output omit-xml-declaration="yes" indent="yes"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dateRequested"/>
    <xsl:param name="dateHarvested"/>
    <xsl:variable name="smallcase" select="'abcdefghijklmnopqrstuvwxyz'" />
    <xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="ro:registryObject"/>

    <xsl:template match="ro:registryObject[ro:collection]">
        <xsl:variable name="sourceUrl">
            <xsl:call-template name="getSourceURL"/>
        </xsl:variable>
        <xsl:variable name="lower_type" select="translate(ro:collection/@type, $uppercase, $smallcase)"/>
        <xsl:if test="not(extRif:extendedMetadata/extRif:annotations/extRif:tags/extRif:tag[@type='secret' and text() = 'excludeDCI']) and  $sourceUrl != '' and ($lower_type = 'collection' or $lower_type = 'repository' or $lower_type = 'dataset')">
            <DataRecord>
                <Header>
                    <DateProvided>
                <xsl:value-of select="$dateRequested"/>
                    </DateProvided>
                    <RepositoryName>
                <xsl:value-of select="@group"/>
                <!--xsl:choose>
                <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:context">
                                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:context"/>
                </xsl:when>
                            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher">
                                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="@group"/>
                            </xsl:otherwise>
                </xsl:choose-->
                    </RepositoryName>
                    <Owner>
                        <xsl:value-of select="@group"/>
                    </Owner>
                    <RecordIdentifier>
                        <xsl:value-of select="ro:key"/>
                    </RecordIdentifier>
                </Header>
                <BibliographicData>
                    <AuthorList>
                        <xsl:choose>
                            <!-- see if we have citationMetadatata -->
                            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor">
                                <xsl:apply-templates select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor"/>
                            </xsl:when>
                        </xsl:choose>
                    </AuthorList>
                    <TitleList>
                        <ItemTitle TitleType="English title">
                            <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
                        </ItemTitle>
                    </TitleList>
                    <Source>
                        <SourceURL>
                            <xsl:value-of select="$sourceUrl"/>
                        </SourceURL>
                        <xsl:if test="extRif:extendedMetadata/extRif:dataSourceTitle">
                            <SourceRepository>
                                <xsl:choose>
                                    <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher">
                                        <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:publisher"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="@group"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </SourceRepository>
                        </xsl:if>
                        <xsl:variable name="createdDate">
                            <xsl:call-template name="getCreatedDate"/>
                        </xsl:variable>
                        <xsl:if test="$createdDate != ''">
                            <CreatedDate>
                                <xsl:value-of select="$createdDate"/>
                            </CreatedDate>
                        </xsl:if>
                        <xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version">
                            <Version>
                                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:version"/>
                            </Version>
                        </xsl:if>
                    </Source>
                    <LanguageList>
                        <Language>English</Language>
                    </LanguageList>
                </BibliographicData>
                <xsl:if test="ro:collection/ro:description">
                    <Abstract>
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
                        <xsl:apply-templates select="ro:collection/ro:description[@type = 'note']"/>
                        <xsl:apply-templates select="ro:collection/ro:description[@type = 'lineage']"/>
                    </Abstract>
                </xsl:if>

                <xsl:choose>
                    <xsl:when test="extRif:extendedMetadata/extRif:right">
                        <Rights_Licensing>
                            <RightsStatement>
                                <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='rightsStatement'] | extRif:extendedMetadata/extRif:right[@type='accessRights'] | extRif:extendedMetadata/extRif:right[@type='rights']"/>
                            </RightsStatement>
                            <LicenseStatement>
                                <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@type='licence']"/>
                            </LicenseStatement>
                        </Rights_Licensing>
                    </xsl:when>
                </xsl:choose>

                <!-- <ParentDataRef/> <relatedObject>
                    <key>EMBL-NC-1</key>
                    <relation type="isPartOf"/>
                    </relatedObject>-->
                <!-- PROBABLY THEY WANT THEIR INTERNAL IDs -->
                <xsl:if test="ro:collection/ro:relatedObject/ro:relation/@type = 'isPartOf'">
                    <!-- XXX: Temporary fix to only include the first isPartOf relationship (can be many-to-many in RIFCS, but not DCI) -->
                    <ParentDataRef><xsl:apply-templates select="ro:collection/ro:relatedObject[ro:relation/@type = 'isPartOf'][1]/ro:key"/></ParentDataRef>
                </xsl:if>

                <DescriptorsData>
                    <xsl:if test="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved">
                        <KeywordsList>
                            <xsl:apply-templates select="extRif:extendedMetadata/extRif:subjects/extRif:subject/extRif:subject_resolved"/>
                        </KeywordsList>
                    </xsl:if>
                    <!--
                    <xs:element name="DataType" minOccurs="0">
                        <xs:annotation>
                            <xs:documentation>Type of data represented. E.g. survey data, protein sequence data etc.</xs:documentation>
                        </xs:annotation>
                    </xs:element>
                    -->
                    <xsl:if test="ro:collection/ro:coverage/ro:spatial"> <!--| ro:collection/ro:location/ro:spatial"-->
                        <GeographicalData>
                            <xsl:apply-templates select="ro:collection/ro:coverage/ro:spatial" /> <!--| ro:collection/ro:location/ro:spatial"-->
                        </GeographicalData>
                    </xsl:if>
                    <!--
                    <xs:element name="OrganismList" minOccurs="0">
                        <xs:annotation>
                            <xs:documentation>Orgainsm names used in the data resource. Latin names preferred</xs:documentation>
                        </xs:annotation>
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element name="OrganismName" type="xs:string" maxOccurs="unbounded"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                    <xs:element name="GeneNameList" minOccurs="0">
                        <xs:annotation>
                            <xs:documentation>Gene names used in the resource. One gene name per element</xs:documentation>
                        </xs:annotation>
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element name="GeneName" type="xs:string" maxOccurs="unbounded"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                    -->
                    <xsl:if test="ro:collection/ro:relatedInfo[@type='reuseInformation']">
                        <MethodologyList>
                            <xsl:apply-templates select="ro:collection/ro:relatedInfo[@type='reuseInformation']"/>
                        </MethodologyList>
                    </xsl:if>
                    <xsl:if test="ro:collection/ro:coverage/ro:temporal/ro:date">
                        <TimeperiodList>
                            <xsl:apply-templates select="ro:collection/ro:coverage/ro:temporal/ro:date"/>
                        </TimeperiodList>
                    </xsl:if>
                    <!--
                    <xs:element name="MethodologyList" minOccurs="0">
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element name="Methodology" type="xs:string" maxOccurs="unbounded"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                    <xs:element name="DemographicList" minOccurs="0">
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element name="Demographic" type="xs:string" maxOccurs="unbounded"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                    -->
                    <xsl:if test="ro:collection/ro:subject[@type = 'AU-ANL:PEAU'] or ro:collection/ro:subject[@type = 'orcid']">
                        <NamedPersonList>
                            <xsl:apply-templates select="ro:collection/ro:subject[@type = 'AU-ANL:PEAU'] | ro:collection/ro:subject[@type = 'orcid']" mode="namedPerson"/>
                        </NamedPersonList>
                    </xsl:if>

                </DescriptorsData>

                <xsl:if test="ro:collection/ro:relatedObject[ro:relation/@type = 'isOutputOf']">
                    <FundingInfo>
                        <xsl:apply-templates select="ro:collection/ro:relatedObject[ro:relation/@type = 'isOutputOf']" mode="fundingInfo"/>
                    </FundingInfo>
                </xsl:if>
                <!--
                <MicrocitationData/>-->
                <xsl:if test="ro:collection/ro:relatedInfo[@type='publication']">
                    <CitationList postproc="1">
                        <xsl:apply-templates select="ro:collection/ro:relatedInfo[@type='publication']"/>
                    </CitationList>
                </xsl:if>
            </DataRecord>
        </xsl:if>
    </xsl:template>


    <xsl:template match="extRif:displayTitle">
        <xsl:value-of select="."/>
    </xsl:template>

    <xsl:template match="ro:collection/ro:relatedInfo[@type='reuseInformation']">
        <Methodology>
            <xsl:value-of select="ro:title" /> 

            <xsl:if test="ro:identifier">
               <xsl:apply-templates select="ro:identifier" mode="methodology"/>
            </xsl:if>

            <xsl:if test="ro:notes != ''"> (<xsl:value-of select="ro:notes"/>)</xsl:if>
        </Methodology>
    </xsl:template>

    <xsl:template match="ro:identifier" mode="methodology">
         &lt;<xsl:if test="@type!='uri'">
                    <xsl:value-of select="@type"/><xsl:text>:</xsl:text>
                </xsl:if> 
                <xsl:value-of select="."/>&gt; 
    </xsl:template>

    <xsl:template match="ro:collection/ro:relatedInfo[@type='publication']">
        <Citation CitationType="Citing Ref">
            <CitationText>
                <CitationString>
                    <xsl:value-of select="ro:title" />
                    <xsl:if test="ro:identifier[@type='uri']">
                        <xsl:text> &lt;</xsl:text>
                        <xsl:value-of select="ro:identifier" />
                        <xsl:text>&gt;</xsl:text>
                    </xsl:if>
                    <xsl:if test="ro:identifier[@type!='uri']">
                        <xsl:text> &lt;</xsl:text>
                        <xsl:value-of select="ro:identifier/@type" />
                        <xsl:text>: </xsl:text>
                        <xsl:value-of select="ro:identifier" />
                        <xsl:text>&gt;</xsl:text>
                    </xsl:if>
                    <xsl:if test="ro:notes">
                        <xsl:text> (</xsl:text>
                        <xsl:value-of select="ro:notes" />
                        <xsl:text>)</xsl:text>
                    </xsl:if>
                </CitationString>
            </CitationText>
        </Citation>
    </xsl:template>

    <xsl:template match="extRif:right[@type='rightsStatement' and (text() or @rightsUri != '')] | extRif:right[@type='accessRights' and (text() or @rightsUri != '')] | extRif:right[@type='rights' and (text() or @rightsUri != '')]">
        <xsl:value-of select="."/>
        <xsl:apply-templates select="@rightsUri"/>
        <xsl:if test="following-sibling::extRif:right[@type='rightsStatement' and text()] | following-sibling::extRif:right[@type='accessRights' and text()] | following-sibling::extRif:right[@type='rights' and text()]">
            <xsl:text>           
        </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="@rightsUri">
        <xsl:if test="parent::node()[text()]">
            <xsl:text>  </xsl:text>
        </xsl:if>
        <xsl:value-of select="."/>
    </xsl:template>


    <xsl:template match="extRif:right[@type='licence']">
        <xsl:value-of select="."/>
        <xsl:if test="following-sibling::extRif:right[@type='licence']">
            <xsl:text>           
        </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template name="getSourceURL">
        <xsl:variable name="sourceUrl">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='handle']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='uri']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='purl']"/>
            </xsl:when>         
            <xsl:when test="ro:collection/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='handle']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='handle']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='uri']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='uri']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:identifier[@type='purl']">
                <xsl:value-of select="ro:collection/ro:identifier[@type='purl']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:url"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:location/ro:address/ro:electronic[@type='url']">
                <xsl:value-of select="ro:collection/ro:location/ro:address/ro:electronic[@type='url']"/>
            </xsl:when>
        </xsl:choose>
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="contains($sourceUrl,'doi.org/')">
                <xsl:value-of select="substring-after($sourceUrl,'doi.org/')"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$sourceUrl"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="extRif:subject_resolved">
        <xsl:if test=". != '' and string(number(.)) = 'NaN'">
            <Keyword>
                <xsl:value-of select="."/>
            </Keyword>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ro:spatial">
        <GeographicalLocation>
            <xsl:value-of select="."/>
        </GeographicalLocation>
    </xsl:template>

    <xsl:template match="ro:date">
        <xsl:choose>
            <xsl:when test="@type='dateFrom'">
                <TimePeriod TimeSpan="Start">
                    <xsl:value-of select="substring(.,1,4)"/>
                </TimePeriod>
            </xsl:when>
            <xsl:when test="@type='dateTo'">
                <TimePeriod TimeSpan="End">
                    <xsl:value-of select="substring(.,1,4)"/>
                </TimePeriod>
            </xsl:when>
            <xsl:otherwise>
                <TimePeriod>
                    <xsl:value-of select="substring(.,1,4)"/>
                </TimePeriod>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="ro:subject" mode="namedPerson">
        <NamedPerson>
            <xsl:value-of select="."/><xsl:if test="@termIdentifier != ''"> (<xsl:value-of select="@termIdentifier" />)</xsl:if>
        </NamedPerson>
    </xsl:template>

    <xsl:template match="ro:contributor">
        <Author seq="{@seq}">
            <AuthorName>
                <xsl:variable name="title">
                    <xsl:apply-templates select="ro:namePart[@type = 'family']"/>
                    <xsl:apply-templates select="ro:namePart[@type = 'given']"/>
                    <xsl:apply-templates select="ro:namePart[@type = 'title']"/>
                    <xsl:apply-templates select="ro:namePart[@type != 'title' and @type != 'given' and @type != 'family']"/>
                    <xsl:apply-templates select="ro:namePart[@type = '' or not(@type)]"/>
                </xsl:variable>
                <xsl:value-of select="substring($title,1,string-length($title)-2)"/>
            </AuthorName>
        </Author>
    </xsl:template>

    <xsl:template match="ro:relatedObject">
        <xsl:if test="not(preceding::ro:relatedObject[ro:key = current()/ro:key])">
            <Author seq="{position()}" postproc="1">
                <xsl:if test="ro:relation">
                    <xsl:attribute name="AuthorRole">
                        <xsl:value-of select="ro:relation/@type"/>
                    </xsl:attribute>
                </xsl:if>
                <AuthorID>
		              <xsl:value-of select="ro:key"/>
                </AuthorID>
            </Author>
        </xsl:if>
    </xsl:template>

    <xsl:template match="ro:relatedObject" mode="fundingInfo">
        <FundingInfoList postproc="1">
            <ParsedFunding>
                <GrantNumber>
                    <xsl:value-of select="ro:key"/>
                </GrantNumber>
            </ParsedFunding>
        </FundingInfoList>
    </xsl:template>

    <xsl:template match="ro:namePart">
        <xsl:value-of select="."/><xsl:text>, </xsl:text>
    </xsl:template>


    <xsl:template match="ro:description">
        <xsl:if test="preceding-sibling::ro:description">
<xsl:text>
 </xsl:text>
        </xsl:if>

        <xsl:value-of select="."/>
    </xsl:template>

    <xsl:template name="getCreatedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='publicationDate'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created'],1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.issued']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.issued']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.available']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.available']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']/text() != ''">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>        
            <xsl:when test="ro:collection/@dateModified != ''">
                <xsl:value-of select="substring(ro:collection/@dateModified,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/@dateAccessioned != ''">
                <xsl:value-of select="substring(ro:collection/@dateAccessioned,1,4)"/>
	        </xsl:when>
	        <xsl:when test="$dateHarvested">
		        <xsl:value-of select="substring($dateHarvested,1,4)" />
	        </xsl:when>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>