<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects" exclude-result-prefixes="ro extRif">
    <xsl:output method="html" encoding="UTF-8" indent="no" omit-xml-declaration="yes"/>
    <xsl:include href="annotations/default.xsl"/>
    <xsl:strip-space elements="*"/>
    <xsl:param name="dataSource" select="//extRif:extendedMetadata/extRif:dataSourceKey"/>
    <xsl:param name="dateCreated"/>
    <xsl:param name="orca_view"/>  
    <xsl:param name="theGroup"/>
    <xsl:param name="key"/>        
    <xsl:variable name="objectClass" >
        <xsl:choose>
            <xsl:when test="//ro:collection">Collection</xsl:when>
            <xsl:when test="//ro:activity">Activity</xsl:when>
            <xsl:when test="//ro:party">Party</xsl:when>
            <xsl:when test="//ro:service">Service</xsl:when>            
        </xsl:choose>       
    </xsl:variable>
    <xsl:variable name="objectClassType" >
      <xsl:choose>
         <xsl:when test="//ro:collection">collections</xsl:when>
         <xsl:when test="//ro:activity">activities</xsl:when>
         <xsl:when test="//ro:party/@type='group'">party_multi</xsl:when>
         <xsl:when test="//ro:party/@type='person'">party_one</xsl:when>		
         <xsl:when test="//ro:party">party_multi</xsl:when>	
         <xsl:when test="//ro:service">services</xsl:when>	
     </xsl:choose>		
 </xsl:variable>		 

 <xsl:template match="ro:registryObject">
    <!--  We will first set up the breadcrumb menu for the page -->   
    <span id="originating_source" class="hide"><xsl:value-of select="$dataSource"/></span>

    <xsl:variable name="group">	
      <xsl:choose>
          <xsl:when test="string-length(./@group)>30">
              <xsl:value-of select="substring(./@group,0,30)"/>...
          </xsl:when>
          <xsl:otherwise>
              <xsl:value-of select="./@group"/>
          </xsl:otherwise>
      </xsl:choose>
  </xsl:variable>	


  <div class="breadcrumb">
     <a href="{$base_url}">Home </a>
         <xsl:choose>
        <xsl:when test="//extRif:extendedMetadata/extRif:contributor">
          <xsl:text>  /  </xsl:text>
            <a class="crumb group_crumb">
              <xsl:attribute name="href">
                <xsl:value-of select="$base_url"/><xsl:value-of select="//extRif:extendedMetadata/extRif:contributor"/>
              </xsl:attribute>
              <xsl:value-of select="$group"/>
            </a>    
            <img src="{$base_url}assets/core/images/caret.png" alt="" class="linked_records hide"/>
         </xsl:when> 
         <xsl:otherwise>
           <xsl:text>  /  </xsl:text>
            <a href="{$base_url}search/#!/group={./@group}" class="crumb group_crumb"><xsl:value-of select="$group"/></a>    
            <img src="{$base_url}assets/core/images/caret.png" alt="" class="linked_records hide"/>
         </xsl:otherwise>
       </xsl:choose>
    <xsl:text>  /  </xsl:text>
    <a>
      <xsl:attribute name="href">
        <xsl:value-of select="$base_url"/>search/#!/group=<xsl:value-of select="./@group"/>/tab=<xsl:value-of select="translate($objectClass,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')"/>
      </xsl:attribute>
      <xsl:attribute name="class">crumb</xsl:attribute>
      <xsl:value-of select="$objectClass"/>
    </a>

    <!--li><xsl:value-of select="$theTitle"/></li-->


				<!--div id="breadcrumb-corner">
				    
				

	      
	       			 <div class="a2a_kit a2a_default_style no_print" id="share">
	        		<a class="a2a_dd" href="http://www.addtoany.com/share_save">Share</a>
	        		<span class="a2a_divider"></span>
	       			 <a class="a2a_button_linkedin"></a>
	        		<a class="a2a_button_facebook"></a>
	        		<a class="a2a_button_twitter"></a>
	        			        		<span class="a2a_divider"></span>
	        		
	        		</div>
	        		<script type="text/javascript">
	        		var a2a_config = a2a_config || {};
	        		</script>
	        		<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
	      
	        	
   			      <a id="tag_show">

                    <xsl:attribute name="href">javascript:void(0);</xsl:attribute>                    
                    <img id="tag_icon">
                    <xsl:attribute name="src">
                    <xsl:value-of select="$base_url"/>
                    <xsl:text>img/</xsl:text>
                    <xsl:text>tag_16_icon.png</xsl:text></xsl:attribute>
                    <xsl:attribute name="alt">Tag Icon</xsl:attribute>
                    </img>

                    </a>	 

                   
					<a target="_blank">
                    <xsl:attribute name="href"><xsl:value-of select="$base_url"/>view/printview/?key=<xsl:value-of select="ro:key"/></xsl:attribute>                    
                    <img id="print_icon">
                    <xsl:attribute name="src">
                    <xsl:value-of select="$base_url"/>
                    <xsl:text>img/</xsl:text>
                    <xsl:text>1313027722_print.png</xsl:text></xsl:attribute>
                    <xsl:attribute name="alt">Print Icon</xsl:attribute>
                    </img>
                    </a>
                </div-->

            </div>	

            <!--  the following hidden elements dfine content to be used in further ajax calls -->
            <div id="registryObjectMetadata" class="hide">
                <div id="group_value"><xsl:value-of select="@group"/></div>
                <div id="datasource_key"><xsl:value-of select="$dataSource"/></div>
                <div id="key_value"></div>
                <div id="class"><xsl:value-of select="$objectClass"/></div>       
                <span id="key"><xsl:value-of select="ro:key"/></span>
                <span id="status"><xsl:value-of select="//extRif:extendedMetadata/extRif:status"/></span>
                <span id="slug"><xsl:value-of select="//extRif:extendedMetadata/extRif:slug"/></span>
                <span id="registry_object_id"><xsl:value-of select="//extRif:extendedMetadata/extRif:id"/></span>
                <span id="class_type"><xsl:value-of select="$objectClassType"/></span>
                <span id="matching_identifier_count"><xsl:value-of select="//extRif:extendedMetadata/extRif:matching_identifier_count"/></span>
            </div>

            <xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
            
        </xsl:template>

        <xsl:template match="ro:collection | ro:activity | ro:party | ro:service">
            <div class="main" id="item-view-inner" itemscope="" itemType="http://schema.org/Thing">

	 	<!--div id="tag_view" class="hide">
	 	<span id="tag_lists">
			<xsl:for-each select="//extRif:tags/extRif:tag">
				<xsl:if test="position()&gt;1">	| </xsl:if>
		 		<span class="tag_text"> <xsl:value-of select="."/></span>
			</xsl:for-each>
			</span>
		<div id="add_tag_button"><input type="button" id="tag_add" value="Add Tag"/></div> 
			<div id="add_tag_form">
			<div id='tag_close'> <br /></div>
				<p><input type="text" name="new_tag" id="new_tag" value=""/><br />
				<span id='tagError' class='tagFormError'><br /></span></p>
				 <div id="captcha_id"></div>
				<p><input type="button" id="tag_submit" name="tag_submit" value="Submit"/></p>
			</div>
		</div-->    
  <div class="page_title" id="displaytitle">

     <xsl:choose>

       <xsl:when test="../extRif:extendedMetadata/extRif:displayTitle!=''">
          <xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayTitle"/>	
      </xsl:when>
      <xsl:otherwise>

      

            <h1 itemprop="name"><xsl:value-of select="../ro:key"/></h1>

            <xsl:for-each select="//ro:existenceDates">
              <xsl:choose>
                <xsl:when test="extRif:friendly_date">
                  <xsl:value-of select="extRif:friendly_date"/><br/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if>
                  -
                  <xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if><br/>
                </xsl:otherwise>
              </xsl:choose>
          </xsl:for-each>
          <div class="right_icon">
            <img class="icon-heading">
               <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
               <xsl:text>assets/core/images/</xsl:text>
               <xsl:value-of select="$objectClassType"/>
               <xsl:text>.png</xsl:text>
           </xsl:attribute>
           <xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
          </img>

          </div> 

        </xsl:otherwise> 

        </xsl:choose>   

        <!-- DISPLAY ALTERNATE TITLES/NAMES -->
        <xsl:apply-templates select="ro:name[@type='alternative' and ro:namePart/text() != '']"/>

        <xsl:apply-templates select="ro:name[@type='abbreviated' and ro:namePart/text() != '']"/>

        <!-- DISPLAY LOGO -->
        <xsl:apply-templates select="../extRif:extendedMetadata/extRif:displayLogo"/>
    </div> 


