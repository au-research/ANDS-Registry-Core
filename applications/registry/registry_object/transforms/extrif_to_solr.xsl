<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="ro extRif">

    <xsl:output omit-xml-declaration="yes" indent="yes" />
    <xsl:strip-space elements="*"/>
    <xsl:output method="xml" encoding="UTF-8" />
    <xsl:param name="recordCreatedDate" />
    <xsl:param name="recordUpdatedDate" />
    <xsl:param name="boost" select="1" />

<xsl:template match="/">
    <xsl:apply-templates/>
</xsl:template>   
    
 <xsl:template match="ro:registryObjects">
    <xsl:apply-templates select="ro:registryObject"/>
 </xsl:template> 

    <xsl:template match="ro:registryObject">
    

        <doc boost="{$boost}">
        <xsl:variable name="roKey">
            <xsl:apply-templates select="ro:key"/>
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="extRif:extendedMetadata">
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:slug"/>
                <xsl:element name="field">
                    <xsl:attribute name="name">key</xsl:attribute>
                    <xsl:value-of select="ro:key"/>
                </xsl:element>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceKey"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:status"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:logo"/>
                <xsl:apply-templates select="ro:originatingSource"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:id"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceID"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:contributor"/>

                <xsl:element name="field">
                    <xsl:attribute name="name">record_created_timestamp</xsl:attribute>
                    <xsl:value-of select="$recordCreatedDate"/>
                </xsl:element> 
                <xsl:element name="field">
                    <xsl:attribute name="name">record_modified_timestamp</xsl:attribute>
                    <xsl:value-of select="$recordUpdatedDate"/>
                </xsl:element>    
                <!--xsl:apply-templates select="extRif:extendedMetadata/extRif:updateTimestamp"/-->

                <xsl:element name="field">
                    <xsl:attribute name="name">group</xsl:attribute>
                    <xsl:value-of select="@group"/>
                </xsl:element>

                <!--xsl:apply-templates select="extRif:extendedMetadata/extRif:reverseLinks"/> 
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:searchBaseScore"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:registryDateModified"/-->

 
                <!--xsl:apply-templates select="extRif:extendedMetadata/extRif:dataSourceKeyHash"/--> 
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:displayTitle"/> 
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:listTitle"/> 
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:simplifiedTitle"/> 

                <xsl:apply-templates select="extRif:extendedMetadata/extRif:right[@licence_group!='']" mode="licence_group"/>        
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:description" mode="value"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:description" mode="type"/>
                <!--xsl:apply-templates select="extRif:extendedMetadata/extRif:flag"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:warning_count"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:error_count"/>
                
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:manually_assessed_flag"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:gold_status_flag"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:quality_level"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:feedType"/>   
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:lastModifiedBy"/-->  
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:spatialGeometry"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:temporal"/>

                <xsl:apply-templates select="extRif:extendedMetadata/extRif:subjects/extRif:subject"/>

                <xsl:apply-templates select="extRif:extendedMetadata/extRif:related_object"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:matching_identifier_count"/>
                <xsl:apply-templates select="extRif:extendedMetadata/extRif:annotations/extRif:tags/extRif:tag"/>

            </xsl:when>
        </xsl:choose>  
        <xsl:apply-templates select="ro:collection | ro:party | ro:activity | ro:service"/>

        </doc>
    </xsl:template> 
   
    <xsl:template match="ro:key">
        <xsl:element name="field">
            <xsl:attribute name="name">key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:slug">
        <xsl:element name="field">
            <xsl:attribute name="name">slug</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    
    <xsl:template match="extRif:id">
        <xsl:element name="field">
            <xsl:attribute name="name">id</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:dataSourceID">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_id</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:contributor">
        <xsl:element name="field">
            <xsl:attribute name="name">contributor_page</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:updateTimestamp">
        <xsl:element name="field">
            <xsl:attribute name="name">update_timestamp</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>    

    <xsl:template match="extRif:flag">
        <xsl:element name="field">
            <xsl:attribute name="name">flag</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:warning_count">
        <xsl:element name="field">
            <xsl:attribute name="name">warning_count</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:error_count">
        <xsl:element name="field">
            <xsl:attribute name="name">error_count</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:url_slug">
        <xsl:element name="field">
            <xsl:attribute name="name">url_slug</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:manually_assessed_flag">
        <xsl:element name="field">
            <xsl:attribute name="name">manually_assessed_flag</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:gold_status_flag">
        <xsl:element name="field">
            <xsl:attribute name="name">gold_status_flag</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:quality_level">
        <xsl:element name="field">
            <xsl:attribute name="name">quality_level</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = ''">0</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:feedType">
        <xsl:element name="field">
            <xsl:attribute name="name">feed_type</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:lastModifiedBy">
        <xsl:element name="field">
            <xsl:attribute name="name">last_modified_by</xsl:attribute>
            <xsl:choose>
                <xsl:when test=". = 'SYSTEM'">Harvester</xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:status">
        <xsl:element name="field">
            <xsl:attribute name="name">status</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:logo">
        <xsl:element name="field">
            <xsl:attribute name="name">logo</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:matching_identifier_count">
        <xsl:element name="field">
            <xsl:attribute name="name">matching_identifier_count</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:searchBaseScore">
        <xsl:element name="field">
            <xsl:attribute name="name">search_base_score</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>


    <xsl:template match="extRif:registryDateModified">
        <xsl:element name="field">
            <xsl:attribute name="name">date_modified</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:reverseLinks">
        <xsl:element name="field">
            <xsl:attribute name="name">reverse_links</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:originatingSource">
        <xsl:element name="field">
            <xsl:attribute name="name">originating_source</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:dataSourceKey">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>   
   
    <xsl:template match="extRif:dataSourceKeyHash">
        <xsl:element name="field">
            <xsl:attribute name="name">data_source_key_hash</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:displayTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">display_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:tag">
        <xsl:element name="field">
            <xsl:attribute name="name">tag</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
        <xsl:element name="field">
            <xsl:attribute name="name">tag_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:listTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">list_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:simplifiedTitle">
        <xsl:element name="field">
            <xsl:attribute name="name">simplified_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
   
    <xsl:template match="ro:collection | ro:party | ro:activity | ro:service"> 
        <xsl:element name="field">
            <xsl:attribute name="name">class</xsl:attribute>
            <xsl:value-of select="local-name()"/>
        </xsl:element>  
        <xsl:element name="field">
            <xsl:attribute name="name">type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>  
        <xsl:element name="field">
            <xsl:attribute name="name">description</xsl:attribute>
            <xsl:choose>
                <xsl:when test="//extRif:description[@type='brief']">
                    <xsl:value-of select="//extRif:description[@type='brief'][1]/text()"/>
                </xsl:when>
                <xsl:when test="//extRif:description[@type = 'full']">
                    <xsl:value-of select="//extRif:description[@type = 'full'][1]/text()"/>
                </xsl:when>
                <xsl:when test="//extRif:description">
                    <xsl:value-of select="//extRif:description[1]/text()"/>
                </xsl:when>
            </xsl:choose>
        </xsl:element>  
        <xsl:apply-templates select="ro:identifier" mode="value"/>
        <xsl:apply-templates select="ro:identifier" mode="type"/>
        <!--<xsl:apply-templates select="ro:name"/>
        
        <xsl:apply-templates select="ro:displayTitle"/>
        <xsl:apply-templates select="ro:listTitle"/>
        
        <xsl:apply-templates select="ro:location"/>
        <xsl:apply-templates select="ro:coverage"/>
        
        <xsl:apply-templates select="ro:relatedObject"/-->
        <!--xsl:apply-templates select="ro:relatedInfo"/-->
    </xsl:template>
    
    <xsl:template match="ro:location">
        <xsl:element name="field">
            <xsl:attribute name="name">location</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo">
    <xsl:element name="field">
        <xsl:attribute name="name">related_info</xsl:attribute>
        <xsl:apply-templates/>
    </xsl:element>       
    </xsl:template>
    
    <xsl:template match="ro:relatedInfo/*">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template>
    

    <xsl:template match="extRif:related_object">
            <xsl:apply-templates/>       
    </xsl:template> 
    
