<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                xmlns:mdb="http://standards.iso.org/iso/19115/-3/mdb/1.0"
                xmlns:cat="http://standards.iso.org/iso/19115/-3/cat/1.0"
                xmlns:cit="http://standards.iso.org/iso/19115/-3/cit/1.0"
                xmlns:gco="http://standards.iso.org/iso/19115/-3/gco/1.0"
                xmlns:gcx="http://standards.iso.org/iso/19115/-3/gcx/1.0"
                xmlns:gex="http://standards.iso.org/iso/19115/-3/gex/1.0"
                xmlns:gfc="http://standards.iso.org/iso/19110/gfc/1.1"
                xmlns:gml="http://www.opengis.net/gml/3.2"
                xmlns:lan="http://standards.iso.org/iso/19115/-3/lan/1.0"
                xmlns:mac="http://standards.iso.org/iso/19115/-3/mac/1.0"
                xmlns:mas="http://standards.iso.org/iso/19115/-3/mas/1.0"
                xmlns:mcc="http://standards.iso.org/iso/19115/-3/mcc/1.0"
                xmlns:mco="http://standards.iso.org/iso/19115/-3/mco/1.0"
                xmlns:mda="http://standards.iso.org/iso/19115/-3/mda/1.0"
                xmlns:mdq="http://standards.iso.org/iso/19157/-2/mdq/1.0"
                xmlns:mds="http://standards.iso.org/iso/19115/-3/mds/1.0"
                xmlns:mdt="http://standards.iso.org/iso/19115/-3/mdt/1.0"
                xmlns:mex="http://standards.iso.org/iso/19115/-3/mex/1.0"
                xmlns:mmi="http://standards.iso.org/iso/19115/-3/mmi/1.0"
                xmlns:mpc="http://standards.iso.org/iso/19115/-3/mpc/1.0"
                xmlns:mrc="http://standards.iso.org/iso/19115/-3/mrc/1.0"
                xmlns:mrd="http://standards.iso.org/iso/19115/-3/mrd/1.0"
                xmlns:mri="http://standards.iso.org/iso/19115/-3/mri/1.0"
                xmlns:mrl="http://standards.iso.org/iso/19115/-3/mrl/1.0"
                xmlns:mrs="http://standards.iso.org/iso/19115/-3/mrs/1.0"
                xmlns:msr="http://standards.iso.org/iso/19115/-3/msr/1.0"
                xmlns:srv="http://standards.iso.org/iso/19115/-3/srv/2.0"
                exclude-result-prefixes="ro">

    <!-- stylesheet to convert RIFCS service xml to ISO19115-3 service record -->

    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

    <xsl:strip-space elements="*"/>

    <xsl:template match="/ | ro:registryObjects | ro:registryObject">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="ro:service">
        <xsl:element name="mdb:MD_Metadata">
            <xsl:attribute name="xsi:schemaLocation">
                <xsl:text>http://standards.iso.org/iso/19115/-3/gco/1.0 ../../../etc/schema/19115/-3/gco/1.0/gco.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/cat/1.0 ../../../etc/schema/19115/-3/cat/1.0/cat.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/cit/1.0 ../../../etc/schema/19115/-3/cit/1.0/cit.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/gcx/1.0 ../../../etc/schema/19115/-3/gcx/1.0/gcx.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/gex/1.0 ../../../etc/schema/19115/-3/gex/1.0/gex.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/lan/1.0 ../../../etc/schema/19115/-3/lan/1.0/lan.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/srv/2.0 ../../../etc/schema/19115/-3/srv/2.0/srv.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mas/1.0 ../../../etc/schema/19115/-3/mas/1.0/mas.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mcc/1.0 ../../../etc/schema/19115/-3/mcc/1.0/mcc.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mco/1.0 ../../../etc/schema/19115/-3/mco/1.0/mco.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mda/1.0 ../../../etc/schema/19115/-3/mda/1.0/mda.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mdb/1.0 ../../../etc/schema/19115/-3/mdb/1.0/mdb.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mds/1.0 ../../../etc/schema/19115/-3/mds/1.0/mds.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mdt/1.0 ../../../etc/schema/19115/-3/mdt/1.0/mdt.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mex/1.0 ../../../etc/schema/19115/-3/mex/1.0/mex.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mmi/1.0 ../../../etc/schema/19115/-3/mmi/1.0/mmi.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mpc/1.0 ../../../etc/schema/19115/-3/mpc/1.0/mpc.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mrc/1.0 ../../../etc/schema/19115/-3/mrc/1.0/mrc.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mrd/1.0 ../../../etc/schema/19115/-3/mrd/1.0/mrd.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mri/1.0 ../../../etc/schema/19115/-3/mri/1.0/mri.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mrl/1.0 ../../../etc/schema/19115/-3/mrl/1.0/mrl.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mrs/1.0 ../../../etc/schema/19115/-3/mrs/1.0/mrs.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/msr/1.0 ../../../etc/schema/19115/-3/msr/1.0/msr.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19157/-2/mdq/1.0 ../../../etc/schema/19157/-2/mdq/1.0/mdq.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19115/-3/mac/1.0 ../../../etc/schema/19115/-3/mac/1.0/mac.xsd</xsl:text>
                <xsl:text> http://standards.iso.org/iso/19110/gfc/1.1 ../../../etc/schema/19110/gfc/1.1/gfc.xsd</xsl:text>
                <xsl:text> http://www.opengis.net/gml/3.2 ../../../etc/schema/19136/gml.xsd</xsl:text>
                <xsl:text> http://www.w3.org/1999/xlink ../../../etc/schema/xlink.xsd</xsl:text>
            </xsl:attribute>
            <xsl:apply-templates select="ro:identifier"/>
            <xsl:call-template name="addScope">
                <xsl:with-param name="scope" select="local-name()"></xsl:with-param>
            </xsl:call-template>
            <!---

     FILL THIS IN
                        -->
            <mdb:contact>
                <xsl:choose>
                    <xsl:when test="ro:location/ro:address/ro:physical | ro:location/ro:address/ro:electronic">
                        <cit:CI_Responsibility>
                            <cit:role>
                                <cit:CI_RoleCode codeList="https://standards.iso.org/iso/19115/resources/Codelists/cat/codelists.xml#CI_RoleCode" codeListValue="pointOfContact"/>
                            </cit:role>
                            <cit:party>
                                <cit:CI_Organisation>
                                    <cit:name>
                                        <gco:CharacterString>
                                            <xsl:apply-templates select="//ro:addressPart[position() = 1]" mode="partyName"/>
                                        </gco:CharacterString>
                                    </cit:name>
                                    <cit:contactInfo>
                                        <cit:CI_Contact>
                                            <xsl:apply-templates select="ro:location/ro:address/ro:physical/ro:addressPart[@type = 'telephoneNumber' or @type='faxNumber']"/>
                                            <cit:address>
                                                <cit:CI_Address>
                                                    <xsl:apply-templates select="ro:location/ro:address/ro:physical/ro:addressPart[position() > 1]" mode="deliveryPoint"/>
                                                    <xsl:apply-templates select="ro:location/ro:address/ro:electronic[@type = 'email']"/>
                                                </cit:CI_Address>
                                            </cit:address>
                                        </cit:CI_Contact>
                                    </cit:contactInfo>
                                </cit:CI_Organisation>
                            </cit:party>
                        </cit:CI_Responsibility>
                    </xsl:when>
                    <xsl:otherwise>
                        <cit:CI_Responsibility xmlns="http://standards.iso.org/iso/19115/-3/cit/1.0">
                            <cit:role></cit:role>
                            <cit:party></cit:party>
                        </cit:CI_Responsibility>
                    </xsl:otherwise>
                </xsl:choose>
            </mdb:contact>
            <mdb:dateInfo></mdb:dateInfo>
            <!--
            END FILL
            -->
            <xsl:call-template name="addMetadataStandard"/>
            <mdb:identificationInfo>
                <srv:SV_ServiceIdentification>
                    <mri:citation>
                        <cit:CI_Citation>
                            <cit:title>
                                <xsl:apply-templates select="ro:name"/>
                            </cit:title>
                        </cit:CI_Citation>
                    </mri:citation>
                    <xsl:apply-templates select="ro:description"/>
                    <xsl:apply-templates select="ro:coverage/ro:spatial"/>
                    <xsl:if test="ro:subject">
                        <mri:descriptiveKeywords>
                            <mri:MD_Keywords>
                                <xsl:apply-templates select="ro:subject"/>
                            </mri:MD_Keywords>
                        </mri:descriptiveKeywords>
                    </xsl:if>                      
                    <xsl:choose>
                        <xsl:when test="ro:rights">
                            <xsl:apply-templates select="ro:rights"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <mri:resourceConstraints>
                                <mco:MD_Constraints><mco:useLimitation><gco:CharacterString>none</gco:CharacterString></mco:useLimitation></mco:MD_Constraints>
                            </mri:resourceConstraints> 
                        </xsl:otherwise>
                    </xsl:choose>

                    <srv:serviceType><gco:ScopedName><xsl:value-of select="@type"/></gco:ScopedName></srv:serviceType>
                    <xsl:apply-templates select="ro:relatedInfo"/>

                </srv:SV_ServiceIdentification>
            </mdb:identificationInfo>
        </xsl:element>
    </xsl:template>

    <xsl:template match="ro:addressPart[@type = 'telephoneNumber']">
        <cit:phone>
            <cit:CI_Telephone>
                <cit:number>
                    <gco:CharacterString><xsl:value-of select="."/></gco:CharacterString>
                </cit:number>
                <cit:numberType>
                    <cit:CI_TelephoneTypeCode codeList="https://standards.iso.org/iso/19115/resources/Codelists/cat/codelists.xml#CI_TelephoneTypeCode" codeListValue="voice"/>
                </cit:numberType>
            </cit:CI_Telephone>
        </cit:phone>
    </xsl:template>

    <xsl:template match="ro:addressPart[@type = 'faxNumber']">
        <cit:phone>
            <cit:CI_Telephone>
                <cit:number>
                    <gco:CharacterString><xsl:value-of select="."/></gco:CharacterString>
                </cit:number>
                <cit:numberType>
                    <cit:CI_TelephoneTypeCode codeList="https://standards.iso.org/iso/19115/resources/Codelists/cat/codelists.xml#CI_TelephoneTypeCode" codeListValue="fax"/>
                </cit:numberType>
            </cit:CI_Telephone>
        </cit:phone>
    </xsl:template>

    <xsl:template match="ro:addressPart" mode="deliveryPoint">
        <cit:deliveryPoint>
            <gco:CharacterString><xsl:value-of select="."/></gco:CharacterString>
        </cit:deliveryPoint>
    </xsl:template>
    
    <xsl:template match="ro:addressPart" mode="partyName">
        <xsl:value-of select="."/>
    </xsl:template>

    


    <xsl:template match="ro:electronic[@type = 'email']">
        <cit:electronicMailAddress>
            <gco:CharacterString><xsl:value-of select="."/></gco:CharacterString>
        </cit:electronicMailAddress>
    </xsl:template>

    <xsl:template match="ro:spatial">
        <xsl:variable name="lowText" select="translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')"/>
        <mri:extent>
            <gex:EX_Extent>
                <gex:geographicElement>
                    <gex:EX_GeographicBoundingBox>
                        <gex:westBoundLongitude>
                            <gco:Decimal><xsl:value-of select="substring-before(substring-after($lowText, 'westlimit='), ';')"/></gco:Decimal>
                        </gex:westBoundLongitude>
                        <gex:eastBoundLongitude>
                            <gco:Decimal><xsl:value-of select="substring-before(substring-after($lowText, 'eastlimit='), ';')"/></gco:Decimal>
                        </gex:eastBoundLongitude>
                        <gex:southBoundLatitude>
                            <gco:Decimal><xsl:value-of select="substring-before(substring-after($lowText, 'southlimit='), ';')"/></gco:Decimal>
                        </gex:southBoundLatitude>
                        <gex:northBoundLatitude>
                            <gco:Decimal><xsl:value-of select="substring-before(substring-after($lowText, 'northlimit='), ';')"/></gco:Decimal>
                        </gex:northBoundLatitude>
                    </gex:EX_GeographicBoundingBox>
                </gex:geographicElement>
            </gex:EX_Extent>
        </mri:extent>
    </xsl:template>


    <xsl:template match="ro:name">
        <gco:CharacterString><xsl:apply-templates select="ro:namePart"/></gco:CharacterString>
    </xsl:template>

    <xsl:template match="ro:namePart">
        <xsl:value-of select="text()"/>
    </xsl:template>
    
    
    <xsl:template match="ro:rights">
        <mri:resourceConstraints>
            <mco:MD_Constraints><mco:useLimitation><gco:CharacterString><xsl:value-of select="."/></gco:CharacterString></mco:useLimitation></mco:MD_Constraints>
        </mri:resourceConstraints> 
    </xsl:template>

    <xsl:template match="ro:description">
        <mri:abstract>
            <gco:CharacterString><xsl:value-of select="text()"/></gco:CharacterString>
        </mri:abstract>
    </xsl:template>

    <xsl:template match="ro:subject">
        <mri:keyword>
            <gco:CharacterString><xsl:value-of select="text()"/></gco:CharacterString>
        </mri:keyword>
    </xsl:template>

    <xsl:template match="ro:relatedInfo">
        <srv:operatesOn>
            <xsl:attribute name="uuidref">
                <xsl:apply-templates select="ro:identifier" mode="justValue"/>
            </xsl:attribute>
            <xsl:attribute name="xlink:href">
                <xsl:apply-templates select="ro:identifier" mode="getByIdRef"/>
            </xsl:attribute>
        </srv:operatesOn>
    </xsl:template>

    <xsl:template match="ro:identifier" mode="justValue">
        <xsl:value-of select="text()"/>
    </xsl:template>

    <xsl:template match="ro:identifier" mode="getByIdRef">
        <xsl:value-of select="text()"/>
    </xsl:template>

    <xsl:template match="ro:identifier">
        <mdb:metadataIdentifier>
            <mcc:MD_Identifier>
                <mcc:authority>
                    <cit:CI_Citation>
                        <cit:title>
                            <gco:CharacterString>GeoNetwork UUID</gco:CharacterString>
                        </cit:title>
                    </cit:CI_Citation>
                </mcc:authority>
                <mcc:code>
                    <gco:CharacterString><xsl:value-of select="text()"/></gco:CharacterString>
                </mcc:code>
                <mcc:codeSpace>
                    <gco:CharacterString>urn:uuid</gco:CharacterString>
                </mcc:codeSpace>
            </mcc:MD_Identifier>
        </mdb:metadataIdentifier>
    </xsl:template>

    <xsl:template name="addScope">
        <xsl:param name="scope" select="'collection'"/>
        <xsl:element name="mdb:metadataScope">
            <xsl:element name="mdb:MD_MetadataScope">
                <xsl:element name="mdb:resourceScope">
                    <xsl:element name="mcc:MD_ScopeCode">
                        <xsl:attribute name="codeList">codeListLocation#MD_ScopeCode</xsl:attribute>
                        <xsl:attribute name="codeListValue"><xsl:value-of select="$scope"/></xsl:attribute>
                    </xsl:element>
                </xsl:element>
                <xsl:element name="mdb:name">
                    <xsl:element name="gco:CharacterString"><xsl:value-of select= "$scope"/></xsl:element>
                </xsl:element>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template name="addMetadataStandard">
        <xsl:element name="mdb:metadataStandard">
            <xsl:element name="cit:CI_Citation">
                <xsl:element name="cit:title">
                    <xsl:element name="gco:CharacterString">ISO 19115-3 (Draft Schemas 2015)</xsl:element>
                </xsl:element>
                <xsl:element name="cit:editionDate">
                    <xsl:element name="gco:DateTime">2015-07-01T00:00:00</xsl:element>
                </xsl:element>
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="node()"/>

</xsl:stylesheet>