<div class="post clear">

    <!-- DISPLAY DESCRIPTIONS -->
    <xsl:if test="ro:description[text() != '']">

        <div class="descriptions" style="position:relative;clear:both;">
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'brief' and text() != '']" mode="content"/>
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'full' and text() != '']" mode="content"/>
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'significanceStatement' and text() != '']" mode="content"/>       
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[@type= 'notes' and text() != '']" mode="content"/>   
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:description[not(@type =  'notes' or @type =  'significanceStatement' or @type =  'full' or @type =  'brief' or @type =  'logo' or @type =  'rights' or @type =  'accessRights') and text() != '']" mode="content"/>
        </div>
        

    </xsl:if>

        <!-- HIERARCHY CHART (NEW) -->
    <div class="hide" style="clear:both;" id="collectionStructureWrapper">
      <h4>Browse nested collections <a href="#" class="hide collectionNote"><img src="{$base_url}/assets/core/images/question_mark.png" style="width:14px;position:relative;top:-8px"/></a></h4>
      <div id="collectionStructureQtip" class="hide">
        <p>Closely related collections to this collection (they have a parent-child relationship) are displayed in a browsable tree structure to provide contextual information for this collection and to facilitate discovery. Browse the related collections by expanding each tree node. Access a related collection of interest by clicking on the hyperlink.</p>
        <p>Please note that only collections using the reciprocal RIF-CS relationship type "isPartOf/hasPart" are listed here. Use the Collections Connections box on the right to access all related collections.</p>
      </div>
      <div id="connectionTree"></div>
      <!--p><i>This record is part of a structured collection.</i></p-->
      <p></p>
    </div>
    