<!-- these are the relatedInfo relations that could not be resolved internally -->
    <xsl:template match="extRif:related_object[extRif:related_object_id = '']"/>


    <xsl:template match="extRif:related_object_key">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>



    <xsl:template match="extRif:related_object_key">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_key</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>

    <xsl:template match="extRif:related_object_id">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_id</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:related_object_class">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_class</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:related_object_type">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_type</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:related_object_display_title">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_display_title</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
        
    <xsl:template match="extRif:related_object_relation">
        <xsl:element name="field">
            <xsl:attribute name="name">related_object_relation</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>  
    </xsl:template>

    
    <!--xsl:template match="ro:coverage/ro:temporal/extRif:date[@type = 'dateFrom'] | ro:coverage/ro:temporal/extRif:date[@type = 'dateTo']">

            <xsl:element name="field">              

                <xsl:if test="@type = 'dateFrom'">
                    <xsl:attribute name="name">date_from</xsl:attribute>
                </xsl:if>
                <xsl:if test="@type = 'dateTo'">
                    <xsl:attribute name="name">date_to</xsl:attribute>
                </xsl:if>
                <xsl:value-of select="."/>           
            </xsl:element>     

    </xsl:template-->
    
    <!--xsl:template match="ro:address | ro:electronic | ro:physical | ro:coverage | ro:temporal | extRif:spatial | extRif:subjects">
            <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template match="ro:electronic/ro:value | ro:addressPart | ro:location/ro:spatial[@type = 'text']">
            <xsl:value-of select="."/><xsl:text> </xsl:text>
    </xsl:template-->
    
    <xsl:template match="ro:identifier" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="ro:identifier" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">identifier_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>       
    </xsl:template>
      
    
    <xsl:template match="extRif:subject_value">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_value_unresolved</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:subject_resolved">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_value_resolved</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
       
    <xsl:template match="extRif:subject_type">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_type</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:subject_uri">
        <xsl:element name="field">
            <xsl:attribute name="name">subject_vocab_uri</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>


    <xsl:template match="extRif:description" mode="value">
        <xsl:element name="field">
            <xsl:attribute name="name">description_value</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>       
    </xsl:template>
    
    <xsl:template match="extRif:description" mode="type">
        <xsl:element name="field">
            <xsl:attribute name="name">description_type</xsl:attribute>
            <xsl:value-of select="@type"/>
        </xsl:element>
    </xsl:template>
 
    <xsl:template match="extRif:spatialGeometry">
        <xsl:apply-templates/>     
    </xsl:template>

    <xsl:template match="extRif:extent">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_extents</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:polygon">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_polygons</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="extRif:center">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_centres</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:area">
        <xsl:element name="field">
            <xsl:attribute name="name">spatial_coverage_area_sum</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:temporal">
        <xsl:apply-templates/>     
    </xsl:template>

    <xsl:template match="extRif:temporal_date_from">
        <xsl:element name="field">
            <xsl:attribute name="name">date_from</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:temporal_date_to">
        <xsl:element name="field">
            <xsl:attribute name="name">date_to</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:temporal_earliest_year">
        <xsl:element name="field">
            <xsl:attribute name="name">earliest_year</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="extRif:temporal_latest_year">
        <xsl:element name="field">
            <xsl:attribute name="name">latest_year</xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
    </xsl:template>
    
     <xsl:template match="extRif:right[@licence_group!='']" mode="licence_group">
        <xsl:element name="field">
            <xsl:attribute name="name">license_class</xsl:attribute>
            <xsl:value-of select="@licence_group"/>
        </xsl:element>
    </xsl:template> 


    <xsl:template match="ro:date | ro:description | ro:spatial | ro:text | ro:subject | ro:relatedObject"/>

    
    <xsl:template match="ro:name"/>
        
   
</xsl:stylesheet>

