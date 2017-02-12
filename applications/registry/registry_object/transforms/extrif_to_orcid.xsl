<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">
<xsl:param name="base_url"/>
<xsl:param name="rda_url"/>
<!-- http://support.orcid.org/knowledgebase/articles/118795-->
    <xsl:output indent="yes" omit-xml-declaration="yes"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>   
    
    <xsl:template match="ro:registryObject">
        <orcid-work>
            <work-title>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/>
                <xsl:if test="ro:collection/ro:name[@type='alternative']">
                    <xsl:apply-templates select="ro:collection/ro:name[@type='alternative']"/>
                </xsl:if>
            </work-title>
            <xsl:if test="extRif:extendedMetadata/extRif:dci_description">
                <short-description>
                    <xsl:apply-templates select="extRif:extendedMetadata/extRif:dci_description"/>
                </short-description>
            </xsl:if>
            <xsl:if test="ro:collection/ro:citationInfo/ro:fullCitation">
                <work-citation>
                    <work-citation-type>
                        <xsl:variable name="style" select="ro:collection/ro:citationInfo/ro:fullCitation/@style"/>
                        <xsl:choose>
                            <xsl:when test="$style = 'Harvard'"><xsl:text>formatted-harvard</xsl:text></xsl:when>
                            <xsl:when test="$style = 'APA'"><xsl:text>formatted-apa</xsl:text></xsl:when>
                            <xsl:when test="$style = 'IEEE'"><xsl:text>formatted-ieee</xsl:text></xsl:when>
                            <xsl:when test="$style = 'MLA'"><xsl:text>formatted-mla</xsl:text></xsl:when>
                            <xsl:when test="$style = 'Vancouver'"><xsl:text>formatted-vancouver</xsl:text></xsl:when>
                            <xsl:when test="$style = 'Chicago'"><xsl:text>formatted-chicago</xsl:text></xsl:when>
                            <xsl:when test="($style = 'Bibtex') or $style = 'bibtex'"><xsl:text>bibtex</xsl:text></xsl:when>
                            <xsl:otherwise>
                                <xsl:text>formatted-unspecified</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </work-citation-type>
                    <citation><xsl:value-of select="ro:collection/ro:citationInfo/ro:fullCitation/text()"/></citation>
                </work-citation>
            </xsl:if>
            <work-type>
                <xsl:choose>
                    <xsl:when test="ro:collection/@type='book'">book</xsl:when>
                    <xsl:when test="ro:collection/@type='Book'">book</xsl:when>
                    <xsl:when test="ro:collection/@type='advertisement'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='journal-issue'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='audio-visual'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='brochure'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='cartoon-comic'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='translation'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='chapter-anthology'">book-chapter</xsl:when>
                    <xsl:when test="ro:collection/@type='chapter-chapter'">book-chapter</xsl:when>
                    <xsl:when test="ro:collection/@type='components'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='conference-proceedings'">conference-paper</xsl:when>
                    <xsl:when test="ro:collection/@type='conference-paper'">conference-paper</xsl:when>
                    <xsl:when test="ro:collection/@type='congressional-publication'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='standards-policy'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='court-case'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='database'">data-set</xsl:when>
                    <xsl:when test="ro:collection/@type='data-set'">data-set</xsl:when>
                    <xsl:when test="ro:collection/@type='dictionary-entry'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='digital-image'">online-resource</xsl:when>
                    <xsl:when test="ro:collection/@type='online-resource'">online-resource</xsl:when>
                    <xsl:when test="ro:collection/@type='dissertation-abstract'">dissertation</xsl:when>
                    <xsl:when test="ro:collection/@type='dissertation'">dissertation</xsl:when>
                    <xsl:when test="ro:collection/@type='email'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='research-tool'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='manual'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='editorial'">magazine-article</xsl:when>
                    <xsl:when test="ro:collection/@type='electronic-only'">online-resource</xsl:when>
                    <xsl:when test="ro:collection/@type='encyclopedia-article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='executive-order'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='federal-bill'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='federal-report'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='federal-rule'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='federal-statute'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='federal-testimony'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='film-movie'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='government-publication'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='interview'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='journal-article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='lecture-speech'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='legal'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='letter'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='live-performance'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='magazine-article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='mailing-list'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='manuscript'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='map-chart'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='musical-recording'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='newsgroup'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='newsletter'">newsletter-article</xsl:when>
                    <xsl:when test="ro:collection/@type='newsletter-article'">newsletter-article</xsl:when>
                    <xsl:when test="ro:collection/@type='newspaper-article'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='non-periodicals'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='other'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='painting'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='pamphlet'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='patent'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='periodicals'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='photograph'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='press-release'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='raw-data'">data-set</xsl:when>
                    <xsl:when test="ro:collection/@type='religious-text'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='report'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='reports-working-papers'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='review'">book-review</xsl:when>
                    <xsl:when test="ro:collection/@type='book-review'">book-review</xsl:when>
                    <xsl:when test="ro:collection/@type='scholarly-project'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='software'">online-resource</xsl:when>
                    <xsl:when test="ro:collection/@type='standards'">standards-policy</xsl:when>
                    <xsl:when test="ro:collection/@type='television-radio'">artistic-performance</xsl:when>
                    <xsl:when test="ro:collection/@type='theological-text'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='thesis'">supervised-student-publication</xsl:when>
                    <xsl:when test="ro:collection/@type='supervised-student-publication'">supervised-student-publication</xsl:when>
                    <xsl:when test="ro:collection/@type='web-site'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='test'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='undefined'">other</xsl:when>
                    <xsl:when test="ro:collection/@type='technical-standard'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='spin-off-company'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='research-technique'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='invention'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='registered-copyright'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='disclosure'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='license'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='conference-abstract'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:when test="ro:collection/@type='conference-poster'"><xsl:value-of select="ro:collection/@type"/></xsl:when>
                    <xsl:otherwise>other</xsl:otherwise>
                </xsl:choose>
            </work-type>
            <xsl:variable name="createdDate">
                <xsl:call-template name="getCreatedDate"/>
            </xsl:variable>
            <xsl:if test="$createdDate != ''">
                <publication-date>
                    <year>
                        <xsl:value-of select="$createdDate"/>
                    </year>
                </publication-date>
            </xsl:if>
            <xsl:if test="//ro:identifier[text()!='']">
                <work-external-identifiers>
                    <xsl:apply-templates select="//ro:identifier[text()!='']"/>
                </work-external-identifiers>
            </xsl:if>
            <!-- <xsl:variable name="sourceUrl">
                <xsl:call-template name="getSourceURL"/>
            </xsl:variable>
            <xsl:if test="$sourceUrl != ''">
                <url><xsl:value-of select="$sourceUrl"/></url>
            </xsl:if> -->
            <url><xsl:value-of select="$rda_url"/></url>
            <xsl:if test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor">
                <work-contributors>
                    <xsl:apply-templates select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:contributor"/>
                </work-contributors>
            </xsl:if>          
        </orcid-work>
    </xsl:template> 



    <xsl:template match="extRif:displayTitle">
        <title> <xsl:value-of select="."/></title>
    </xsl:template>

    <xsl:template match="ro:name[@type='alternative']">
        <subtitle> <xsl:value-of select="."/></subtitle>
    </xsl:template>

    <xsl:template match="ro:identifier">
        <work-external-identifier>
            <work-external-identifier-type>
                <xsl:choose>
                    <xsl:when test="(@type='arxiv') 
                        or (@type='asin') 
                        or (@type='asin-tld') 
                        or (@type='bibcode') 
                        or (@type='doi')
                        or (@type='eid')
                        or (@type='isbn')
                        or (@type='issn')
                        or (@type='jfm')
                        or (@type='jstor')
                        or (@type='lccn')
                        or (@type='mr')
                        or (@type='oclc')
                        or (@type='ol')
                        or (@type='osti')
                        or (@type='pmc')
                        or (@type='pmid')
                        or (@type='rfc')
                        or (@type='ssrn')
                        or (@type='zbl')
                        ">
                        <xsl:value-of select="@type"/>
                    </xsl:when>
                    <xsl:otherwise>other-id</xsl:otherwise>
                </xsl:choose>
                <!-- http://support.orcid.org/knowledgebase/articles/118807 -->
            </work-external-identifier-type>
          <work-external-identifier-id><xsl:value-of select="."/></work-external-identifier-id>
        </work-external-identifier>
    </xsl:template>

    <xsl:template match="ro:namePart">
        <xsl:value-of select="."/><xsl:text>, </xsl:text>
    </xsl:template>

    <xsl:template name="getCreatedDate">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='created']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:date[@type='issued']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:dates[@type='dc.created']">
                <xsl:value-of select="substring(ro:collection/ro:dates[@type='dc.created']/ro:date,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:location/@dateFrom">
                <xsl:value-of select="substring(ro:collection/ro:location/@dateFrom,1,4)"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:coverage/ro:temporal/ro:date[@type= 'dateFrom']">
                <xsl:value-of select="substring(ro:collection/ro:coverage/ro:temporal/ro:date[@type= 'dateFrom']/text() ,1,4)"/>
            </xsl:when>        
            <xsl:when test="ro:collection/@dateModified">
                <xsl:value-of select="substring(ro:collection/@dateModified,1,4)"/>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="getSourceURL">
        <xsl:choose>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
            <xsl:when test="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']">
                <xsl:value-of select="ro:collection/ro:citationInfo/ro:citationMetadata/ro:identifier[@type='doi']"/>
            </xsl:when>
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
    </xsl:template>
   
    <xsl:template match="ro:contributor">
      <contributor>
        <credit-name>
            <xsl:variable name="title">
                <xsl:apply-templates select="ro:namePart[@type = 'family']"/>
                <xsl:apply-templates select="ro:namePart[@type = 'given']"/>
                <xsl:apply-templates select="ro:namePart[@type = 'title']"/>
                <xsl:apply-templates select="ro:namePart[@type = '' or not(@type)]"/>
            </xsl:variable>
            <xsl:value-of select="substring($title,1,string-length($title)-2)"/>
        </credit-name>
        <contributor-attributes>
          <contributor-sequence>
            <xsl:choose>
                <xsl:when test="@seq=1">first</xsl:when>
                <xsl:otherwise>additional</xsl:otherwise>
            </xsl:choose>
        </contributor-sequence>
        <contributor-role>
            author
            <!-- author,  assignee,  editor,  chair-or-translator,  co-investigator,  co-inventor,  graduate-student,  other-inventor,  principal-investigator,  postdoctoral-researcher,  support-staff-->
        </contributor-role>
        </contributor-attributes>
      </contributor>
    </xsl:template>

</xsl:stylesheet>