<!-- DISPLAY CITATION INFORMATION -->
    <xsl:choose>
      <xsl:when test="ro:citationInfo">
        <div id="citation" style="position:relative;clear:both;">
            <xsl:choose>
                <xsl:when test="ro:citationInfo/ro:citationMetadata[descendant::text() != '']">
                    <p><xsl:text>&amp;nbsp;</xsl:text></p>
                    <h4>How to Cite this Collection</h4>
                       <!--   <a title="Add this article to your Mendeley library" target="_blank">
                       <xsl:attribute name="href">
                        http://www.mendeley.com/import/?url=<xsl:value-of select="ro:citationInfo/ro:citationMetadata/ro:url"/>
                        </xsl:attribute> 
                        <img src="http://www.mendeley.com/graphics/mendeley.png"/></a> -->
                        <h5>Citation (Metadata):</h5>
                        <div class="citationDisplay">
                          <xsl:apply-templates select="ro:citationInfo/ro:citationMetadata"/> 
                        </div>
                    </xsl:when>
                    <xsl:when test="ro:citationInfo/ro:fullCitation[text() != '']">
                        <p><xsl:text>&amp;nbsp;</xsl:text></p>
                        <h4>How to Cite this Collection</h4>
                        <h5>Full Citation:</h5>
                        <div class="citationDisplay">
                          <xsl:apply-templates select="ro:citationInfo/ro:fullCitation"/>
                        </div>
                    </xsl:when>
                    <xsl:otherwise >
                        <!-- If we have found an empty citation element build the openURL using the object display title -->
                        <span class="Z3988">    
                            <xsl:attribute name="title">
                                <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
                                <xsl:text>&amp;amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
                                <xsl:text>&amp;amp;rfr_id=info%3Asid%2FANDS</xsl:text>
                                <xsl:text>&amp;amp;rft.title=</xsl:text><xsl:value-of select="//extRif:displayTitle"/>
                                <xsl:text>&amp;amp;rft.description=</xsl:text><xsl:value-of select="//extRif:displayTitle"/>
                            </xsl:attribute>
                        </span><span class="Z3988"></span>      
                    </xsl:otherwise>                        
                </xsl:choose>   
            </div>          
        </xsl:when>
    </xsl:choose>
    
    <xsl:if test="ro:identifier[text() != '']">
      <p></p>
      <h4>Identifiers</h4>
      <div id="identifiers">
        <xsl:for-each select="ro:identifier[text() != '']">
          <p>
            <xsl:apply-templates select="current()[@type='doi']" mode="doi_prefixedLink"/>
            <xsl:apply-templates select="current()[@type='ark']" mode="ark_prefixedLink"/>      
            <xsl:apply-templates select="current()[@type='AU-ANL:PEAU']" mode="nla_prefixedLink"/>  
            <xsl:apply-templates select="current()[@type='handle']" mode="handle_prefixedLink"/>   
            <xsl:apply-templates select="current()[@type='purl']" mode="purl_prefixedLink"/>
            <xsl:apply-templates select="current()[@type='uri']" mode="uri_prefixedLink"/> 
            <xsl:apply-templates select="current()[@type='orcid']" mode = "orcid_prefixedLink"/>
            <xsl:apply-templates select="current()[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri' or @type = 'orcid')]" mode="other_prefixedLink"/> 
          </p>
        </xsl:for-each>
      </div>
    </xsl:if>

    <!-- DISPLAY RELATED INFO -->
    <xsl:if test="ro:relatedInfo[@type='publication' and ro:identifier/text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Related Publications</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type='publication' and ro:identifier/text() != '']"/> 
    </xsl:if>
    <xsl:if test="ro:relatedInfo[@type='website' and ro:identifier/text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Related Websites</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type='website' and ro:identifier/text() != '']"/> 
    </xsl:if>
    <xsl:if test="ro:relatedInfo[@type='reuseInformation' and ro:identifier/text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Reuse Information</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type='reuseInformation' and ro:identifier/text() != '']"/> 
    </xsl:if>
    <xsl:if test="ro:relatedInfo[@type='dataQualityInformation' and ro:identifier/text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Data Quailty Information</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type='dataQualityInformation' and ro:identifier/text() != '']"/> 
    </xsl:if>
    <xsl:if test="ro:relatedInfo[@type='metadata' and ro:identifier/text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Additional Metadata</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type='metadata' and ro:identifier/text() != '']"/> 
    </xsl:if>
    <xsl:if test="ro:relatedInfo[@type !='metadata' and @type!='dataQualityInformation' and @type!='reuseInformation' and @type!='website' and @type!='publication' and @type!='party' and @type!='collection' and @type!='service' and @type!='activity'] or ro:relatedInfo[(@type='party' or @type='collection' or @type='service' or @type='activity') and (not(ro:title) or ro:title/text() = '') and (not(ro:identifier/@resolved))]">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>More Information</h4>
        <xsl:apply-templates select="ro:relatedInfo[@type !='metadata' and @type!='dataQualityInformation' and @type!='reuseInformation' and @type!='website' and @type!='publication' and @type!='party' and @type!='collection' and @type!='service' and @type!='activity']"/>
        <xsl:apply-templates select="ro:relatedInfo[(@type='party' or @type='collection' or @type='service' or @type='activity') and (not(ro:title) or ro:title/text() = '') and (not(ro:identifier/@resolved))]"/>
    </xsl:if>

    
    <!-- DISPLAY COVERAGE (SPATIAL AND TEMPORAL) -->            
    <xsl:if test="ro:coverage/ro:spatial[descendant::text() != ''] or ro:location/ro:spatial[descendant::text() != '']">
        <xsl:variable name="coverageLabel">
            <xsl:choose>
                <xsl:when test="(ro:coverage/ro:spatial or ro:location[@type='coverage']) and ro:location/ro:spatial">
                    <xsl:text>Spatial Coverage And Location:</xsl:text>
                </xsl:when>
                <xsl:when test="ro:location/ro:spatial">
                    <xsl:text>Location:</xsl:text>
                </xsl:when>
                <xsl:when test="ro:coverage/ro:spatial[descendant::text() != '']">
                    <xsl:text>Spatial Coverage:</xsl:text>
                </xsl:when>
                
            </xsl:choose>
        </xsl:variable>
          

          <p>&amp;nbsp;</p>
          <h4><xsl:value-of select="$coverageLabel"/></h4>

            <xsl:if test="../extRif:extendedMetadata/extRif:spatialGeometry/extRif:polygon">          
              <div id="spatial_coverage_map"></div>
            </xsl:if>
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:spatialGeometry/extRif:polygon"/>   
            <xsl:apply-templates select="../extRif:extendedMetadata/extRif:spatialGeometry/extRif:center"/>

            
            <xsl:for-each select="ro:coverage/ro:spatial[@type!='iso19139dcmiBox' and @type!='gmlKmlPolyCoords' and @type!='kmlPolyCoords']">
              <p class="coverage_text"><xsl:value-of select="./@type"/>: <xsl:value-of select="."/></p>
            </xsl:for-each>

            <xsl:for-each select="ro:location/ro:spatial[@type!='iso19139dcmiBox' and @type!='gmlKmlPolyCoords' and @type!='kmlPolyCoords']">
              <p class="coverage_text"><xsl:value-of select="./@type"/>: <xsl:value-of select="."/></p>
            </xsl:for-each>          

      </xsl:if>

      <xsl:if test="ro:coverage/ro:temporal or ro:location[@dateFrom!=''] or ro:location[@dateTo!='']">

        <xsl:if test="ro:coverage/ro:temporal/ro:date | ro:location[@dateFrom!=''] | ro:location[@datefrom!=''] | ro:location[@dateTo!=''] | ro:coverage/ro:temporal/ro:text">
            <p>&amp;nbsp;</p><h4>Temporal Coverage:</h4>
        </xsl:if>

        <xsl:if test="ro:coverage/ro:temporal/ro:date">
          <xsl:apply-templates select="ro:coverage/ro:temporal[ro:date]" mode="date" />
        </xsl:if>
        
        <xsl:if test="ro:location[@datefrom!=''] | ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">
           <xsl:apply-templates select="ro:location[@datefrom!=''] | ro:location[@dateFrom!=''] | ro:location[@dateTo!='']"/>   
       </xsl:if>           

       <xsl:if test="ro:coverage/ro:temporal/ro:text">
          <xsl:apply-templates select="ro:coverage/ro:temporal/ro:text"/> 
      </xsl:if> 

    </xsl:if>

  <!-- DISPLAY SUBJECTS -->
  <xsl:if test="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']">
    <div style="position:relative;clear:both">
        <!--<p><b>Subjects:</b>-->
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Subjects</h4>

        <!-- ANZSRC SUBJECTS -->
        <xsl:if test="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']/extRif:subject_type ='anzsrc-for'">
            <p class="subject_type">Field of Research</p>
            <div class="tags">
                <xsl:for-each select="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']">      
                    <xsl:sort select="extRif:subject_type"/>
                    <xsl:if test="extRif:subject_type='anzsrc-for'">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
            </div>
        </xsl:if>

        <xsl:if test="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']/extRif:subject_type ='anzsrc-seo'">
            <p class="subject_type">Socio-economic Objective</p>
            <div class="tags">
                <xsl:for-each select="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']">      
                    <xsl:sort select="extRif:subject_type"/>
                    <xsl:if test="extRif:subject_type='anzsrc-seo'">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
            </div>
        </xsl:if>

        <!-- OTHER SUBJECTS -->
        <xsl:if test="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']/extRif:subject_type!='anzsrc-for' and ../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']/extRif:subject_type!='anzsrc-seo'">
            <p class="subject_type">Keywords</p> 
            <div class="tags">
                <xsl:for-each select="../extRif:extendedMetadata/extRif:subjects/extRif:subject[extRif:subject_value/text() != '']">      
                    <xsl:sort select="extRif:subject_type"/>
                    <xsl:if test="extRif:subject_type!='anzsrc-for' and extRif:subject_type!='anzsrc-seo'">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
            </div>
        </xsl:if> 
        
 
    

    </div>  


</xsl:if>

 <xsl:if test="../extRif:annotations/extRif:tags">
    <p class="subject_type">User Contributed Tags <a href="#" class="tags_helper"><i class="portal-icon portal-icon-info"></i></a></p>
    <div class="tags user_tags" id="tags_container">
      <xsl:for-each select="../extRif:annotations/extRif:tags/extRif:tag">
        <xsl:apply-templates select="."/>
      </xsl:for-each>
    </div>
</xsl:if>
%%%%ADDTAGFORM%%%%

    <!-- DISPLAY DATES -->
    <xsl:if test="ro:dates[descendant::text() != '']">
        <p><xsl:text>&amp;nbsp;</xsl:text></p>
        <h4>Dates</h4>
          <xsl:apply-templates select="ro:dates[descendant::text() != '']"/>
        <p>&amp;nbsp;</p>
    </xsl:if>           
    
    <!-- DISPLAY IDENTIFIERS -->
   
        <!--div style="position:relative;clear:both;" class="no_print">
            <p> <a>
                <xsl:attribute name="href"><xsl:value-of select="$orca_view"/>?key=<xsl:value-of select="$key"/></xsl:attribute>
                View the complete record in the ANDS Collections Registry
                </a>
            </p>  
        </div-->  


    </div>
    <a href="javascript:;" class="sharing_widget">Share</a>
    <!-- AddThis Button BEGIN -->
    <div class="addthis_toolbox addthis_default_style " style="float:right;">
      <a class="addthis_button_facebook_like"></a>
      <a class="addthis_button_tweet"></a>
      <a class="addthis_counter addthis_pill_style"></a>
    </div>
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-521ea5093dcee175&amp;async=1"></script>
    <!-- AddThis Button END -->
