<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif ro">
	<xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:param name="dataSource"/>
	<xsl:param name="dateCreated"/>

	<xsl:template match="/">
			<xsl:apply-templates select="//ro:registryObject"/>
	</xsl:template>

	<xsl:template match="ro:registryObject">
		<table class="recordTable" summary="Preview of Draft Registry Object">
			<tbody class="recordFields">
				<tr>
					<td>Class: </td>
					<td style="">
						<xsl:choose>
						
							<xsl:when test="ro:collection">
								Collection
							</xsl:when>
							<xsl:when test="ro:party">
								Party
							</xsl:when>
							<xsl:when test="ro:activity">
								Activity
							</xsl:when>
							<xsl:when test="ro:service">
								Service
							</xsl:when>

						</xsl:choose>
					</td>
				</tr>
				<tr>
					<td>Type: </td>
					<td style="">
						<xsl:apply-templates select="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type"/>
					</td>
				</tr>

				<tr>
					<td>Key: </td>
					<td>
						<xsl:apply-templates select="ro:key"/>
					</td>
				</tr>

				<tr>
					<td>Source: </td>
					<td>
						<xsl:value-of select="$dataSource"/>
					</td>
				</tr>

				<tr>
					<td>Originating Source: </td>
					<td>
						<xsl:apply-templates select="ro:originatingSource"/>
					</td>
				</tr>
			
				<tr>
					<td>Group: </td>
					<td>
						<xsl:apply-templates select="@group"/>
					</td>
				</tr>

				<xsl:if test="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified">
					<tr>
						<td>Date Modified: </td>
						<td style="">
							<xsl:apply-templates select="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified"/>
						</td>
					</tr>
				</xsl:if>

				<xsl:if test="ro:collection/@dateAccessioned">				
					<tr>
						<td>Date Accessioned: </td>
						<td style="">
							<xsl:apply-templates select="ro:collection/@dateAccessioned"/>
						</td>
					</tr>
				</xsl:if>
			
				<xsl:apply-templates select="ro:collection | ro:activity | ro:party | ro:service"/>
						
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="@group">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:key">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<!-- xsl:template match="relatedObject/key">
		<tr>
			<td class="attribute">
				<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
			</td>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template-->
	
	<xsl:template match="ro:relatedObject/ro:key">
		<tr>
			<td></td><td class="resolvedRelated" key_value="{.}"></td>
		</tr>	
		<tr>
			<td class="attribute">
				<xsl:value-of select="name()"/><xsl:text>: </xsl:text></td>
			<td class="valueAttribute resolvable_key" key_value="{.}">
				<xsl:value-of select="."/>
			</td>
		</tr> 


	</xsl:template>
	
	<xsl:template match="ro:originatingSource">
		<xsl:value-of select="."/>
	</xsl:template>

	<xsl:template match="ro:collection/@dateAccessioned">
		<xsl:value-of select="."/>
	</xsl:template>

	<xsl:template match="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified">
		<xsl:value-of select="."/>
	</xsl:template>

	<xsl:template match="ro:collection | ro:activity | ro:party | ro:service">

		<xsl:if test="ro:name">
			<tr>
				<td>Names:</td>
				<td>
					<table class="subtable">
					<xsl:apply-templates select="ro:name"/>
					</table>
				</td>
			</tr>
		</xsl:if>

		<xsl:choose>
			<xsl:when test="ro:dates">
				<tr>
					<td>Dates:</td>
					<td>
						<table class="subtable">
							<xsl:apply-templates select="ro:dates"/>
						</table>
					</td>
				</tr>
			</xsl:when>
	 	</xsl:choose>

		<xsl:if test="ro:identifier">
			<tr>
				<td>Identifiers:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:identifier"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		<xsl:if test="ro:location">
			<tr>
				<td>Location:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:location"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		<xsl:if test="ro:coverage">
			<tr>
				<td>Coverage:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:coverage"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
		

			<tr class="hide" id="rorow">
				<td>Related Objects:</td>
				<td>
					<table class="subtable" id="related_objects_table">
					 <xsl:if test="ro:relatedObject">
						<xsl:apply-templates select="ro:relatedObject"/>
					</xsl:if>
					</table>
				</td>
			</tr>

		
		
		<xsl:if test="ro:subject">
			<tr>
				<td>Subjects:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:subject"/>
					</table>
				</td>
			</tr>
		</xsl:if>

		<xsl:choose>
			<xsl:when test="ro:description">
				<tr>
					<td>Description:</td>
					<td><!--  div name="errors_description" class="fieldError"/-->
						<table class="subtable">
							<xsl:apply-templates select="ro:description"/>
						</table>
					</td>
				</tr>
			</xsl:when>
	 	</xsl:choose>
	 	
	 	<xsl:choose>
			<xsl:when test="ro:existenceDates">
				<tr>
					<td>Existence Dates:</td>
					<td>
						<table class="subtable">
							<xsl:apply-templates select="ro:existenceDates/ro:startDate | ro:existenceDates/ro:endDate "/>
						</table>
					</td>
				</tr>
			</xsl:when>
	 	</xsl:choose>
	 	
	 	<xsl:if test="ro:citationInfo">
			<tr>
				<td>Citation:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:citationInfo/ro:citationMetadata | ro:citationInfo/ro:fullCitation"/>
					</table>
				</td>
			</tr>
		</xsl:if>
	 	
	 	<xsl:if test="ro:relatedInfo">
			<tr>
				<td>Related Info:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:relatedInfo"/>
					</table>
				</td>
			</tr>
		</xsl:if>	
		
		 <xsl:if test="ro:rights">
			<tr>
				<td>Rights:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:rights"/>
					</table>
				</td>
			</tr>
		</xsl:if>
		
	 	<xsl:if test="ro:accessPolicy">
			<tr>
				<td>Access Policy:</td>
				<td>
					<table class="subtable">
						<xsl:apply-templates select="ro:accessPolicy"/>
					</table>
				</td>
			</tr>
		</xsl:if>
	 	
	</xsl:template>
	
	<!-- xsl:template match="citationMetadata/identifier">
		
		
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/>:
			</td>
			<td>
				<table class="subtable1">
		
					<tr>	
						<td class="attribute">
							Type<xsl:text>: </xsl:text>
						</td>
						<td class="valueAttribute">
							<xsl:value-of select="@type" />
						</td>
					</tr>
					<tr>	
						<td class="attribute">
							Value<xsl:text>: </xsl:text>
						</td>
						<td>
					    	<xsl:apply-templates select="current()[@type='doi']" mode = "doi"/>
					     	<xsl:apply-templates select="current()[@type='ark']" mode = "ark"/>    	
					      	<xsl:apply-templates select="current()[@type='AU-ANL:PEAU']" mode = "nla"/>  
					      	<xsl:apply-templates select="current()[@type='handle']" mode = "handle"/>   
					      	<xsl:apply-templates select="current()[@type='purl']" mode = "purl"/>
					     	<xsl:apply-templates select="current()[@type='uri']" mode = "uri"/> 
							<xsl:if test="not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')">
								<xsl:value-of select="." />
							</xsl:if>
					  		<xsl:apply-templates select="current()[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>
						</td>
					</tr>
					
				</table>
			</td>
		</tr>
	</xsl:template-->
	
	<xsl:template match="ro:citationMetadata/ro:identifier">
		
		
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/>:
			</td>
			<td>
				<table class="subtable1">
		
					<tr>	
						<td class="attribute">
							Type<xsl:text>: </xsl:text>
						</td>
						<td class="valueAttribute">
							<xsl:value-of select="@type" />
						</td>
					</tr>
					<tr>	
						<td class="attribute">
							Value<xsl:text>: </xsl:text>
						</td>
						<td>
					    	<xsl:apply-templates select="current()[@type='doi']" mode = "doi"/>
					     	<xsl:apply-templates select="current()[@type='ark']" mode = "ark"/>    	
					      	<xsl:apply-templates select="current()[@type='AU-ANL:PEAU']" mode = "nla"/>  
					      	<xsl:apply-templates select="current()[@type='handle']" mode = "handle"/>   
					      	<xsl:apply-templates select="current()[@type='purl']" mode = "purl"/>
					     	<xsl:apply-templates select="current()[@type='uri']" mode = "uri"/> 
							<xsl:if test="not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')">
								<xsl:value-of select="." />
							</xsl:if>
					  		<!--<xsl:apply-templates select="current()[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>-->
						</td>
					</tr>
					
				</table>
			</td>
		</tr>
	</xsl:template>
	
		
	
    <xsl:template match="ro:relatedInfo/ro:identifier">
		
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/>:
			</td>
			<td>
				<table class="subtable1">
		
					<tr>	
						<td class="attribute">
							Type<xsl:text>: </xsl:text>
						</td>
						<td class="valueAttribute">
							<xsl:value-of select="@type" />
						</td>
					</tr>
					<tr>	
						<td class="attribute">
							Value<xsl:text>: </xsl:text>
						</td>
						<td>
					    	<xsl:apply-templates select="current()[@type='doi']" mode = "doi"/>
					     	<xsl:apply-templates select="current()[@type='ark']" mode = "ark"/>    	
					      	<xsl:apply-templates select="current()[@type='AU-ANL:PEAU']" mode = "nla"/>  
					      	<xsl:apply-templates select="current()[@type='handle']" mode = "handle"/>   
					      	<xsl:apply-templates select="current()[@type='purl']" mode = "purl"/>
					     	<xsl:apply-templates select="current()[@type='uri']" mode = "uri"/> 
							<xsl:if test="not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')">
								<xsl:value-of select="." />
							</xsl:if>
					  		<!--<xsl:apply-templates select="current()[not(@type =  'doi' or @type =  'ark' or @type =  'AU-ANL:PEAU' or @type =  'handle' or @type =  'purl' or @type =  'uri')]" mode="other"/>-->
						</td>
					</tr>
					
				</table>
			</td>
		</tr>
	           
    </xsl:template>
	
	
    <xsl:template match="ro:relatedInfo">
		<tr>	
			<td class="attribute">
				Type<xsl:text>: </xsl:text>
			</td>
			<td class="valueAttribute">
				<xsl:value-of select="@type" />
			</td>
		</tr>
		<tr>	
			<td colspan="2">
				<table class="subtable1">
		
					<xsl:if test="ro:title">
			         	<xsl:apply-templates select="ro:title"/>
					</xsl:if>
					<xsl:apply-templates select="ro:identifier"/>
					<xsl:apply-templates select="ro:relation"/>
					<xsl:if test="ro:format">
			         	<xsl:apply-templates select="ro:format"/>
					</xsl:if>
					<xsl:if test="ro:notes">
			         	<xsl:apply-templates select="ro:notes"/>
					</xsl:if>
				</table>
			</td>
		</tr>
	           
     </xsl:template>
	
	  <xsl:template match="ro:identifier" mode="ark">
	      			    <xsl:variable name="theidentifier">    			
	    				<xsl:choose>	
	    			    	<xsl:when test="string-length(substring-after(.,'http://'))>0">
	    			     		<xsl:value-of select="(substring-after(.,'http://'))"/>
	    			     	</xsl:when>	    							
	     	
	    			     	<xsl:otherwise>
	    			     		<xsl:value-of select="."/>
	    			     	</xsl:otherwise>		
	    				</xsl:choose>
	 					</xsl:variable>  
	 					<xsl:if test="string-length(substring-after(.,'/ark:/'))>0">    			     
	    				<a>
						<xsl:attribute name="target"><xsl:text>_blank</xsl:text></xsl:attribute>
	    				<xsl:attribute name="href"><xsl:text>http://</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
	    				<xsl:attribute name="title"><xsl:text>Resolve this ARK identifier</xsl:text></xsl:attribute>    				
	    				<xsl:value-of select="."/>
	    				</a>
	    				</xsl:if>
	    				<xsl:if test="string-length(substring-after(.,'/ark:/'))&lt;1">
	    					<xsl:value-of select="."/>
	    				</xsl:if> 
	</xsl:template>
	 <xsl:template match="ro:identifier" mode="nla">
	       			    <xsl:variable name="theidentifier">    			
	    				<xsl:choose>				
	    			    	<xsl:when test="string-length(substring-after(.,'nla.gov.au/'))>0">
	    			     		<xsl:value-of select="substring-after(.,'nla.gov.au/')"/>
	    			     	</xsl:when>		     	
	    			     	<xsl:otherwise>
	    			     		<xsl:value-of select="."/>
	    			     	</xsl:otherwise>		
	    				</xsl:choose>
	 					</xsl:variable>  
	 					<xsl:if test="string-length(substring-after(.,'nla.party'))>0">		
	    				<a>
						<xsl:attribute name="target"><xsl:text>_blank</xsl:text></xsl:attribute>
	    				<xsl:attribute name="href"><xsl:text>http://nla.gov.au/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
	    				<xsl:attribute name="title"><xsl:text>View the record for this party in Trove</xsl:text></xsl:attribute>    				
	    				<xsl:value-of select="."/>
	    				</a> 	<br />
	  				</xsl:if> 
	  					<xsl:if test="string-length(substring-after(.,'nla.party'))&lt;1">		
   				
	    				<xsl:value-of select="."/>
	  				</xsl:if> 
	 </xsl:template>
	 <xsl:template match="ro:identifier" mode="doi">   					
	        	  <xsl:variable name="theidentifier">    			
	    				<xsl:choose>				
	    			    	<xsl:when test="string-length(substring-after(.,'doi.org/'))>0">
	    			     		<xsl:value-of select="substring-after(.,'doi.org/')"/>
	    			     	</xsl:when>		     	
	    			     	<xsl:otherwise>
	    			     		<xsl:value-of select="."/>
	    			     	</xsl:otherwise>		
	    				</xsl:choose>
	 					</xsl:variable>   	  
	  					<xsl:if test="string-length(substring-after(.,'10.'))>0">		
	      				<a>
						<xsl:attribute name="target"><xsl:text>_blank</xsl:text></xsl:attribute>
	    				<xsl:attribute name="href"><xsl:text>http://dx.doi.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
	    				<xsl:attribute name="title"><xsl:text>Resolve this DOI</xsl:text></xsl:attribute>    				
	    				<xsl:value-of select="."/>
	    				</a> 		 <br />
	  				</xsl:if> 
	  					<xsl:if test="string-length(substring-after(.,'10.'))&lt;1">		
   				
	    				<xsl:value-of select="."/>
	  				</xsl:if> 					 			

    			
	 </xsl:template>
	 <xsl:template match="ro:identifier" mode="handle">      			
	      			    <xsl:variable name="theidentifier">    			
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
	 					</xsl:variable>
    			     
	    				<a>
						<xsl:attribute name="target"><xsl:text>_blank</xsl:text></xsl:attribute>
	    				<xsl:attribute name="href"> <xsl:value-of select="$theidentifier"/></xsl:attribute>
	    				<xsl:attribute name="title"><xsl:text>Resolve this handle</xsl:text></xsl:attribute>    				
	    				<xsl:value-of select="."/>
	    				</a> 	 
	 </xsl:template>
	 <xsl:template match="ro:identifier" mode="purl">     			
	    <xsl:variable name="theidentifier">    			
	    <xsl:choose>				
	    	<xsl:when test="string-length(substring-after(.,'purl.org/'))>0">
	    		<xsl:value-of select="substring-after(.,'purl.org/')"/>
	    	</xsl:when>		     	
	    	<xsl:otherwise>
	    		<xsl:value-of select="."/>
	    	</xsl:otherwise>		
	    </xsl:choose>
	 	</xsl:variable>   	   			
	    <a>
	    <xsl:attribute name="href"><xsl:text>http://purl.org/</xsl:text> <xsl:value-of select="$theidentifier"/></xsl:attribute>
	    <xsl:attribute name="title"><xsl:text>Resolve this purl identifier</xsl:text></xsl:attribute>    				
	    <xsl:value-of select="."/>
	    </a>  
	 </xsl:template>
	 
	 <xsl:template match="ro:identifier | ro:relation/ro:url" mode="uri">     			
	   <xsl:variable name="theidentifier">    			
	    <xsl:choose>				
	    	<xsl:when test="string-length(substring-after(.,'http'))>0"><xsl:value-of select="."/></xsl:when>		     	
	    	<xsl:otherwise>http://<xsl:value-of select="."/></xsl:otherwise>		
	    </xsl:choose>
	 	</xsl:variable>   	        			
	    <a>
		<xsl:attribute name="target"><xsl:text>_blank</xsl:text></xsl:attribute>			
	    <xsl:attribute name="href"><xsl:value-of select="$theidentifier"/></xsl:attribute>
	    <xsl:attribute name="title"><xsl:text>Resolve this uri</xsl:text></xsl:attribute>    				
	    <xsl:value-of select="."/>  
	    </a>   		 
	</xsl:template> 
	
    <xsl:template match="ro:identifier" mode="other">     		
		
		<tr>	
			<td class="attribute">
				Type:
			</td>
			<td class="valueAttribute">	
				<xsl:value-of select="./@type"/>
			</td>
		</tr>
		<tr>	
			<td class="attribute">
				Value:
			</td>
			<td class="">	
				<xsl:value-of select="."/>
			</td>
		</tr>
		
     </xsl:template>  
	
	

	<xsl:template match="ro:relation/ro:description">
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
			</td>
			<td>
				<table class="subtable1">
					<xsl:value-of select="."/>
				</table>
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="ro:relation/ro:url  | ro:electronic/ro:value | ro:electronic/ro:title | ro:electronic/ro:byteSize | ro:electronic/ro:mediaType | ro:electronic/ro:notes">
		<tr>	
			<td class="attribute">
				<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
			</td>
			<td>
				<xsl:apply-templates select="." mode="uri"/>
			</td>
		</tr>
	</xsl:template>
	


	<xsl:template match="ro:name/ro:namePart">
		<tr>	
			<td class="attribute">
			<xsl:choose>
				<xsl:when test="preceding-sibling::ro:namePart"/>
				<xsl:when test="following-sibling::ro:namePart">
					<xsl:text>Name Parts:</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>Name Part:</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			</td>
			<td>
				<table class="subtable1">
					<xsl:apply-templates select="@* | node()"/>
				</table>
			</td>
		</tr>
	</xsl:template>
	
	<xsl:template match="text()">
		<xsl:if test="not(following-sibling::node()) and not(preceding-sibling::node())">
		<tr>
			<td class="attribute">Value: </td>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
		</xsl:if>
	</xsl:template>


	<xsl:template match="ro:value/text()">
		<tr>
			<td class="value">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="node()">
		<tr>
			<xsl:if test="(not(contains('-name-relatedObject-description-subject-rights-', concat('-',local-name(),'-'))))">	
				<td class="attribute">
					<xsl:value-of select="local-name()"/><xsl:text>: </xsl:text>
				</td>
			</xsl:if>
			<td>
				<table class="subtable1">
					<xsl:apply-templates select="@* | node()"/>
				</table>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="@*">
		<tr>
			<td class="attribute">
				<xsl:value-of select="name()"/><xsl:text>: </xsl:text></td>
			<td class="valueAttribute">
				<xsl:value-of select="."/>
			</td>
		</tr>
	</xsl:template>
	
		
	<xsl:template match="@field_id | @tab_id | @lang"/>


</xsl:stylesheet>