</div>      


<!--  we will now transform the right hand stuff -->
<div class="sidebar">
	<xsl:apply-templates select="//extRif:theme_page"/>
<h3 id="draft_status" class="hide" style="color:#FF6688;">DRAFT PREVIEW</h3>

  <xsl:if test="ro:location/ro:address/ro:electronic/@type='url' 
    or ro:rights or ro:location/ro:address/ro:electronic/@type='email' or ro:location/ro:address/ro:physical">     
    <div class="right-box">
        


        <h2>Access</h2>
        <div class="limitHeight300">
            <xsl:if test="ro:location/ro:address/ro:electronic/@type='url'">
                <p><xsl:apply-templates select="ro:location/ro:address/ro:electronic"/></p> 
            </xsl:if>
       
          <!--  <xsl:apply-templates select="ro:description[@type = 'accessRights' or @type = 'rights']"/> -->
            <!--xsl:apply-templates select="ro:rights"/-->
            <xsl:apply-templates select="//extRif:right[@type='licence']"/>
            <xsl:apply-templates select="//extRif:right[@type!='licence']"/>  

            <xsl:if test="ro:location/ro:address/ro:electronic/@type='email' or ro:location/ro:address/ro:physical">
                <h3>Contacts</h3>
                <xsl:if test="ro:location/ro:address/ro:electronic/@type='email'">
                    <p><xsl:apply-templates select="ro:location/ro:address/ro:electronic/@type"/></p>   
                </xsl:if>
                <xsl:if test="ro:location/ro:address/ro:physical">
                    <p>
                        <xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='telephoneNumber'">
                            <xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='telephoneNumber']">
                                <p>Ph:  <xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
                            </xsl:for-each>
                        </xsl:if>
                        
                        <xsl:if test="ro:location/ro:address/ro:physical/ro:addressPart/@type='faxNumber'">
                            <xsl:for-each select="ro:location/ro:address/ro:physical/ro:addressPart[@type='faxNumber']">
                                <p>Fax:<xsl:value-of select="."  disable-output-escaping="yes"></xsl:value-of></p>
                            </xsl:for-each>
                        </xsl:if>   

                        <xsl:apply-templates select="ro:location/ro:address/ro:physical"/></p>  
                    </xsl:if>       
                </xsl:if>           

            </div>
        </div>      
        <p><br/></p>            
    </xsl:if>

    <xsl:apply-templates select="../extRif:annotations"/>


    <!-- NEW CONNECTION -->
    <!--xsl:if test="//ro:relatedObject"-->
      <div class="right-box" id="connectionsRightBox">
          <div id="connectionsInfoBox" class="hide"></div>
          <div id="connections">%%%%CONNECTIONS%%%%</div>
        </div>  
    <!--/xsl:if-->




  <div id="suggestedLinksRightBox" class="right-box">
      <div id="infoBox" class="hide"></div>


      <div id="AndsSuggestedLinksBox">
      %%%%ANDS_SUGGESTED_LINKS%%%%
      </div>

      <xsl:if test="$objectClass='Collection'">
        <br/><br/>
        <div id="DataCiteSuggestedLinksBox" class="hide">
            <!--img>
                <xsl:attribute name="src"><xsl:value-of select="$base_url"/><xsl:text>assets/core/images/ajax-loader.gif</xsl:text></xsl:attribute>
                <xsl:attribute name="class">loading-icon</xsl:attribute>
                <xsl:attribute name="alt">Loading…</xsl:attribute>
            </img-->
        </div>
      </xsl:if>
  </div>

</div> 
<div class="container_clear"></div>
<div class="border"></div>

</xsl:template>

<!--  the following templates will format the view page content -->
<xsl:template match="extRif:displayTitle">   

<!-- <div class="right_icon">
   <img class="icon-heading">
     <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
     <xsl:text>assets/core/images/icons/</xsl:text>
     <xsl:value-of select="$objectClassType"/>
     <xsl:text>.png</xsl:text></xsl:attribute>
     <xsl:attribute name="alt"><xsl:value-of select="$objectClassType"/></xsl:attribute>
 </img>
</div>   --> 

      <xsl:apply-templates select="../extRif:logo"/>
      
       <h1><xsl:value-of select="." disable-output-escaping="no"/></h1>
       <xsl:for-each select="//ro:existenceDates">
         <xsl:choose>
          <xsl:when test="extRif:friendly_date">
            <xsl:value-of select="extRif:friendly_date"/><br/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:if test="./ro:startDate"><xsl:value-of select="./ro:startDate"/></xsl:if>
            -
            <xsl:if test="./ro:endDate"><xsl:value-of select="./ro:endDate"/></xsl:if><br/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each> 
</xsl:template>

<xsl:template match="extRif:displayLogo">
    <xsl:if test="extRif:displayLogo/text() != ''">
        <div>
            <img id="party_logo" style="max-width:130px;max-height:none;height:auto">
            	<xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
            	<xsl:attribute name="alt">Party Logo</xsl:attribute>
            </img>
        </div>
    </xsl:if>  
</xsl:template> 

<xsl:template match="extRif:theme_page">
  <div class="theme_page">
    <xsl:attribute name="slug"><xsl:value-of select="."/></xsl:attribute>
  </div>
</xsl:template>

<xsl:template match="ro:name[@type='alternative']">   
    <p class="alt_displayTitle">Also known as: <xsl:apply-templates/></p>
</xsl:template> 

<xsl:template match="extRif:logo">
    <!-- these inline styles aren't great, but they're everywhere. check out #party_logo in the various transforms... -->
    <img class="logo" style="max-width:130px;max-height:none;height:auto" src="{.}"/>
</xsl:template> 

<xsl:template match="ro:name[@type='abbreviated']">   
    <p class="abbrev_displayTitle">Also known as: <xsl:apply-templates/></p>
</xsl:template>

<xsl:template match="ro:namePart[text() != '']">
    <xsl:value-of select="."/><xsl:text>, </xsl:text>    
</xsl:template>

<xsl:template match="ro:title">
    <xsl:value-of select="."/>    
</xsl:template>

<xsl:template match="ro:relatedInfo/ro:notes">
    <xsl:value-of select="."/>   
</xsl:template> 


<xsl:template match="extRif:polygon">
    <p class="coverage hide"><xsl:value-of select="."/></p>
</xsl:template>   

<xsl:template match="extRif:center">
    <p class="spatial_coverage_center hide"><xsl:value-of select="."/></p>
</xsl:template>

<xsl:template match="ro:temporal/ro:date">
  <xsl:if test="./@type = 'dateFrom'">
      From 
  </xsl:if>
  <xsl:if test="./@type = 'dateTo'">
      to  
  </xsl:if>     
  <xsl:value-of select="."/>       
</xsl:template> 

<xsl:template match="ro:location[@datefrom!=''] | ro:location[@dateFrom!=''] | ro:location[@dateTo!='']">  
   <xsl:if test="./@datefrom != ''">
    From         <xsl:value-of select="./@datefrom"/>     
</xsl:if>     
<xsl:if test="./@dateFrom != ''">
    From         <xsl:value-of select="./@dateFrom"/>     
</xsl:if>
<xsl:if test="./@dateTo != ''">
    to         <xsl:value-of select="./@dateTo"/><br />   
</xsl:if>       

</xsl:template>   
<xsl:template match="ro:coverage/ro:temporal/ro:text">
   <xsl:value-of select="."/>   <br />
</xsl:template>
<xsl:template match="ro:coverage/ro:temporal/ro:date">
 <xsl:if test="./@type = 'datefrom'">
    From         <xsl:value-of select="."/>     
</xsl:if>
<xsl:if test="./@type = 'dateFrom'">
    From         <xsl:value-of select="."/>     
</xsl:if>
<xsl:if test="./@type = 'dateTo'">
    to         <xsl:value-of select="."/><br/>
</xsl:if> 
</xsl:template>

<xsl:template match="extRif:subject[extRif:subject_value/text() != '']">   
  <xsl:choose>
    <xsl:when test="extRif:subject_type = 'anzsrc-for'">
      <a href="{$base_url}search/#!/subject_vocab_uri={extRif:subject_uri}" vocab_uri="{extRif:subject_uri}" class="subject_vocab_filter" id="{extRif:subject_resolved}" title="{extRif:subject_resolved}">
        <xsl:value-of select="extRif:subject_resolved"/>
      </a>
    </xsl:when>
    <xsl:otherwise>
    <a href="{$base_url}search/#!/s_subject_value_resolved={extRif:subject_resolved}/" class="subjectFilter" id="{extRif:subject_resolved}" title="{extRif:subject_resolved}">
      <xsl:value-of select="extRif:subject_resolved"/>
    </a>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="extRif:tag">
  <xsl:choose>
    <xsl:when test="@type='public'">
      <a href="{$base_url}search/#!/tag={.}"><xsl:value-of select="."/></a>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:relatedInfo">
    <p>
      <xsl:if test="./ro:title">

        <xsl:if test="./@type='publication'">        
          <img class="publication" style="margin-top: -2px; height: 24px; width: 24px;">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/publications.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">Publication icon</xsl:attribute>
            <xsl:if test="./ro:relation">
            <xsl:attribute name="title"><xsl:value-of select="./ro:relation/@type"/></xsl:attribute>
            <xsl:attribute name="object_class"><xsl:value-of select="$objectClass"/></xsl:attribute>
          </xsl:if>
          </img>
          <xsl:text>  </xsl:text>
        </xsl:if>
        
        <xsl:value-of select="./ro:title"/><br/>
      </xsl:if>
      <xsl:apply-templates select="./ro:identifier[@type='doi']" mode = "doi_prefixedLink"/>
      <xsl:apply-templates select="./ro:identifier[@type='orcid']" mode = "orcid_prefixedLink"/>
      <xsl:apply-templates select="./ro:identifier[@type='ark']" mode = "ark_prefixedLink"/>    	
      <xsl:apply-templates select="./ro:identifier[@type='AU-ANL:PEAU']" mode = "nla_prefixedLink"/>  
      <xsl:apply-templates select="./ro:identifier[@type='handle']" mode = "handle_prefixedLink"/>   
      <xsl:apply-templates select="./ro:identifier[@type='purl']" mode = "purl_prefixedLink"/>
      <xsl:apply-templates select="./ro:identifier[@type='uri']" mode = "uri_prefixedLink"/> 
      <xsl:apply-templates select="./ro:identifier[@type='urn']" mode = "urn_prefixedLink"/> 
      <xsl:apply-templates select="./ro:identifier[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri' or @type =  'urn' or  @type='orcid')]" mode="other_prefixedLink"/>			            	
      <!--xsl:if test="./ro:format">
    
        <p> Format
       <xsl:apply-templates select="./ro:format"/>
     </p>
      </xsl:if-->
   <xsl:if test="./ro:notes">
    <p>
       <xsl:apply-templates select="./ro:notes"/>
      </p>
   </xsl:if>
</p>        
</xsl:template>

<!--xsl:template match="ro:format">
  <p>
    <xsl:apply-templates select="./ro:identifier[@type='Doi']" mode = "doi_prefixedLink"/>
    <xsl:apply-templates select="./ro:identifier[@type='Ark']" mode = "ark_prefixedLink"/>    	
    <xsl:apply-templates select="./ro:identifier[@type='AU-ANL:PEAU']" mode = "nla_prefixedLink"/>  
    <xsl:apply-templates select="./ro:identifier[@type='Handle']" mode = "handle_prefixedLink"/>   
    <xsl:apply-templates select="./ro:identifier[@type='Purl']" mode = "purl_prefixedLink"/>
    <xsl:apply-templates select="./ro:identifier[@type='Uri']" mode = "uri_prefixedLink"/> 
    <xsl:apply-templates select="./ro:identifier[not(@type =  'Doi' or @type =  'Ark' or @type =  'AU-ANL:PEAU' or @type =  'Handle' or @type =  'Purl' or @type =  'Uri')]" mode="other_prefixedLink"/>			            	                          
  </p>	
</xsl:template-->






<!--
  - <identifier/@type>_resolveURL = The URL which would be displayed which, if clicked in an anchor, resolves the identifier
  - <identifier/@type>_prefixedLink = "Handle: <a href=<resolveURL>..."
-->

<!-- HANDLE IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="handle_resolveURL">
  <xsl:choose>
  <xsl:when test="string-length(substring-after(.,'hdl:'))>0">
     <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl:')"/>
  </xsl:when> 
  <xsl:when test="string-length(substring-after(.,'hdl.handle.net/'))>0">
     <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="substring-after(.,'hdl.handle.net/')"/>
  </xsl:when>                          
  <xsl:when test="string-length(substring-after(.,'http:'))>0">
     <xsl:text></xsl:text><xsl:value-of select="."/>
  </xsl:when>                              
  <xsl:otherwise>
     <xsl:text>http://hdl.handle.net/</xsl:text><xsl:value-of select="."/>
  </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="handle_prefixedLink">

  <xsl:variable name="theidentifier">    			
    <xsl:apply-templates select="." mode="handle_resolveURL" />
  </xsl:variable>

  <xsl:text>Handle: </xsl:text>
  <a>
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
    <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/handle_icon.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">Handle icon</xsl:attribute>
      </img>
  </a> 
  <xsl:if test="following-sibling::ro:identifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if>
</xsl:template>




<!-- DOI IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="doi_resolveURL">

  <xsl:choose>       
    <xsl:when test="string-length(substring-after(.,'doi.org/'))>1">
      <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="substring-after(.,'doi.org/')"/>
    </xsl:when>          
    <xsl:otherwise>
      <xsl:text>http://dx.doi.org/</xsl:text><xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="doi_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="doi_resolveURL" />
  </xsl:variable>

  <xsl:text>DOI: </xsl:text>

  <xsl:if test="string-length(substring-after(.,'10.'))>0">    
    <a>
      <xsl:attribute name="class">identifier</xsl:attribute>
      <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
      <xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>            
      <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/doi_icon.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">DOI icon</xsl:attribute>
      </img>
    </a>
  </xsl:if>
  <xsl:if test="string-length(substring-after(.,'10.'))&lt;1">    
    <a class="identifier"><xsl:value-of select="."/></a>
  </xsl:if>
  <xsl:if test="following-sibling::ro:identifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if> 
</xsl:template>


<!-- DOI IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="orcid_resolveURL">

  <xsl:choose>       
    <xsl:when test="string-length(substring-after(.,'orcid.org/'))>1">
      <xsl:text>http://orcid.org/</xsl:text><xsl:value-of select="substring-after(.,'orcid.org/')"/>
    </xsl:when>          
    <xsl:otherwise>
      <xsl:text>http://orcid.org/</xsl:text><xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="orcid_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="orcid_resolveURL" />
  </xsl:variable>

  <xsl:text>ORCID: </xsl:text>
 
  <a>
    <xsl:attribute name="class">identifier</xsl:attribute>
    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
    <xsl:attribute name="title"><xsl:text>Resolve this ORCID</xsl:text></xsl:attribute>            
    <xsl:value-of select="."/>
    <img class="identifier_logo">
          <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
            <xsl:text>assets/core/images/icons/orcid_icon.png</xsl:text>
          </xsl:attribute>
          <xsl:attribute name="alt">ORCID icon</xsl:attribute>
    </img>
  </a>
  <xsl:if test="following-sibling::ro:identifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if>
</xsl:template>



<!-- NLA IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="nla_resolveURL">
  <xsl:choose>       
    <xsl:when test="string-length(substring-after(.,'nla.gov.au/'))>0">
      <xsl:text>http://nla.gov.au/</xsl:text><xsl:value-of select="substring-after(.,'nla.gov.au/')"/>
    </xsl:when>          
    <xsl:otherwise>
      <xsl:text>http://nla.gov.au/</xsl:text><xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="nla_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="nla_resolveURL" />
  </xsl:variable>

  <xsl:text>NLA: </xsl:text>

  <xsl:if test="string-length(substring-after(.,'nla.party'))>0">    
    <a>
      <xsl:attribute name="class">identifier</xsl:attribute>
      <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
      <xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>            
      <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/nla_icon.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">Trove icon</xsl:attribute>
      </img>
    </a>
  </xsl:if>
  <xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">    
    <a class="identifier"><xsl:value-of select="."/></a>
  </xsl:if> 
  <xsl:if test="following-sibling::ro:identifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if> 
</xsl:template>



<!-- PURL IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="purl_resolveURL">
  <xsl:choose>       
    <xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
      <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="substring-after(.,'purl.org/')"/>
    </xsl:when>          
    <xsl:otherwise>
      <xsl:text>http://purl.org/</xsl:text><xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="purl_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="purl_resolveURL" />
  </xsl:variable>

  <xsl:text>PURL: </xsl:text>

   
    <a>
      <xsl:attribute name="class">identifier</xsl:attribute>
      <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
      <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>            
      <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/external_link.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">External Link</xsl:attribute>
      </img>
    </a>
    <xsl:if test="following-sibling::ro:identifier">    
      <xsl:element name="br">
      </xsl:element>
    </xsl:if>
</xsl:template>


<!-- URI IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="uri_resolveURL">
  <xsl:choose>       
    <xsl:when test="string-length(substring-after(.,'http'))>0">
      <xsl:value-of select="."/>
    </xsl:when>          
    <xsl:otherwise>
      <xsl:text>http://</xsl:text><xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="uri_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="uri_resolveURL" />
  </xsl:variable>

  <xsl:text>URI: </xsl:text> 

    <a>
      <xsl:attribute name="class">identifier</xsl:attribute>
      <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
      <xsl:attribute name="title"><xsl:text>Resolve this URI</xsl:text></xsl:attribute>            
      <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/external_link.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">External Link</xsl:attribute>
      </img>
    </a>
    <xsl:if test="following-sibling::ro:identifier">    
      <xsl:element name="br">
      </xsl:element>
    </xsl:if>
</xsl:template>

<xsl:template match="ro:identifier" mode="urn_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="uri_resolveURL" />
  </xsl:variable>
  <xsl:text>URN: </xsl:text> 
    <a>
      <xsl:attribute name="class">identifier</xsl:attribute>
      <xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
      <xsl:attribute name="title"><xsl:text>Resolve this URN</xsl:text></xsl:attribute>            
      <xsl:value-of select="."/>
      <img class="identifier_logo">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
              <xsl:text>assets/core/images/icons/external_link.png</xsl:text>
            </xsl:attribute>
            <xsl:attribute name="alt">External Link</xsl:attribute>
      </img>
    </a>
    <xsl:if test="following-sibling::ro:identifier">    
      <xsl:element name="br">
      </xsl:element>
    </xsl:if> 
</xsl:template>


<!-- ARK IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="ark_resolveURL">      
  <xsl:choose> 
    <xsl:when test="string-length(substring-after(.,'http://'))>0">
      <xsl:value-of select="(substring-after(.,'http://'))"/>
    </xsl:when>                    
    <xsl:when test="string-length(substring-after(.,'https://'))>0">
      <xsl:value-of select="(substring-after(.,'https://'))"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="."/>
    </xsl:otherwise>   
  </xsl:choose>
</xsl:template>

<xsl:template match="ro:identifier" mode="ark_prefixedLink">

  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="ark_resolveURL" />
  </xsl:variable>

  <xsl:text>ARK: </xsl:text> 

  <xsl:if test="string-length(substring-after(.,'/ark:/'))>0">    
    <xsl:value-of select="$theidentifier"/>
  </xsl:if>
  <xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">    
    <xsl:value-of select="."/>
  </xsl:if> 
  <xsl:if test="following-sibling::roidentifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if>  
</xsl:template>



<!-- OTHER IDENTIFIER DISPLAY MODES -->
<xsl:template match="ro:identifier" mode="other_resolveURL">
  <xsl:value-of select="."/>
</xsl:template>


<xsl:template match="ro:identifier" mode="other_prefixedLink">
  <xsl:variable name="theidentifier">         
    <xsl:apply-templates select="." mode="uri_resolveURL" />
  </xsl:variable>
  <xsl:choose>
     <xsl:when test="./@type='arc' or ./@type='abn' or ./@type='isil'">
         <xsl:value-of select="translate(./@type,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>: <xsl:value-of select="."/>  
     </xsl:when>
     <xsl:when test="./@type='local'">
         <xsl:text>Local: </xsl:text><xsl:value-of select="."/>
     </xsl:when>  
     <xsl:otherwise>
       <xsl:value-of select="./@type"/>: <xsl:value-of select="."/>
   </xsl:otherwise>
  </xsl:choose>
  <xsl:if test="following-sibling::roidentifier">    
    <xsl:element name="br">
    </xsl:element>
  </xsl:if> 
</xsl:template>




<xsl:template match="ro:citationInfo/ro:fullCitation">
    <p><xsl:value-of select="."/></p>
    <span class="Z3988">    
        <xsl:attribute name="title">
            <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
            <xsl:text>&amp;amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
            <xsl:text>&amp;amp;rfr_id=info%3Asid%2FANDS</xsl:text>
            <xsl:text>&amp;amp;rft.title=</xsl:text><xsl:value-of select="//ro:displayTitle"/>
            <xsl:text>&amp;amp;rft.description=</xsl:text><xsl:value-of select="."/>
        </xsl:attribute>
    </span>
    <span class="Z3988">
    </span>     
</xsl:template>



<!-- Reordering of citationMetadata - CC-447 -->
<!-- Contributor (Year): Title. Publisher. Identifier Type: Identifier Value.
      <resolved identifier>
-->
<xsl:template match="ro:citationInfo/ro:citationMetadata">
   <p>
    <xsl:if test="./ro:contributor">
        <xsl:apply-templates select="ro:contributor"/>
    </xsl:if>
    <xsl:if test="./ro:date">
        (
        <xsl:apply-templates select="./ro:date"/>               
        ):           
    </xsl:if>   
    <xsl:if test="./ro:title != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="./ro:title"/>.
    </xsl:if>
    <xsl:if test="./ro:publisher != ''">
        <xsl:text> </xsl:text>      
        <xsl:value-of select="./ro:publisher"/>.
    </xsl:if>
    <xsl:if test="./ro:identifier != ''">
       <xsl:apply-templates select="./ro:identifier[@type = 'doi']" mode="doi_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'uri']" mode="uri_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'URL']" mode="uri_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'url']" mode="uri_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'purl']" mode="purl_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'handle']" mode="handle_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'AU-ANL:PEAU']" mode="nla_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type = 'ark']" mode="ark_prefixedLink"/>
       <xsl:apply-templates select="current()[@type='orcid']" mode = "orcid_prefixedLink"/>
       <xsl:apply-templates select="./ro:identifier[@type != 'doi' and @type != 'uri' and @type != 'URL' and @type != 'url' and @type != 'purl' and @type != 'handle' and @type != 'AU-ANL:PEAU' and @type != 'ark' and @type!='orcid']" mode="other_prefixedLink"/>
       <xsl:text>.</xsl:text>

    </xsl:if>
  <!--xsl:if test="./ro:version != ''">
  <xsl:text> </xsl:text>
  <xsl:value-of select="./ro:version"/>.
  </xsl:if>
  <xsl:if test="./ro:placePublished != ''">
  <xsl:text> </xsl:text>
  <xsl:value-of select="./ro:placePublished"/>.
  </xsl:if>
  <xsl:if test="./ro:publisher != ''">
  <xsl:text> </xsl:text>
  <xsl:value-of select="./ro:publisher"/>.
  </xsl:if>
  <xsl:if test="./ro:url != ''">
  <xsl:text> </xsl:text>
  <xsl:value-of select="./ro:url"/>
  </xsl:if>
  <xsl:if test="./ro:context != ''">
  <xsl:text> </xsl:text>
  , <xsl:value-of select="./ro:context"/>
  </xsl:if-->
    <xsl:if test="./ro:identifier != ''">
      <xsl:variable name="theResolvedURL">
        <xsl:apply-templates select="./ro:identifier[@type = 'doi']" mode="doi_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'uri']" mode="uri_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'URL']" mode="uri_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'url']" mode="uri_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'purl']" mode="purl_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'handle']" mode="handle_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'AU-ANL:PEAU']" mode="nla_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'ark']" mode="other_resolveURL"/>
        <xsl:apply-templates select="./ro:identifier[@type = 'orcid']" mode="orcid_resolveURL"/>
        <!--xsl:apply-templates select="./ro:identifier[@type != 'doi' and @type != 'uri' and @type != 'URL' and @type != 'url' and @type != 'purl' and @type != 'handle' and @type != 'AU-ANL:PEAU' and @type != 'ark' and @type != 'orcid']" mode="other_resolveURL"/-->

      </xsl:variable>

      <xsl:choose>
        <xsl:when test="./ro:identifier[@type = 'ark'] and (string-length(substring-after($theResolvedURL,'http://'))>0 or string-length(substring-after($theResolvedURL,'https://'))>0)">
        <br/>
          <a>
            <xsl:attribute name="class">identifier</xsl:attribute>
            <xsl:attribute name="href"><xsl:value-of select="$theResolvedURL"/></xsl:attribute>
            <xsl:attribute name="title">Resolve this ARK identifier</xsl:attribute>
            <xsl:value-of select="$theResolvedURL"/>
          </a> 
        </xsl:when>
        <xsl:when test="./ro:identifier[@type = 'ark'] and string-length(substring-after($theResolvedURL,'/ark:/'))>0">
        <br/>
          <a>
            <xsl:attribute name="class">identifier</xsl:attribute>
            <xsl:attribute name="href"><xsl:value-of select="concat('http://',$theResolvedURL)"/></xsl:attribute>
            <xsl:attribute name="title">Resolve this ARK identifier</xsl:attribute>
            <xsl:value-of select="$theResolvedURL"/>
          </a> 
        </xsl:when>
        <xsl:when test="./ro:identifier[@type = 'doi' or @type = 'uri' or @type = 'URL' or @type = 'url' or @type = 'purl' or @type = 'handle' or @type = 'AU-ANL:PEAU' or @type = 'orcid']">
          <br/>
          <a>
            <xsl:attribute name="class">identifier</xsl:attribute>
            <xsl:attribute name="href"><xsl:value-of select="$theResolvedURL"/></xsl:attribute>
            <xsl:attribute name="title">Resolve this identifier</xsl:attribute>
            <xsl:value-of select="$theResolvedURL"/>
          </a>
        </xsl:when>
        <!-- maybe we shouldnt display it at all -->
        <!--xsl:otherwise>
            <xsl:value-of select="$theResolvedURL"/>
        </xsl:otherwise-->
      </xsl:choose>
    </xsl:if>
    <xsl:if test="./ro:url != ''">
      <br/>    
      <a href="{./ro:url}" class="external"><xsl:value-of select="./ro:url"/></a>
    </xsl:if>
</p>
<span class="Z3988">   
   <xsl:attribute name="title">
       <xsl:text>ctx_ver=Z39.88-2004</xsl:text>
       <xsl:text>&amp;amp;rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Adc</xsl:text>
       <xsl:text>&amp;amp;rfr_id=info%3Asid%2FANDS</xsl:text>
       <xsl:text>&amp;amp;rft.contributor=</xsl:text><xsl:apply-templates select="ro:contributor"/>
       <xsl:text>&amp;amp;rft.title=</xsl:text><xsl:value-of select="./ro:title"/> 
       <xsl:text>&amp;amp;rft.place=</xsl:text><xsl:value-of select="./ro:placePublished"/>
       <xsl:text>&amp;amp;rft_id=</xsl:text><xsl:value-of select="./ro:url"/>
       <xsl:text>&amp;amp;rft.edition=</xsl:text><xsl:value-of select="./ro:version"/>.
       <xsl:text>&amp;amp;rft.description=</xsl:text><xsl:value-of select="./ro:context"/>
   </xsl:attribute>
</span>
<span class="Z3988">
</span>                                                     
</xsl:template> 

<xsl:template match="ro:contributor">
  <xsl:variable name="displayName">       
    <xsl:apply-templates select="./ro:namePart[@type='family']"/>
    <xsl:apply-templates select="./ro:namePart[@type='given']"/>
    <xsl:if test="./ro:namePart/@type='initial' and not(./ro:namePart/@type='given')">
        <xsl:apply-templates select="./ro:namePart[@type='initial']"/>
    </xsl:if>   
    <xsl:apply-templates select="./ro:namePart[@type='full']"/>
    <xsl:apply-templates select="./ro:namePart[@type='']"/>
    <!-- catch-all statement for dodgy data -->
    <xsl:apply-templates select="./ro:namePart[not (@type)] | ./ro:namePart[not(@type='family') and not(@type='given') and not(@type='initial') and not(@type='full') and not(@type='')]"/>   
  </xsl:variable> 
  <xsl:value-of select="concat(substring($displayName,1,string-length($displayName)-2),' ')"/>     
</xsl:template> 

<xsl:template match="//ro:citationInfo/ro:citationMetadata/ro:date[text() != '']">
    <xsl:if test="position()>1">
        <xsl:text>,</xsl:text>
    </xsl:if>       
    <xsl:value-of select="substring(.,1,4)"/> 
</xsl:template> 

<xsl:template match="ro:location/ro:address/ro:electronic[ro:value/text() != '']">
  <xsl:if test="./@type='url'">

      <xsl:variable name="url">
          <xsl:choose>
              <xsl:when test="string-length(.)>30">
                <xsl:value-of select="substring(.,0,30)"/>...
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="."/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable> 
    <a>
        <xsl:attribute name="href">
            <xsl:value-of select="."/>
        </xsl:attribute>
        <xsl:attribute name="class">recordOutBound</xsl:attribute>
        <xsl:attribute name="type">electronic_address</xsl:attribute>
        <xsl:attribute name="target">_blank</xsl:attribute><xsl:value-of select="$url"/>
    </a><br />
</xsl:if>
</xsl:template>

<xsl:template match="ro:location/ro:address/ro:physical[ro:addressPart/text() != '']">
  <p>
     <xsl:choose>
        <xsl:when test = "./ro:addressPart">
          <xsl:apply-templates select="./ro:addressPart[@type='fullName']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='organizationName']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='buildingOrPropertyName']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='flatOrUnitNumber']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='floorOrLevelNumber']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='lotNumber']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='houseNumber']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='streetName']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberPrefix']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberValue']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='postalDeliveryNumberSuffix']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='addressLine']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='suburbOrPlaceOrLocality']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='stateOrTerritory']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='postCode']"/>	
          <xsl:apply-templates select="./ro:addressPart[@type='country']"/>		
          <xsl:apply-templates select="./ro:addressPart[@type='locationDescriptor']"/>
          <xsl:apply-templates select="./ro:addressPart[@type='deliveryPointIdentifier']"/>					
          <xsl:apply-templates select="./ro:addressPart[not(@type='organizationName' or @type='fullName' or @type='buildingOrPropertyName' or @type='flatOrUnitNumber' or @type='floorOrLevelNumber' or @type='lotNumber' or @type='houseNumber' or @type='streetName' or @type='postalDeliveryNumberPrefix' or @type='postalDeliveryNumberValue' or @type='postalDeliveryNumberSuffix' or @type='addressLine' or @type='suburbOrPlaceOrLocality' or @type='stateOrTerritory' or @type='country' or @type='locationDescriptor' or @type='deliveryPointIdentifier' or @type='postCode' or @type='telephoneNumber' or @type='faxNumber')]"/>	

          <!--xsl:apply-templates select="./ro:addressPart[not(@type='addressLine') or @type!='deliveryPointIdentifier' or @type='locationDescriptor' or @type='country' or @type='stateOrTerritory' or @type='suburbOrPlaceOrLocality' or @type='suburbOrPlaceOrLocality' or @type='addressLine' or @type='postalDeliveryNumberSuffix])"/-->
      </xsl:when>
      <xsl:otherwise>
       <xsl:value-of select="." disable-output-escaping="yes"/><br />			
   </xsl:otherwise>
</xsl:choose>	
</p>
</xsl:template>	

<xsl:template match="ro:addressPart[text() != '']">			
 <xsl:value-of select="." disable-output-escaping="yes"/><br />
</xsl:template> 

<xsl:template match="//extRif:right[@type!='licence']">

 <xsl:if test="./@type='rights' or ./@type='rightsStatement'"><h4>Rights statement</h4></xsl:if>
 <xsl:if test="./@type='accessRights'"><h4>Access rights</h4></xsl:if>
 <p class="rights"><xsl:value-of select="." disable-output-escaping="yes"/>
 <xsl:if test="./@rightsUri"><p>
    <a target="_blank">
        <xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
    </xsl:if>
</p>  

</xsl:template> 
<xsl:template match="//extRif:right[@type='licence']">
  <h4>Licence</h4>
  <p class="rights">
     <xsl:if test="string-length(substring-after(./@licence_type,'CC-'))>0">
        <img id="licence_logo" style="width:130px;height:auto">
            <xsl:attribute name="src"><xsl:value-of select="$base_url"/>
            <xsl:text>assets/core/images/icons/</xsl:text>
            <xsl:value-of select="./@licence_type"/>
            <xsl:text>.png</xsl:text></xsl:attribute>
            <xsl:attribute name="alt"><xsl:value-of select="./@licence_type"/></xsl:attribute>
        </img>
    </xsl:if>
    <xsl:if test="string-length(substring-after(./@licence_type,'CC-'))=0">    
     <xsl:if test="./@licence_type='Unknown' and .=''"><p class="rights">Unknown</p></xsl:if>
     <xsl:if test="./@licence_type!='Unknown' and .!='' "><p class="rights"><xsl:value-of select="./@licence_type"/></p></xsl:if>
     <!--  <xsl:value-of select="./@licence_type"/> -->
 </xsl:if>
 <xsl:if test=". and .!=''"><p class="rights"><xsl:value-of select="."/></p></xsl:if>
 <xsl:if test="./@rightsUri"><p>
    <a target="_blank">
        <xsl:attribute name="href"><xsl:value-of select="./@rightsUri"/></xsl:attribute><xsl:value-of select="./@rightsUri"/></a></p>
    </xsl:if>           
</p>    
</xsl:template>


<xsl:template match="extRif:description" mode="content">     
    <div>
       <xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>
       <xsl:choose>
        <xsl:when test="@type = 'full'">
          <h5 class="lightgrey">Full Description</h5>
        </xsl:when>
        <xsl:when test="@type ='brief'">
          <h5 class="lightgrey">Brief Description</h5>
        </xsl:when>
        <xsl:when test="@type ='note'">
          <h5 class="lightgrey">Note</h5>
        </xsl:when>
        <xsl:when test="@type ='significanceStatement'">
          <h5 class="lightgrey">Significance Statement</h5>
        </xsl:when>
        <xsl:when test="@type = 'deliverymethod'">
          <h5 class="lightgrey">Delivery Method</h5>
        </xsl:when>
       </xsl:choose>
       <p>
         <xsl:value-of select="." disable-output-escaping="yes"/>
       </p>
     </div>
 </xsl:template> 

  <xsl:template match="ro:temporal" mode="date">
    <xsl:choose>
      <xsl:when test="extRif:friendly_date[text() != '']">
        <p><xsl:value-of select="extRif:friendly_date[text() != '']" /></p>
      </xsl:when>
      <xsl:otherwise>
       <p><xsl:apply-templates select="ro:date[text() != '']"/></p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

 <xsl:template match="ro:location/ro:address/ro:electronic/@type">		
  <xsl:if test=".='email'">	
   <xsl:value-of select=".."/><br />
</xsl:if>				
</xsl:template> 

<xsl:template match="ro:dates[ro:date/text() != '']">

  <xsl:choose>
    <xsl:when test="./extRif:friendly_date">
      <p><xsl:value-of select="./extRif:friendly_date"/></p>
    </xsl:when>
    <xsl:otherwise>

      <xsl:if test="./ro:date/@type!='dateTo'"> 
          <xsl:value-of select="./ro:date[@type!='dateTo']"/> 
      </xsl:if>      
      <xsl:if test="./ro:date/@type='dateTo'"> to 
        <xsl:value-of select="./ro:date[@type='dateTo']"/> 
      </xsl:if>

    </xsl:otherwise>
  </xsl:choose>

</xsl:template>

<xsl:template match="extRif:extendedMetadata" priority="-1" />


</xsl:stylesheet>