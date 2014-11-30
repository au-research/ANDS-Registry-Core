<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:ro="http://ands.org.au/standards/rif-cs/registryObjects" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:extRif="http://ands.org.au/standards/rif-cs/extendedRegistryObjects"
	exclude-result-prefixes="extRif ro">
	<xsl:output method="html" encoding="UTF-8" indent="yes" omit-xml-declaration="yes"/>
	<xsl:param name="base_url"/>
	<xsl:variable name="maxRelatedDisp" select="30"/>

	<xsl:variable name="ro_class">
		<xsl:apply-templates select="ro:registryObject/ro:collection | ro:registryObject/ro:activity | ro:registryObject/ro:party  | ro:registryObject/ro:service" mode="getClass"/>
	</xsl:variable>


	<xsl:template match="/">
			<xsl:apply-templates select="//ro:registryObject"/>
	</xsl:template>

	<xsl:template match="ro:registryObject">

	<xsl:variable name="registry_object_id"><xsl:value-of select="//extRif:id"/></xsl:variable>
	<xsl:variable name="dataSourceID"><xsl:value-of select="//extRif:dataSourceID"/></xsl:variable>
	<xsl:variable name="dataSourceTitle"><xsl:value-of select="//extRif:dataSourceTitle"/></xsl:variable>
	<xsl:variable name="display_title"><xsl:value-of select="//extRif:displayTitle" disable-output-escaping="yes" /></xsl:variable>

	
	<div id="sidebar">
		<input id="data_source_id" value="{$dataSourceID}" type="hidden" class="hide" />
		<div id="mode-switch" class="btn-group" style="display:none; text-align: center;margin: 10px auto 0px auto;">
			<button class="btn btn-primary" aro-mode="simple">Simple</button>
			<button class="btn" aro-mode="advanced">Advanced</button>
		</div>
		<ul id="simple-menu" class="hide">
			<li class="active"><a href="#simple_describe" data-toggle="tab"><span>Describe your Data</span></a></li>
			<li class=""><a href="#simple_link" data-toggle="tab"><span>Link your Data</span></a></li>
			<li class=""><a href="#simple_citation" data-toggle="tab"><span>Create a Citation</span></a></li>
			<li class=""><a href="#simple_protect" data-toggle="tab"><span>Protect your Data</span></a></li>
		</ul>
		<ul id="advanced-menu" class="">
			<li class="active"><a href="#admin" data-toggle="tab">Record Administration</a></li>
			<li><a href="#names" data-toggle="tab">Names</a></li>
			<li><a href="#descriptions_rights" data-toggle="tab">Descriptions/Rights</a></li>
			<li><a href="#identifiers" data-toggle="tab">Identifiers</a></li>
			<xsl:if test="$ro_class = 'collection'">
				<li><a href="#dates" data-toggle="tab">Dates</a></li>
			</xsl:if>
			<li><a href="#locations" data-toggle="tab">Locations</a></li>
			<li><a href="#coverages" data-toggle="tab">Coverage</a></li>
			<li><a href="#relatedObjects" data-toggle="tab">Related Objects</a></li>
			<li><a href="#subjects" data-toggle="tab">Subjects</a></li>
			<li><a href="#relatedinfos" data-toggle="tab">Related Info</a></li>
			<xsl:if test="$ro_class = 'service'">
				<li><a href="#accesspolicies" data-toggle="tab">Access Policy</a></li>
			</xsl:if>
			<xsl:if test="$ro_class = 'collection'">
				<li><a href="#citationInfos" data-toggle="tab">Citation Info</a></li>
			</xsl:if>
			<xsl:if test="$ro_class != 'collection'">
				<li><a href="#existencedates" data-toggle="tab">Existence Dates</a></li>
			</xsl:if>
			<li id="annotations_tab"><a href="#annotations_pane" data-toggle="tab">Annotations</a></li>
			<li><a href="#qa" id="savePreview" data-toggle="tab"><i class="icon-white icon-hdd"></i> Save &amp; Validate</a></li>
		</ul>
	</div>

	<div id="content" style="Manage My Data Sources">
		<div class="content-header">
			<h1>
				<img class="class_icon">
				<xsl:attribute name="tip">
					<xsl:value-of select="$ro_class"/> 
				</xsl:attribute>
				<xsl:attribute name="src">
					<xsl:value-of select="$base_url"/><xsl:text>../assets/img/</xsl:text><xsl:value-of select="$ro_class"/>.png</xsl:attribute>
			</img><xsl:text> </xsl:text>
				<xsl:value-of select="$display_title"/></h1>
			<div class="btn-group">
				<!--a class="hide btn" title="Manage Files" id="master_export_xml"><i class="icon-download"></i> Export RIFCS</a>
				<a class="hide btn btn-primary" title="Manage Files" id="validate">Validate</a-->
				<a class="btn btn-primary" title="Manage Files" id="save"><i class="icon-white icon-hdd"></i> Save &amp; Validate</a>
			</div>
		</div>
		<div id="breadcrumb" class="clear">
			<a href="{$base_url}data_source/manage_records/{$dataSourceID}"><xsl:value-of select="$dataSourceTitle" /> - Manage Records</a>
			<a href="{$base_url}registry_object/view/{$registry_object_id}" title="" class="current"><xsl:value-of select="$display_title"/></a>
			<a href="#" class="">Edit</a>
			<div class="pull-right"><span class="label"><i class="icon-question-sign icon-white"></i><a class="youtube" href="http://www.youtube.com/watch?v=noeAISwMkNE" style="color:white;" > New to this screen? Take a tour!</a></span>	<xsl:text> </xsl:text>			
				<span class="label"><i class="icon-question-sign icon-white"></i> <a id="aro_help_link" target="_blank" style="color:white;" href="http://ands.org.au/guides/cpguide/"> Help</a></span>
			</div>
		</div>
		<form class="form-horizontal" id="edit-form" autocomplete="off">
			<xsl:call-template name="simpleDescribeTab"/>
			<xsl:call-template name="recordAdminTab"/>
			<xsl:call-template name="namesTab"/>
			<xsl:call-template name="descriptionRightsTab"/>
			<xsl:call-template name="identifiersTab"/>
			<xsl:if test="$ro_class = 'collection'">
				<xsl:call-template name="datesTab"/>
			</xsl:if>
			<xsl:call-template name="locationsTab"/>
			<xsl:call-template name="coverageTab"/>
			<xsl:call-template name="relatedObjectsTab"/>
			<xsl:call-template name="subjectsTab"/>
			<xsl:call-template name="relatedinfosTab"/>
			<xsl:if test="$ro_class = 'service'">
				<xsl:call-template name="accesspolicyTab"/>
			</xsl:if>
			<xsl:if test="$ro_class = 'collection'">
				<xsl:call-template name="citationInfoTab"/>
			</xsl:if>
			<xsl:if test="$ro_class != 'collection'">
				<xsl:call-template name="ExistenceDatesTab"/>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="extRif:annotations">
					<xsl:apply-templates select="extRif:annotations"/>
				</xsl:when>
				<xsl:otherwise>
					<div id="annotations_pane" class="pane hide">
						<fieldset>
							<legend>Annotations</legend>
							<textarea id="annotations" rows="5" class="input-xxlarge" name="annotations"></textarea>	
						</fieldset>
					</div>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:apply-templates select="extRif:annotations"/>
			<xsl:call-template name="recordQATab">
				<xsl:with-param name="registry_object_id" select="$registry_object_id"/>
				<xsl:with-param name="base_url" select="$base_url"/>
			</xsl:call-template>
		</form>
		<xsl:call-template name="blankTemplate"/>
		<div class="modal large hide" id="myModal">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>Alert</h3>
			</div>
			<div class="modal-body"/>
			<div class="modal-footer"> </div>
		</div>
		
	</div>


		<input type="hidden" class="hide" id="ro_id" value="{$registry_object_id}"/>
		<input type="hidden" class="hide" id="ro_class" value="{$ro_class}"/>
		<input type="hidden" class="hide" id="originatingSource" value="{ro:originatingSource/text()}"/>
	</xsl:template>

	<xsl:template match="ro:collection | ro:activity | ro:party  | ro:service" mode="getClass">
		<xsl:value-of select="name()"/>
	</xsl:template>




	<xsl:template match="extRif:annotations">
		<div id="annotations_pane" class="pane hide">
			<fieldset>
				<legend>Annotations</legend>
				<div class="separate_line"/>
				<textarea id="annotations" rows="5" class="input-xxlarge" name="annotations">
					<xsl:copy-of select="node()"/>
				</textarea>	
			</fieldset>
		</div>		
	</xsl:template>

	<xsl:template name="simpleDescribeTab" mode="collection">
		<!-- Record Admin-->
		<div id="simple_describe" class="pane hide">
			
			<br/>
			<span class="alert persist"><strong>A much simpler record editor is coming soon!</strong> In the meantime, please use the <a href="#!/advanced/admin">Advanced Mode</a> to edit records.</span>
			<fieldset class="hide">
				<legend>Describe your Data</legend>

				<xsl:variable name="simpleRecordName" select="ro:collection/ro:name[@type='primary']/ro:namePart[1]" />
				<xsl:variable name="simpleRecordType" select="ro:collection/@type" />
				<xsl:variable name="simpleBriefDescription" select="ro:collection/ro:description" />
				<xsl:variable name="simpleFullDescription" select="ro:collection/ro:description[@type='full']" />
				<xsl:variable name="simpleRecordIdentifier" select="ro:collection/ro:identifier[0]" />
				<xsl:variable name="simpleRecordIdentifierType" select="ro:collection/ro:identifier[0]/@type" />
				<xsl:variable name="simpleRecordGroup" select="@group" />


				<div class="control-group">
					<label class="control-label" for="simple_collectionTitle">* Collection Title</label>
					<div class="controls">
							<input type="text" field-bind="ro:collection/ro:name[@type='primary']/ro:namePart[1]" class="input-xxlarge" name="simpleRecordName" value="{$simpleRecordName}"/>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simple_briefDescription">* Brief Collection Description</label>
					<div class="controls">
							<textarea rows="5" class="input-xxlarge" name="simpleBriefDescription"><xsl:value-of select="$simpleBriefDescription"/></textarea>
						<p class="help-block">
							<button id="simpleFullDescriptionToggle" class="btn btn-mini btn-info">add an extended description</button>
						</p>
					</div>
				</div>

				<div class="control-group hide">
					<label class="control-label" for="simpleFullDescription">Full Collection Description</label>
					<div class="controls">
							<textarea rows="5" class="input-xxlarge" name="simpleFullDescription" id="simpleFullDescription"><xsl:value-of select="$simpleFullDescription"/></textarea>
						<p class="help-block">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simpleRecordGroup">* Group/Institution Name</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" field-bind="ro:collection/@group" class="input-large" name="simpleRecordGroup" value="{$simpleRecordGroup}"/>
						</div>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="simple_collectionTitle">* Type of Collection</label>
					<div class="controls">
						<div class="input-prepend">
							<button class="btn triggerTypeAhead" type="button">
								<i class="icon-chevron-down"/>
							</button>
							<input type="text" field-bind="ro:collection/@type" class="input-large" name="simpleRecordType" value="{$simpleRecordType}"/>
						</div>
						<p class="help-inline">
							<small></small>
						</p>
					</div>
				</div>



				<hr/>
				<h4>About the Data</h4>

				<div class="split-left">
					<div class="control-group">
						<h5>How is the data identified?</h5>
						<label class="control-label" for="simple_briefDescription">* Identifier:						</label>

						<div class="controls">
							<div class="input-prepend">
								<button class="btn triggerTypeAhead" type="button">
									<i class="icon-chevron-down"/>
								</button>
								<input type="text" field-bind="ro:collection/ro:identifier/@type" class="input-mini" name="simpleRecordIdentifierType" value="{$simpleRecordIdentifierType}" placeholder="type"/>
							</div>

							<input type="text" field-bind="ro:collection/ro:identifier" class="input-medium" name="simpleRecordIdentifier" value="{$simpleRecordIdentifier}" placeholder="identifier value"/>		
						</div>

						<div>

							<p class="pull-right" style="margin-right:18px;">
								<button class="btn btn-mini pull-right" id="simpleAddMoreIdentifiers">
									<i class="icon-plus"></i> more
								</button><br/>
								<button class="btn btn-mini btn-info" style="margin-top:8px;" id="simpleAddMoreIdentifiers">
									<i class="icon-wrench icon-white"></i> No identifier?
								</button>
							</p>
						</div>

					</div>
					
				</div>

				<div class="split-right">
					<div class="control-group">
						<h5>What time period does the data cover?</h5>
						<label class="control-label" for="simple_briefDescription">Data Start Date</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned"
									value="{ro:collection/@dateAccessioned}"/>
								<button class="btn triggerDatePicker" type="button">
									<i class="icon-calendar"/>
								</button>
								<p class="help-inline">
									<small/>
								</p>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="simple_briefDescription">Data End Date</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned"
									value="{ro:collection/@dateAccessioned}"/>
								<button class="btn triggerDatePicker" type="button">
									<i class="icon-calendar"/>
								</button>
								<p class="help-inline">
									<small/>
								</p>
							</div>
						</div>
					</div>
				</div>

				<div class="clear"><br/></div>


				<div class="split-left">
					<div class="control-group">
						<h5>Field(s) of Research</h5>
						<label class="control-label" for="simpleFORSubject">* FOR Category:</label>
						<div class="controls">
							<input type="text" class="input-medium" name="simpleFORSubject"
								value="{ro:collection/@dateAccessioned}"/>
							
							<button class="btn btn-mini" style="margin-left:8px;" id="simpleAddMoreIdentifiers">
								<i class="icon-plus"></i> more
							</button>

							<p class="help-block">
								<small>Select the most specific category that applies</small>
							</p>
						</div>
					</div>
				</div>

				<div class="split-right">
					<div class="control-group">
						<h5><br/></h5>
						<label class="control-label" for="simpleKeywords">Subject Keywords:</label>
						<div class="controls">
							<input type="text" class="input-medium" name="simpleFORSubject"
								value="{ro:collection/@dateAccessioned}"/>
							
							<button class="btn btn-mini" style="margin-left:8px;" id="simpleAddMoreIdentifiers">
								<i class="icon-plus"></i> more
							</button>

							<p class="help-block">
								<small>Any topical keywords that will assist searching</small>
							</p>
						</div>
					</div>
				</div>

			<div class="center_footer">
				<button class="btn btn-primary pull-right" id="simpleAddMoreIdentifiers">
					<i class="icon-share-alt icon-white"></i> Proceed to Link your Data (Step 2)
				</button>
			</div>
			</fieldset>

			<div class="clear"></div>
		</div>

	</xsl:template>


	<xsl:template name="recordAdminTab">
		<xsl:variable name="dataSourceTitle"><xsl:value-of select="//extRif:dataSourceTitle"/></xsl:variable>

		<!-- Record Admin-->
		<div id="admin" class="pane">
			<fieldset>
				<legend>Record Administration  <sup><a class="muted" href=" http://ands.org.au/guides/cpguide/" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:variable name="ro_type">
					<xsl:apply-templates select="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type"/>
				</xsl:variable>
				<xsl:variable name="dateModified">
					<xsl:apply-templates select="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified"/>
				</xsl:variable>

				<div class="control-group">
					<label class="control-label" for="ds">
						Data Source
						<sup><a class="muted" href="http://ands.org.au/guides/cpguide/cpgdsaaccount.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
					</label>
					<div class="controls">
						<input type="text" id="data_source_title" class="input-large" name="ds" value="{$dataSourceTitle}" disabled="disabled"/>
					</div>
				</div>

				<div class="control-group warning">
					<label class="control-label" for="key">						
						Key
						<sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgkey.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
					</label>
					<div class="controls">
						<input type="text" class="input-xlarge" required="" name="key" value="{ro:key}"/>
						<button class="btn btn" id="generate_random_key">
							<i class="icon-refresh"/> Generate Random Key </button>
						<p class="help-inline">
							<small>Key must be unique and is case sensitive</small>
						</p>
					</div>
				</div>
				
				<div class="control-group hide">
					<label class="control-label" for="originatingSource">Originating Source</label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" id="originatingSource" name="originatingSource" placeholder="Value" value="{ro:originatingSource/text()}" class="inner_input"/>
							<input type="text" id="originatingSource" class="inner_input_type rifcs-type" vocab="RIFCSOriginatingSourceType" name="originatingSourceType" placeholder="Type"  value="{ro:originatingSource/@type}"/>
						</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="class">
						Class
						<sup><a class="muted" href="http://ands.org.au/guides/cpguide/cpgdsaaccount.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
					</label>

					<div class="controls">
						<input type="text" class="input" name="class" disabled="disabled" value="{$ro_class}" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="type">Type</label>
					<div class="controls">
						<input type="text" id="{generate-id()}_value" class="rifcs-type" required="" vocab="{concat('RIFCS',$ro_class,'Type')}" name="type" value="{$ro_type}"/>
						<p class="help-inline">
							<small/>
						</p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="group">
						Group
						<sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpggroup.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
					</label>
					<div class="controls">
						<input type="text" class="rifcs-type rifcs-type-loading" vocab="GroupSuggestor" name="group" required="" value="{@group}"/>
					</div>
				</div>


				<div class="control-group">
					<label class="control-label" for="date_modified">
						Date Modified
						<sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgdatemod.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
					</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-large datepicker" name="date_modified" value="{$dateModified}"/>
						</div>
					</div>
				</div>

				<xsl:if test="ro:collection">
					<div class="control-group">
						<label class="control-label" for="date_accessioned">
							Date Accessioned
							<sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgdateaccessioned.html" target="_blank" title="View Content Providers' Guide">?</a></sup>
						</label>
						<div class="controls">
							<div class="input-append">
								<input type="text" class="input-large datepicker" name="date_accessioned" value="{ro:collection/@dateAccessioned}"/>
							</div>
						</div>
					</div>
				</xsl:if>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="recordQATab">
		<xsl:param name="registry_object_id"/>
		<xsl:param name="base_url"/>
		<div id="qa" class="pane">
			<fieldset>
				<div id="response_result"></div>
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="namesTab">
		<div id="names" class="pane">
			<fieldset>
				<legend>Names <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgname.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>

				<xsl:apply-templates
					select="ro:collection/ro:name | ro:activity/ro:name | ro:party/ro:name  | ro:service/ro:name"/>
				<div class="separate_line"/>

				<button class="btn btn-primary addNew" type="name" add_new_type="name">
					<i class="icon-plus icon-white"/> Add Name </button>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="datesTab">
		<div id="dates" class="pane">
			<fieldset>
				<legend>Dates <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgdates.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>

				<xsl:apply-templates
					select="ro:collection/ro:dates | ro:activity/ro:dates | ro:party/ro:dates  | ro:service/ro:dates"/>
				<div class="separate_line"/>

				<button class="btn btn-primary addNew" type="dates" add_new_type="dates">
					<i class="icon-plus icon-white"/> Add Dates </button>
				
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:dates | ro:activity/ro:dates | ro:party/ro:dates  | ro:service/ro:dates">
		<div class="aro_box" type="dates">

			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				<div class="controls">
					<input type="text" class="input-small rifcs-type" vocab="RIFCSDatesType" name="type" placeholder="Date Type" value="{@type}"/>
				</div>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<xsl:apply-templates select="ro:date" mode="dates" />
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="dates_date" add_new_type="dates_date">
						<i class="icon-plus icon-white"></i> Add new Date
				</button>
			</div>
			
		</div>
	</xsl:template>
	
	<xsl:template match="ro:date" mode="dates">
		<div class="aro_box_part" type="dates_date">
			<div class="control-group">
				<label class="control-label" for="title">Date: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input datepicker" value="{text()}"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" required="" name="type" placeholder="Type" value="{@type}"/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
				</div>
			</div>
		</div>
	</xsl:template>					
						
	<xsl:template match="ro:collection/@type | ro:activity/@type | ro:party/@type  | ro:service/@type">
		<xsl:value-of select="."/>
	</xsl:template>
	
	<xsl:template match="ro:collection/@dateModified | ro:activity/@dateModified | ro:party/@dateModified  | ro:service/@dateModified">
		<xsl:value-of select="."/>
	</xsl:template>
	

	<xsl:template match="ro:collection/ro:name | ro:activity/ro:name | ro:party/ro:name  | ro:service/ro:name">
		<div class="aro_box" type="name">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSNameType" name="type" placeholder="Type" value="{@type}"/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<xsl:apply-templates select="ro:namePart"/>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="namePart" add_new_type="namePart">
					<i class="icon-plus icon-white"></i> Add Name Part
				</button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:namePart">
		<xsl:param name="hide" select="'hide'"/>
		<div class="aro_box_part" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input" value="{text()}"/>

						<xsl:if test="//ro:party or parent::ro:contributor">
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value="{@type}"/>
						</xsl:if>
					</span>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template name="descriptionRightsTab">
		<div id="descriptions_rights" class="pane">
			<fieldset>
				<legend>Descriptions  <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgdescription.html" target="_blank" title="View Content Providers' Guide">?</a></sup> / Rights <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgrights.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
					select="ro:collection/ro:description | ro:activity/ro:description | ro:party/ro:description  | ro:service/ro:description"/>
				<xsl:apply-templates
					select="ro:collection/ro:rights | ro:activity/ro:rights | ro:party/ro:rights  | ro:service/ro:rights"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="description" add_new_type="description">
					<i class="icon-plus icon-white"/> Add Description </button>
				<button class="btn btn-primary addNew" type="rights" add_new_type="rights">
					<i class="icon-plus icon-white"/> Add Rights </button>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="accesspolicyTab">
		<div id="accesspolicies" class="pane">
			<fieldset>
				<legend>Access Policy <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgservice-accesspolicy.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates select="ro:service/ro:accessPolicy"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="accessPolicy" add_new_type="accessPolicy">
					<i class="icon-plus icon-white"/> Add Access Policy </button>

			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="citationInfoTab">
		<div id="citationInfos" class="pane">
			<fieldset>
				<legend>Citation Information <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgcitation.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates select="ro:collection/ro:citationInfo"/>
				<div class="separate_line"/>
				<div class="btn-group">
					<button class="btn btn-primary addNew" type="fullCitation" add_new_type="fullCitation">Add Full Citation</button>
					<button class="btn btn-primary addNew" type="citationMetadata" add_new_type="citationMetadata">Add Citation Metadata</button>
					
				</div>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:citationInfo">
		<xsl:apply-templates select="ro:fullCitation"/>
		<xsl:apply-templates select="ro:citationMetadata"/>
	</xsl:template>

	<xsl:template match="ro:fullCitation">
		<div class="aro_box" type="fullCitation">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"></i></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSCitationStyle" name="style" placeholder="Style" value="{@style}"/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				<h1>Full Citation</h1>
			</div>
			<textarea name="value" place-holder="value" rows="6"><xsl:value-of select="text()"/></textarea>
		</div>
	</xsl:template>

	<xsl:template match="ro:citationMetadata">
		<div class="aro_box" type="citationMetadata">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"></i></button><h1>Citation Metadata</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="identifier">
				<div class="control-group">
				<label class="control-label">Identifier:</label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input" name="value" placeholder="Identifier" value="{ro:identifier/text()}" required=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSCitationIdentifierType" name="type" placeholder="Type" value="{ro:identifier/@type}"/>
						</span>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="title">
				<div class="control-group">
				<label class="control-label">Title:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Title" value="{ro:title/text()}" required=""/>
					</div>
				</div>
			</div>


			<div class="aro_box_part" type="version">
				<div class="control-group">
				<label class="control-label">Version:</label>
					<div class="controls">
						<xsl:if test="ro:edition">
							<input type="text" class="input-xlarge" name="value" placeholder="Edition" value="{ro:edition/text()}"/>
						</xsl:if>
						<xsl:if test="ro:version">
							<input type="text" class="input-xlarge" name="value" placeholder="Version" value="{ro:version/text()}"/>
						</xsl:if>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="placePublished">
				<div class="control-group">
				<label class="control-label">Place Published:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Place Published" value="{ro:placePublished/text()}"/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="publisher">
				<div class="control-group">
				<label class="control-label">Publisher:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Publisher" value="{ro:publisher/text()}" required=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="url">
				<div class="control-group">
				<label class="control-label">URL:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" valid-type="url" placeholder="URL" value="{ro:url/text()}"/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="context">
				<div class="control-group">
				<label class="control-label">Context:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Context" value="{ro:context/text()}"/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="contributor">
				<div class="control-group">
				<label class="control-label">Contributors:</label>
					<div class="controls">
						<div class="aro_box_part">
							<xsl:apply-templates select="ro:contributor"/>
							<div class="separate_line"/>
							<button class="btn btn-primary addNew" type="contributor" add_new_type="contributor">
								<i class="icon-plus icon-white"></i> Add Contributor
							</button>
							<hr/>
						</div>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="citation_date">
				<div class="control-group">
				<label class="control-label">Dates:</label>
					<div class="controls">
						<div class="aro_box_part">
							<xsl:apply-templates select="ro:date" mode="citation"/>
							<div class="separate_line"/>
							<button class="btn btn-primary addNew" type="date" add_new_type="citation_date">
								<i class="icon-plus icon-white"></i> Add Date
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:contributor">
		<div class="aro_box_part" type="contributor">
		Contributor: <input type="text" class="input-small" name="seq" placeholder="Seq" value="{@seq}"/>
			<xsl:if test="not(ro:namePart)">
				<div class="aro_box_part" type="namePart">
					<div class="control-group">
						<label class="control-label" for="title">Name Part: </label>
						<div class="controls">
							<span class="inputs_group">
								<input type="text" name="value" class="inner_input" value=""/>
								<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
							</span>
							<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
						</div>
					</div>
				</div>
			</xsl:if>
			<xsl:apply-templates select="ro:namePart">
				<xsl:with-param name="hide" select="''"/>
			</xsl:apply-templates>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="contributor_namePart" add_new_type="contributor_namePart"><i class="icon-plus icon-white"/> Add Name Part </button>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> Remove this contributor</button>
			</div>
			<hr/>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:description | ro:activity/ro:description | ro:party/ro:description  | ro:service/ro:description">
		<div class="aro_box" type="description">
			<div class="aro_box_display clearfix">
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDescriptionType" name="type" placeholder="Type" value="{@type}"/>
				<h1>Description</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<textarea name="value" class="editor">
				<xsl:value-of disable-output-escaping="yes" select="text()"/>
			</textarea>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:rights | ro:activity/ro:rights | ro:party/ro:rights  | ro:service/ro:rights">
		<div class="aro_box" type="rights">
			<div class="aro_box_display clearfix">
				<h1>Rights</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="rightsStatement">
				<label>Rights Statement</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:rightsStatement/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:rightsStatement/text()}"/>
			</div>
			<div class="aro_box_part" type="licence">
				<label>Licence</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:licence/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:licence/text()}"/>
				<input type="text" class="input-xlarge rifcs-type" vocab="RIFCSLicenceType" name="type" placeholder="Type" value="{ro:licence/@type}"/>				
			</div>		
			<div class="aro_box_part" type="accessRights">
				<label>Access Rights</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value="{ro:accessRights/@rightsUri}"/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value="{ro:accessRights/text()}"/>				
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSAccessRightsType" name="type" placeholder="Type" value="{ro:accessRights/@type}"/>
			</div>
		</div>
	</xsl:template>

	<xsl:template name="subjectsTab">
		<div id="subjects" class="pane">
			<fieldset>
				<legend>Subjects <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgsubject.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
					select="ro:collection/ro:subject | ro:activity/ro:subject | ro:party/ro:subject  | ro:service/ro:subject"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="subject" add_new_type="subject">
					<i class="icon-plus icon-white"/> Add Subject </button>
				
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template name="identifiersTab">
		<div id="identifiers" class="pane">
			<fieldset>
				<legend>Identifiers <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgidentifiers.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
					select="ro:collection/ro:identifier | ro:activity/ro:identifier | ro:party/ro:identifier  | ro:service/ro:identifier"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="identifier" add_new_type="identifier">
					<i class="icon-plus icon-white"/> Add Identifier </button>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="relatedObjectsTab">
		<div id="relatedObjects" class="pane">
			<fieldset>
				<legend>Related Objects 
					<xsl:if test="count(//ro:relatedObject) > $maxRelatedDisp">
					(<xsl:value-of select="count(//ro:relatedObject)"/>) <br/> No More than <xsl:value-of select="$maxRelatedDisp"/> related Objects can be displayed
					</xsl:if>
					<sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgrelatedobject.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
					<xsl:for-each select="ro:collection/ro:relatedObject | ro:activity/ro:relatedObject | ro:party/ro:relatedObject  | ro:service/ro:relatedObject">
						<xsl:if test="position() &lt;= $maxRelatedDisp">
							<xsl:apply-templates select="." mode="form"/>
						</xsl:if>
					</xsl:for-each>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedObject" add_new_type="relatedObject">
					<i class="icon-plus icon-white"/> Add Related Object </button>
				
			</fieldset>
		</div>
		<xsl:if test="count(//ro:relatedObject) > $maxRelatedDisp">
			<div id="relatedObjects_overflow">
				<xsl:for-each select="ro:collection/ro:relatedObject | ro:activity/ro:relatedObject | ro:party/ro:relatedObject  | ro:service/ro:relatedObject">
					<xsl:if test="position() &gt; $maxRelatedDisp">
						<xsl:apply-templates select="." mode="data"/>
					</xsl:if>
				</xsl:for-each>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template name="relatedinfosTab">
		<div id="relatedinfos" class="pane">
			<fieldset>
				<legend>Related Information <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgrelatedinfo.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
					select="ro:collection/ro:relatedInfo | ro:activity/ro:relatedInfo | ro:party/ro:relatedInfo | ro:service/ro:relatedInfo"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="relatedInfo" add_new_type="relatedInfo">
					<i class="icon-plus icon-white"/> Add Related Info </button>
				
			</fieldset>
		</div>
	</xsl:template>

	<xsl:template name="locationsTab">
		<div id="locations" class="pane">
			<div class='well'>
				Do not describe collection coverage here. Please use the Coverage tab instead.
			</div>
			<fieldset>
				<legend>Locations <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpglocationintro.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
					select="ro:collection/ro:location | ro:activity/ro:location | ro:party/ro:location  | ro:service/ro:location"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="location" add_new_type="location">
					<i class="icon-plus icon-white"/> Add Location </button>
				
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="coverageTab">
		<div id="coverages" class="pane">
			<div class="well">
				The Coverage element should be used to record the temporal and/or spatial coverage of the collection. <br/>
				To record dates associated with an event in the life cycle of the collection (eg. date created) please use the Dates tab.
			</div>
			<fieldset>
				<legend>Coverage <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgcoverage.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates
				select="ro:collection/ro:coverage | ro:activity/ro:coverage | ro:party/ro:coverage | ro:service/ro:coverage"/>
				<div class="aro_box" type="coverage">
					<div class="separate_line"/>
					<div class="btn-group">
						<button class="btn btn-primary addNew" type="temporal" add_new_type="temporal"><i class="icon-white icon-plus"></i> Add Temporal Coverage</button>
						<button class="btn btn-primary addNew" type="spatial" add_new_type="spatial"><i class="icon-white icon-plus"></i> Add Spatial Coverage</button>
					</div>
				</div>
			</fieldset>
		</div>
	</xsl:template>
	
	<xsl:template name="ExistenceDatesTab">
		<div id="existencedates" class="pane">
			<fieldset>
				<legend>Existence Dates <sup><a class="muted" href="http://www.ands.org.au/guides/cpguide/cpgexistencedates.html" target="_blank" title="View Content Providers' Guide">?</a></sup></legend>
				<xsl:apply-templates select="ro:activity/ro:existenceDates | ro:party/ro:existenceDates  | ro:service/ro:existenceDates"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="existenceDates" add_new_type="existenceDates">
					<i class="icon-plus icon-white"/> Add Existence Date </button>
				
			</fieldset>
		</div>
	</xsl:template>


	<xsl:template match="ro:activity/ro:existenceDates | ro:party/ro:existenceDates  | ro:service/ro:existenceDates">
		<div class="aro_box" type="existenceDates">
			<div class="aro_box_display clearfix">
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part" type="startDate">
				<div class="control-group">
					<label class="control-label">Start Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker"  name="value" placeholder="startDate Value" value="{ro:startDate/text()}"/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="startDate dateFormat" value="{ro:startDate/@dateFormat}"/>
						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="endDate">
				<div class="control-group">
					<label class="control-label">End Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker" name="value" placeholder="endDate Value" value="{ro:endDate/text()}"/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="endDate dateFormat" value="{ro:endDate/@dateFormat}"/>
						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
	
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:relatedInfo | ro:activity/ro:relatedInfo | ro:party/ro:relatedInfo | ro:service/ro:relatedInfo">
		<div class="aro_box" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSRelatedInformationType" name="type" placeholder="Type" value="{@type}"/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" placeholder="Title" value="{ro:title/text()}"/>
					</div>
				</div>
				<xsl:apply-templates select="ro:identifier"/>
				<div class="separate_line"/>
				<div class="controls">
					<button class="btn btn-primary addNew" type="relatedInfo_identifier" add_new_type="relatedInfo_identifier"><i class="icon-plus icon-white"/> Add Identifier </button>
				</div>
				<xsl:apply-templates select="ro:relation"/>
				<div class="separate_line"/>
				<div class="controls">
					<button class="btn btn-primary addNew" type="relation" add_new_type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
				</div>

				<div class="control-group">
					<xsl:choose>
						<xsl:when test="ro:format">
							<xsl:apply-templates select="ro:format"/>
						</xsl:when>
						<xsl:otherwise>
							<div class="control-group">
								<label class="control-label" for="title">Format: </label>
									<div class="controls">
										<div class="separate_line"/>
										<button class="btn btn-primary addNew" type="format_identifier" add_new_type="format_identifier">
											<i class="icon-plus icon-white"></i> Add Format Identifier
										</button>
									</div>
							</div>
						</xsl:otherwise>
					</xsl:choose>
				</div>
				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{ro:notes/text()}"/>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:collection/ro:subject  | ro:activity/ro:subject  | ro:party/ro:subject   | ro:service/ro:subject">
		<div class="aro_box" type="subject">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Subject: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<div class="controls">
				
				<span class="inputs_group">
					<input type="text" class="input-xlarge inner_input" placeholder="Value" value="{text()}" name="value" required="" vocab="RIFCSSubject"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSubjectType" name="type" placeholder="type" value="{@type}" required=""/>
				</span>
				  Term Identifier: <input type="text" class="input-xlarge" vocab="RIFCSSubjectTermIdentifier" name="termIdentifier" placeholder="termIdentifier" value="{@termIdentifier}"/>
				</div>
			</div>
		</div>


	</xsl:template>

	<xsl:template match="ro:collection/ro:identifier  | ro:activity/ro:identifier  | ro:party/ro:identifier   | ro:service/ro:identifier">
		<div class="aro_box" type="identifier">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="input-xlarge inner_input" placeholder="Value"  value="{text()}" name="value" required=""/>
						<input type="text" class="inner_input_type rifcs-type identifierType" vocab="RIFCSIdentifierType" name="type" placeholder="type" value="{@type}"/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:relatedObject | ro:activity/ro:relatedObject| ro:party/ro:relatedObject | ro:service/ro:relatedObject" mode="form">
		<div class="aro_box" type="relatedObject">
			<div class="aro_box_display clearfix">
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-xlarge" name="key" value="{ro:key}" placeholder="Related Object Key" required=""/>
							<button class="btn search_related_btn" type="button"><i class='icon icon-search'></i> Search</button>
						</div>
					</div>
				</div>
			</div>

			<xsl:apply-templates select="ro:relation"/>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="relation" add_new_type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:relatedObject | ro:relatedObject| ro:relatedObject | ro:relatedObject" mode="data">
			<xsl:copy-of select="."/>
	</xsl:template>

	<xsl:template match="ro:collection/ro:location | ro:activity/ro:location | ro:party/ro:location  | ro:service/ro:location">
		<div class="aro_box" type="location">

			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				Date From: 
				<div class="input-append">
					<input type="text" class="input-large datepicker" name="dateFrom" placeholder="dateFrom" value="{@dateFrom}"/>
				</div>
					Date To: 
				<div class="input-append">
				<input type="text" class="input-large datepicker" name="dateTo" placeholder="dateTo" value="{@dateTo}"/>
				</div>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_subbox" type="address">
				<h1>Address</h1>
				<xsl:apply-templates select="ro:address"/>
				<div class="separate_line"/>
				<div class="btn-group">
					<button class="btn btn-primary addNew" type="electronic" add_new_type="electronic"><i class="icon icon-white icon-plus"></i> Electronic Address</button>
					<button class="btn btn-primary addNew" type="physical" add_new_type="physical"><i class="icon icon-white icon-plus"></i> Physical Address</button>
				</div>
			</div>

			<div class="aro_subbox" type="spatial">
				<h1>Spatial Location</h1>
				<xsl:apply-templates select="ro:spatial"/>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial" add_new_type="spatial"><i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>

		</div>
	</xsl:template>

	<xsl:template match="ro:collection/ro:coverage | ro:activity/ro:coverage | ro:party/ro:coverage  | ro:service/ro:coverage">
		<div class="aro_box" type="coverage">
			<xsl:apply-templates select="ro:temporal"/>
			<xsl:apply-templates select="ro:spatial"/>
		</div>
	</xsl:template>


	<xsl:template match="ro:temporal">
		<div class="aro_box_part" type="temporal">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Temporal Coverage: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<xsl:apply-templates select="ro:date" mode="coverage"/>
				<xsl:apply-templates select="ro:text"/>
				<div class="separate_line"/>
				<div class="controls">
					<div class="btn-group">
						<button class="btn btn-mini btn-primary addNew" type="coverage_date" add_new_type="coverage_date"><i class="icon-white icon-plus"></i> Date</button>
						<button class="btn btn-mini btn-primary addNew" type="text" add_new_type="text"><i class="icon-white icon-plus"></i> Text</button>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:date" mode="coverage">
		<div class="aro_box_part" type="coverage_date">
			<div class="control-group">
				<label class="control-label" for="title">Date: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value="{text()}"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value="{@type}"/>
					</span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="Date Format" value="{@dateFormat}"/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
	</xsl:template>
	



	<xsl:template match="ro:date">
		<div class="aro_box_part" type="date">
			<span class="inputs_group">
				<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value="{text()}"/>
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value="{@type}"/>
			</span>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>
	</xsl:template>

	<xsl:template match="ro:date" mode="citation">
		<div class="aro_box_part" type="citation_date">
			<span class="inputs_group">
				<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value="{text()}"/>
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSCitationDateType" name="type" placeholder="Date Type" value="{@type}"/>
			</span>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>
	</xsl:template>
	
	<xsl:template match="ro:text">
		<div class="aro_box_part" type="text">
			<div class="control-group">
				<label class="control-label" for="title">Text: </label>
				<div class="controls">
					<input type="text" class="input-large" name="value" placeholder="Date Value" value="{text()}"/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:relation">
		<div class="aro_box_part" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value="{@type}"/>
					<input type="text" class="inner_input input-large" name="description" placeholder="Description" value="{ro:description}"/>
					<input type="text" class="input-small" name="url" valid-type="url" placeholder="URL" value="{ro:url}"/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:spatial">
		<div class="aro_box_part" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="inner_input spatial_value" name="value" placeholder="Value" value="{text()}"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSpatialType" name="type" placeholder="Type" value="{@type}"/>
					</span>
					<button class="btn triggerMapWidget" type="button"><i class="icon-globe"></i></button>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:address">
		<xsl:apply-templates select="ro:electronic | ro:physical"/>
		<div class="separate_line"/>
	</xsl:template>


	<xsl:template match="ro:electronic">
		<div class="aro_box_part" type="electronic">
            <div class="control-group">
                <div class="aro_box_display clearfix">
                    <label class="control-label" for="title">Electronic Address: </label>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
                
            	<div class="control-group">
            		<label class="control-label" for="value">Value: </label>
            		<div class="controls">
            			<span class="inputs_group">
                		    <input type="text" class="inner_input" name="value" placeholder="Value" value="{ro:value}" valid-type="url"/>
                		    <input type="text" class="inner_input_type rifcs-type" vocab="RIFCSElectronicAddressType" name="type" placeholder="Type" value="{@type}"/>
                		</span>
                        <xsl:if test="ancestor::ro:service">
                            <button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
                            <div class="parts hide">
                                <xsl:apply-templates select="ro:arg"/>
                                <div class="separate_line"/>
                                <button class="btn btn-primary addNew" type="arg" add_new_type="arg">
                                    <i class="icon-plus icon-white"></i> Add Args
                                </button>
                            </div>
                        </xsl:if>
            		</div>
            	</div>
                <div class="separate_line"/>
                
                
                <xsl:if test="ancestor::ro:collection">
                    <div class="control-group">
                        <label class="control-label" for="Title">Target: </label>
                        <div class="controls">
                            <xsl:choose>
                                <xsl:when test="@target">
                                    <span class="inputs_group">
                                        <input type="text" class="input_large rifcs-type" vocab="RIFCSElectronicAddressTarget" name="target" placeholder="Target" value="{@target}"/>
                                    </span>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="inputs_group">
                                        <input type="text" class="input_large rifcs-type" vocab="RIFCSElectronicAddressTarget" name="target" placeholder="Target" value=""/>
                                    </span>
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </div>

                    <div class="separate_line"/>
                	<div class="control-group">
                		<label class="control-label" for="value">Title: </label>
                		<div class="controls">
                			<xsl:choose>
	                            <xsl:when test="ro:title">
	                                <span class="inputs_group">
	                                    <input type="text" class="input-large" name="title" placeholder="Title" value="{ro:title}"/>
	                                </span>
	                            </xsl:when>
	                            <xsl:otherwise>
	                                <span class="inputs_group">
	                                    <input type="text" class="input-large" name="title" placeholder="Title" value=""/>
	                                </span>
	                            </xsl:otherwise>
	                        </xsl:choose>
                		</div>
                	</div>
                   
                    <div class="separate_line"/>

					<xsl:choose>
                        <xsl:when test="ro:notes">
                            <xsl:apply-templates select="ro:notes"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="control-group">
                                <label for="" class="control-label">Note: </label>
                                <div class="controls">
                                    <input type="text" class="input-xlarge" name="notes" placeholder="Notes" value=""/>
                                </div>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <div class="separate_line"/>
                    <div class="control-group">                            
                        <div class="controls">
                            <button class="btn btn-primary addNew" type="electronic_addr_notes" add_new_type="electronic_addr_notes">
                                <i class="icon-plus icon-white"></i> Add Notes
                            </button>
                        </div>
                    </div>
                   

                    <xsl:choose>
                        <xsl:when test="ro:mediaType">
                            <xsl:apply-templates select="ro:mediaType"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="control-group">
                                <label for="" class="control-label">Media Type: </label>
                                <div class="controls">
                                    <input type="text" class="input-large" name="mediaType" placeholder="Media Type" value="{text()}"/>
                                </div>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>

                    <div class="separate_line"/>
                    <div class="control-group">
                        <div class="controls">
                            <button class="btn btn-primary addNew" type="mediaType" add_new_type="mediaType">
                                <i class="icon-plus icon-white"></i> Add Media Type
                            </button>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="mediaType">Byte Size: </label>
                        <div class="controls">
                            <xsl:choose>
                                <xsl:when test="ro:byteSize">
                                    <span class="inputs_group">
                                        <input type="text" class="input-small" name="byteSize" placeholder="Byte Size" value="{ro:byteSize}"/>
                                    </span>
                                </xsl:when>
                                <xsl:otherwise>
                                    <span class="inputs_group">
                                        <input type="text" class="input-small" name="byteSize" placeholder="Byte Size" value=""/>
                                    </span>
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </div>
                </xsl:if>
            </div>
		</div>
	</xsl:template>

	<xsl:template match="ro:physical">
		<div class="aro_box_part" type="physical">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Physical Address: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<div class="controls">
					<input type="text" class="input rifcs-type" vocab="RIFCSPhysicalAddressType" name="type" placeholder="Type" value="{@type}"/>
					<xsl:apply-templates select="ro:addressPart"/>
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="addressPart" add_new_type="addressPart">
						<i class="icon-plus icon-white"></i> Add Address Part
					</button>
				</div>
			</div>

		</div>
	</xsl:template>

    <xsl:template match="ro:notes">
        <div class="aro_box_part">
            <div class="control-group">
                <label for="" class="control-label">Note: </label>
                <div class="controls">
                    <input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{text()}"/>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="ro:mediaType">
        <div class="aro_box_part">
        	<div class="control-group">
                <label for="" class="control-label">Media Type: </label>
                <div class="controls">
                    <input type="text" class="input-large" name="mediaType" placeholder="Media Type" value="{text()}"/>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
            </div>
        </div>
    </xsl:template>

	<xsl:template match="ro:arg">
		<div class="aro_box_part" type="arg">
			<label class="control-label" for="title">Arg: </label>
			<div class="control-group">
				<span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value="{@required}"/>
				<span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgUse" name="use"  placeholder="Use" value="{@use}"/>
				</span>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value="{text()}"/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>
	</xsl:template>


<!--
    <input type="text" class="input-small" name="title" placeholder="Title" value="{ro:title}"/>
    <input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{ro:title}"/>
    <input type="text" class="input-large" name="mediaType" placeholder="Media ype" value="{ro:mediaType}"/>
    <input type="text" class="input-small" name="byteSize" placeholder="Byte Size" value="{ro:byteSize}"/>
-->



    <xsl:template match="ro:addressPart">
		<div class="aro_box_part" type="addressPart">
			<div class="control-group">
				<span class="inputs_group">
					<input type="text" class="inner_input" name="value" placeholder="value" value="{text()}"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSPhysicalAddressPartType" name="type" placeholder="Type" value="{@type}"/>
				</span>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="ro:accessPolicy">
		<div class="aro_box" type="accessPolicy">
			<input type="text" class="input-xlarge" name="value" placeholder="value" value="{text()}"/>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>
	</xsl:template>


	<xsl:template match="ro:format">
		<label class="control-label" for="title">Format: </label>
			<div class="controls">
				<xsl:apply-templates select="ro:identifier"/>				
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="format_identifier" add_new_type="format_identifier">
				<i class="icon-plus icon-white"></i> Add Format Identifier
				</button>
			</div>
	</xsl:template>


	<xsl:template match="ro:relatedInfo/ro:identifier">
		<div class="aro_box_part">
				<div class="control-group">
					<div class="controls">
					<label class="control-label" for="Identifier">Identifier:</label>						
					<span class="inputs_group">
						<input type="text" class="inner_input input-large" name="identifier" placeholder="Identifier" value="{text()}" required=""/>
						<input type="text" class="inner_input_type rifcs-type identifierType" vocab="RIFCSRelatedInformationIdentifierType" name="identifier_type" placeholder="Type" value="{@type}"/>
					</span>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>	
				</div>
			</div>
		</div>
	</xsl:template>


	<xsl:template match="ro:format/ro:identifier">
		<div class="aro_box_part">
			<div class="aro_box_part clearfix" type="format_identifier">
				<label class="control-label" for="title"></label>						
				<span class="inputs_group">
					<input type="text" class="inner_input input-large" name="format_identifier" placeholder="Format Identifier" value="{text()}" required=""/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSRelatedInformationIdentifierType" name="format_identifier_type" placeholder="Type" value="{@type}"/>
				</span>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>	
			</div>
		</div>
	</xsl:template>



	<!-- BLANK TEMPLATE -->
	<xsl:template name="blankTemplate">
		<div class="aro_box template" type="name">

			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSNameType" name="type" placeholder="Type" value=""/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part" type="namePart">
				<div class="control-group">
					<label class="control-label" for="title">Name Part: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input" value="" placeholder="Name Part Value"/>

							<xsl:if test="//ro:party">
								<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
							</xsl:if>

						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>

			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="namePart" add_new_type="namePart">
					<i class="icon-plus icon-white"></i> Add Name Part
				</button>
			</div>

		</div>

		<div class="aro_box_part template" type="namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input" value="" placeholder="Name Part Value"/>
					
						<xsl:if test="//ro:party">
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
						</xsl:if>

					</span>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>



		<div class="aro_box_part template" type="contributor_namePart">
			<div class="control-group">
				<label class="control-label" for="title">Name Part: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input" value="" placeholder="Name Part Value"/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="description">
			<div class="aro_box_display clearfix">
				<input type="text" class="input-small rifcs-type" vocab="RIFCSDescriptionType" name="type" placeholder="Type" value=""/>
				<h1>Description</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<textarea name="value" class=""/>
		</div>

		<div class="aro_box template" type="rights">	
			<div class="aro_box_display clearfix">
				<h1>Rights</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="rightsStatement">
				<label>Rights Statement</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
			</div>
			<div class="aro_box_part" type="licence">
				<label>Licence</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
				<input type="text" class="input-xlarge rifcs-type" vocab="RIFCSLicenceType" name="type" placeholder="Type" value=""/>
			</div>		
			<div class="aro_box_part" type="accessRights">
				<label>Access Rights</label>
				<input type="text" class="input-xlarge" name="rightsUri" placeholder="Rights Uri" value=""/>
				<input type="text" class="input-xlarge" name="value" placeholder="Value" value=""/>
                <input type="text" class="inner_input_type rifcs-type" vocab="RIFCSAccessRightsType" name="type" placeholder="Type" value=""/>
			</div>
		</div>

		<div class="aro_box template" type="subject">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Subject: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<div class="controls">
				
				<span class="inputs_group">
					<input type="text" class="input-xlarge inner_input" placeholder="Value" value="" name="value" required="" vocab="RIFCSSubject"/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSubjectType" name="type" placeholder="type" value="" required=""/>
				</span>
				Term Identifier: <input type="text" class="input-xlarge" vocab="RIFCSSubjectTermIdentifier" name="termIdentifier" placeholder="termIdentifier" value=""/>
				</div>
			</div>
		</div>



		<div class="aro_box template" type="identifier">
			<div class="aro_box_display clearfix">
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="input-xlarge inner_input" placeholder="Value" value="" name="value" required=""/>
						<input type="text" class="inner_input_type rifcs-type identifierType" vocab="RIFCSIdentifierType" name="type" placeholder="type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
					<p class="help-inline">
						<small/>
					</p>
				</div>
			</div>
		</div>

		<div class="aro_box_part template" type="relatedInfo_identifier">
			<div class="control-group">
				<label class="control-label" for="title">Identifier: </label>	
				<div class="controls">					
					<span class="inputs_group">
						<input type="text" class="inner_input input-large" name="identifier" placeholder="Identifier" value=""/>
						<input type="text" class="inner_input_type rifcs-type identifierType" vocab="RIFCSRelatedInformationIdentifierType" name="identifier_type" placeholder="Type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>	
				</div>
				
			</div>
		</div>

		<div class="aro_box template" type="relatedObject">
			<div class="aro_box_display clearfix">
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part">
				<div class="control-group">
					<label class="control-label" for="title">Key: </label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="input-xlarge" name="key" value="" placeholder="Related Object Key" required=""/>
							<button class="btn search_related_btn" type="button"><i class='icon icon-search'></i> Search</button>
						</div>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="relation">
				<div class="control-group">
					<label class="control-label" for="title">Relation: </label>
					<div class="controls">
						<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value=""/>
						<input type="text" class="inner_input input-large" name="description" placeholder="Description" value=""/>
						<input type="text" class="input-small" name="url" valid-type="url" placeholder="URL" value=""/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
					</div>
				</div>
			</div>

			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="relation" add_new_type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
			</div>
		</div>

		<div class="aro_box_part template" type="relation">
			<div class="control-group">
				<label class="control-label" for="title">Relation: </label>
				<div class="controls">
					<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value=""/>
					<input type="text" class="inner_input input-large" name="description" placeholder="Description" value=""/>
					<input type="text" class="input-small" name="url" valid-type="url" placeholder="URL" value=""/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
				</div>
			</div>
		</div>

		<div class="aro_box template" type="relatedInfo">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSRelatedInformationType" name="type" placeholder="Type" value=""/>
				<h1/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		
			<div class="aro_box_part" type="relatedInfo">
				<div class="control-group">
					<label class="control-label" for="title">Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" placeholder="Title" value=""/>
					</div>
				</div>

			<div class="control-group">
				<label class="control-label" for="title">Identifier: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="inner_input input-large" name="identifier" placeholder="Identifier" value="" required=""/>
						<input type="text" class="inner_input_type rifcs-type identifierType" vocab="RIFCSRelatedInformationIdentifierType" name="identifier_type" placeholder="Identifier Type" value=""/>
					</span>

				</div>
				<div class="separate_line"/>
					<div class="controls">
						<button class="btn btn-primary addNew" type="relatedInfo_identifier" add_new_type="relatedInfo_identifier">
								<i class="icon-plus icon-white"></i> Add Identifier
						</button>
					</div>
			</div>

			

			<div class="aro_box_part" type="relation">
				<div class="control-group">
					<label class="control-label" for="title">Relation: </label>
					<div class="controls">
						<input type="text" class="rifcs-type" vocab="{concat('RIFCS',$ro_class,'RelationType')}" name="type" placeholder="Relation Type" value=""/>
						<input type="text" class="inner_input input-large" name="description" placeholder="Description" value=""/>
						<input type="text" class="input-small" name="url" valid-type="url" placeholder="URL" value=""/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> </button>
					</div>
				</div>
			</div>

			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="relation" add_new_type="relation"><i class="icon-plus icon-white"/> Add Relation </button>
			</div>


				<div class="control-group">
					<label class="control-label" for="title">Format: </label>
					<div class="controls">
						<div class="separate_line"/>
						<button class="btn btn-primary addNew" type="format_identifier" add_new_type="format_identifier">
							<i class="icon-plus icon-white"></i> Add Format
						</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="title">Notes: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="notes" placeholder="Notes" value="{notes/text()}"/>
					</div>
				</div>
			</div>
		</div>

	<div class="aro_box_part template" type="format_identifier">
		<div class="aro_box_part">
			<label class="control-label" for="title"></label>						
			<span class="inputs_group">
				<input type="text" class="inner_input input-large" name="format_identifier" placeholder="Format Identifier" value="" required=""/>
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSRelatedInformationIdentifierType" name="format_identifier_type" placeholder="Type" value=""/>
			</span>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>	
		</div>
	</div>

		<div class="aro_box template" type="location">

			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"/></button>
				Date From: 
				<div class="input-append">
					<input type="text" class="input-large datepicker" name="dateFrom" placeholder="dateFrom" value=""/>
				</div>
				 Date To: 
				<div class="input-append">
					<input type="text" class="input-large datepicker" name="dateTo" placeholder="dateTo" value=""/>
				</div>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_subbox" type="address">
				<h1>Address</h1>
				<div class="separate_line"/>
				<div class="btn-group">
					<button class="btn btn-primary addNew" type="electronic" add_new_type="electronic"><i class="icon icon-white icon-plus"></i> Electronic Address</button>
					<button class="btn btn-primary addNew" type="physical" add_new_type="physical"><i class="icon icon-white icon-plus"></i> Physical Address</button>
				</div>
			</div>

			<div class="aro_subbox" type="spatial">
				<h1>Spatial Location</h1>
				<div class="separate_line"/>
				<button class="btn btn-primary addNew" type="spatial" add_new_type="spatial"><i class="icon-map-marker icon-white"/> Add Spatial Location </button>
			</div>

		</div>

		<div class="aro_box_part template" type="spatial">
			<div class="control-group">
				<label class="control-label" for="title">Spatial: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="inner_input spatial_value" name="value" placeholder="Value" value=""/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSSpatialType" name="type" placeholder="Type" value=""/>
					</span>
					<button class="btn triggerMapWidget" type="button"><i class="icon-globe"></i></button>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>

        <div class="aro_box_part template" type="electronic">
            <div class="control-group">
                <div class="aro_box_display clearfix">
                    <label class="control-label" for="title">Electronic Address: </label>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
               
                <div class="control-group">
                    <label class="control-label" for="value">Value: </label>
                    <div class="controls">
                        <span class="inputs_group">
                            <input type="text" class="inner_input" name="value" placeholder="Value" value="" valid-type="url"/>
                            <input type="text" class="inner_input_type rifcs-type" vocab="RIFCSElectronicAddressType" name="type" placeholder="Type" value=""/>
                        </span>
                        <xsl:if test="//ro:service">
                            <button class="btn btn-primary showParts"><i class="icon-chevron-right icon-white"></i></button>
                            <div class="parts hide">
                                <div class="separate_line"/>
                                <button class="btn btn-primary addNew" type="arg" add_new_type="arg">
                                    <i class="icon-plus icon-white"></i> Add Args
                                </button>
                            </div>
                        </xsl:if>
                    </div>
                </div>
                <div class="separate_line"/>
                
                <xsl:if test="//ro:collection">
                    <div class="control-group">
                        <label class="control-label" for="Title">Target: </label>
                        <div class="controls">
                            <span class="inputs_group">
                                <input type="text" class="input_large rifcs-type" vocab="RIFCSElectronicAddressTarget" name="target" placeholder="Target" value=""/>
                            </span>
                        </div>
                    </div>
                    <div class="separate_line"/>
                    <div class="control-group">
                        <label class="control-label" for="value">Title: </label>
                        <div class="controls">
                            <span class="inputs_group">
                                <input type="text" class="input-large" name="title" placeholder="Title" value=""/>
                            </span>
                        </div>
                    </div>
                   
                    <div class="separate_line"/>
                    <div class="control-group">
                        <label class="control-label" for="value">Notes: </label>
                        <div class="controls">
                            <span class="inputs_group">
                                <input type="text" class="input-xlarge" name="notes" placeholder="Notes" value=""/>
                            </span>
                        </div>
                    </div>
                    
                    <div class="separate_line"/>
                    <div class="control-group">
                        <div class="controls">
                            <button class="btn btn-primary addNew" type="electronic_addr_notes" add_new_type="electronic_addr_notes">
                                <i class="icon-plus icon-white"></i> Add Notes
                            </button>
                        </div>
                    </div>
                   

                    <div class="control-group">
                        <label class="control-label" for="mediaType">Media Type: </label>
                        <div class="controls">
                            <span class="inputs_group">
                                <input type="text" class="input-large" name="mediaType" placeholder="Media Type" value=""/>
                            </span>
                        </div>
                    </div>

                    <div class="separate_line"/>
                    <div class="control-group">
                        <div class="controls">
                            <button class="btn btn-primary addNew" type="mediaType" add_new_type="mediaType">
                                <i class="icon-plus icon-white"></i> Add Media Type
                            </button>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="mediaType">Byte Size: </label>
                        <div class="controls">
                            <span class="inputs_group">
                                <input type="text" class="input-small" name="byteSize" placeholder="Byte Size" value=""/>
                            </span>
                        </div>
                    </div>
                </xsl:if>
                
            </div>
        </div>

        <span class="aro_box_part template" type="mediaType">
            <div class="control-group">
            	<label for="" class="control-label">Media Type: </label>
                <div class="controls">
                    <input type="text" class="input-large" name="mediaType" placeholder="Media Type" value=""/>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
            </div>
        </span>

        <span class="aro_box_part template" type="electronic_addr_notes">
            <div class="control-group">
                <label for="" class="control-label">Note: </label>
                <div class="controls">
                    <input type="text" class="input-xlarge" name="notes" placeholder="Notes" value=""/>
                    <button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
                </div>
            </div>
        </span>

		<div class="aro_box_part template" type="physical">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Physical Address: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<div class="controls">
					<input type="text" class="input rifcs-type" vocab="RIFCSPhysicalAddressType" name="type" placeholder="Address Type" value=""/>	
					<div class="aro_box_part" type="addressPart">
						<div class="control-group">
							<span class="inputs_group">
								<input type="text" class="inner_input" name="value" placeholder="value" value=""/>
								<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSPhysicalAddressPartType" name="type" placeholder="Type" value=""/>
							</span>
							<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
						</div>
					</div>			
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="addressPart" add_new_type="addressPart">
						<i class="icon-plus icon-white"></i> Add Address Part
					</button>					
				</div>
			</div>
		</div>

		<div class="aro_box_part template" type="arg">
			<label class="control-label" for="title">Arg: </label>
			<div class="control-group">
				<span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgType" name="type" placeholder="Type" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="required"  placeholder="Required" value=""/>
				<span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSArgUse" name="use"  placeholder="Use" value=""/>
				</span>
				<input type="text" class="input-xlarge" name="value"  placeholder="Value" value=""/>
				<button class="btn btn-mini btn-danger remove">
					<i class="icon-remove icon-white"></i>
				</button>
			</div>
		</div>

		<div class="aro_box_part template" type="addressPart">
			<div class="control-group">
				<span class="inputs_group">
					<input type="text" class="inner_input" name="value" placeholder="value" value=""/>
					<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSPhysicalAddressPartType" name="type" placeholder="Type" value=""/>
				</span>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
		</div>

		<div class="aro_box template" type="accessPolicy">
			<input type="text" class="input-xlarge" name="value" placeholder="value" value=""/>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>

		<div class="aro_box template" type="fullCitation">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"></i></button>
				<input type="text" class="input-small rifcs-type" vocab="RIFCSCitationStyle" name="style" placeholder="Style" value="{@style}"/>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				<h1>Full Citation</h1>
			</div>
			<textarea name="value" place-holder="value" rows="6"></textarea>
		</div>



		<div class="aro_box template" type="citationMetadata">
			<div class="aro_box_display clearfix">
				<button class="btn-link toggle"><i class="icon-minus"></i></button><h1>Citation Metadata</h1>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>
			<div class="aro_box_part" type="identifier">
				<div class="control-group">
				<label class="control-label">Identifier:</label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input" name="value" placeholder="Identifier" value="" required=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSCitationIdentifierType" name="type" placeholder="Type" value=""/>
						</span>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="title">
				<div class="control-group">
				<label class="control-label">Title:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Title" required="" value=""/>
					</div>
				</div>
			</div>


			<div class="aro_box_part" type="version">
				<div class="control-group">
				<label class="control-label">Version:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Version" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="placePublished">
				<div class="control-group">
				<label class="control-label">Place Published:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Place Published" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="publisher">
				<div class="control-group">
				<label class="control-label">Publisher:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Publisher" required="" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="url">
				<div class="control-group">
				<label class="control-label">URL:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" valid-type="url" placeholder="URL" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="context">
				<div class="control-group">
				<label class="control-label">Context:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="value" placeholder="Context" value=""/>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="contributor">
				<div class="control-group">
				<label class="control-label">Contributors:</label>
					<div class="controls">
						<div class="aro_box_part" type="contributor">
							Contributor: <input type="text" class="input-small" name="seq" placeholder="Seq" value="1"/>
							<div class="aro_box_part" type="contributor_namePart">
								<div class="control-group">
									<label class="control-label" for="title">Name Part: </label>
									<div class="controls">
										<span class="inputs_group">
											<input type="text" name="value" class="inner_input" value="" placeholder="Value"/>
											<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
										</span>
										<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
									</div>
								</div>
							</div>
							<div class="separate_line"/>
							<div class="controls">
								<button class="btn btn-primary addNew" type="contributor_namePart" add_new_type="contributor_namePart"><i class="icon-plus icon-white"/> Add Name Part </button>
								<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> Remove this contributor</button>
								<hr/>
							</div>
							<hr/>
						</div>
					</div>
				</div>
				<div class="controls">
					<div class="separate_line"/>
					<button class="btn btn-primary addNew" type="contributor" add_new_type="contributor"><i class="icon-plus icon-white"/> Add Contributor </button>
				</div>
			</div>

			<div class="aro_box_part" type="citation_date">
				<div class="control-group">
				<label class="control-label">Dates:</label>
					<div class="controls">
						<div class="aro_box_part">
							<span class="inputs_group">
							<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSCitationDateType" name="type" placeholder="Date Type" value=""/>
							</span>
							<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>

						</div>
							<div class="separate_line"/>
								<button class="btn btn-primary addNew" type="date" add_new_type="citation_date"><i class="icon-plus icon-white"></i> Add Date</button>
							</div>						
					</div>
				</div>		
			</div>


		<div class="aro_box_part template" type="contributor">
			Contributor: <input type="text" class="input-small" name="seq" placeholder="Seq" value=""/>
			<div class="aro_box_part" type="contributor_namePart">
				<div class="control-group">
					<label class="control-label" for="title">Name Part: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input" value="" placeholder="Value"/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSNamePartType" name="type" placeholder="Type" value=""/>
						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="contributor_namePart" add_new_type="contributor_namePart"><i class="icon-plus icon-white"/> Add Name Part </button>
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/> Remove this contributor</button>
			</div>
			<hr/>
		</div>	
			
		<div class="aro_box_part template" type="date">
			<span class="inputs_group">
				<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>

		<div class="aro_box_part template" type="citation_date">
			<span class="inputs_group">
				<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
				<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSCitationDateType" name="type" placeholder="Date Type" value=""/>
			</span>
			<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
		</div>
		
		
		<div class="aro_box template" type="existenceDates">
			<div class="aro_box_display clearfix">
				<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
			</div>

			<div class="aro_box_part" type="startDate">
				<div class="control-group">
					<label class="control-label">Start Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker"  name="value" placeholder="startDate Value" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="startDate dateFormat" value=""/>
						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>

			<div class="aro_box_part" type="endDate">
				<div class="control-group">
					<label class="control-label">End Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker" name="value" placeholder="endDate Value" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="endDate dateFormat" value=""/>
						</span>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="aro_box_part template" type="temporal">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Temporal Coverage: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			<div class="aro_box_part" type="coverage_date">
				<div class="control-group">
					<label class="control-label" for="title">Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value="dateFrom"/>
						</span>
						<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="Date Format" value="W3CDTF"/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
			<div class="aro_box_part" type="coverage_date">
				<div class="control-group">
					<label class="control-label" for="title">Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value="dateTo"/>
						</span>
						<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="Date Format" value="W3CDTF"/>
						<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
					</div>
				</div>
			</div>
			<div class="separate_line"/>
			<div class="controls">
				<div class="btn-group">
					<button class="btn btn-mini btn-primary addNew" type="coverage_date" add_new_type="coverage_date"><i class="icon-white icon-plus"></i> Date</button>
					<button class="btn btn-mini btn-primary addNew" type="text" add_new_type="text"><i class="icon-white icon-plus"></i> Text</button>
				</div>
			</div>
			</div>
		</div>
		
		<div class="aro_box template" type="coverage">
			<div class="control-group">
				<div class="aro_box_display clearfix">
					<label class="control-label" for="title">Coverage: </label>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
				<div class="controls">
				<div class="separate_line"/>
					<div class="btn-group">
						<button class="btn btn-primary addNew" type="temporal" add_new_type="temporal"><i class="icon-white icon-plus"></i> Add Temporal Coverage</button>
						<button class="btn btn-primary  addNew" type="spatial" add_new_type="spatial"><i class="icon-white icon-plus"></i> Add Spatial Coverage</button>
					</div>					
				</div>
			</div>
		</div>

		<div class="aro_box template" type="dates">

				<div class="aro_box_display clearfix">
					<button class="btn-link toggle"><i class="icon-minus"/></button>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSDatesType" name="type" placeholder="Date Type" value=""/>
					<h1/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			<div class="aro_box_part" type="dates_date">
				<div class="control-group">
					<label class="control-label" for="title">Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input datepicker"  value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value="dateFrom"/>
						</span>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
					</div>
				</div>
			</div>
			<div class="aro_box_part" type="dates_date">
				<div class="control-group">
					<label class="control-label" for="title">Date: </label>
					<div class="controls">
						<span class="inputs_group">
							<input type="text" name="value" class="inner_input datepicker" value=""/>
							<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value="dateTo"/>
						</span>
						<button class="btn btn-mini btn-danger remove">
							<i class="icon-remove icon-white"/>
						</button>
					</div>
				</div>
			</div>
			<div class="separate_line"/>
			<div class="controls">
				<button class="btn btn-primary addNew" type="dates_date" add_new_type="dates_date">
					<i class="icon-plus icon-white"></i> Add new Date
				</button>
			</div>
		</div>

		<div class="aro_box_part template" type="dates_date">
			<div class="control-group">
				<label class="control-label" for="title">Date: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" name="value" class="inner_input datepicker" value=""/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Type" value=""/>
					</span>
					<button class="btn btn-mini btn-danger remove">
						<i class="icon-remove icon-white"/>
					</button>
				</div>
			</div>
		</div>
		
		
		<div class="aro_box_part template" type="coverage_date">
			<div class="control-group">
				<label class="control-label" for="title">Date: </label>
				<div class="controls">
					<span class="inputs_group">
						<input type="text" class="inner_input datepicker" name="value" placeholder="Date Value" value=""/>
						<input type="text" class="inner_input_type rifcs-type" vocab="RIFCSTemporalCoverageDateType" name="type" placeholder="Date Type" value=""/>
					</span>
					<input type="text" class="input-small rifcs-type" vocab="RIFCSDateFormat" name="dateFormat" placeholder="Date Format" value="W3CDTF"/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
		
		<div class="aro_box_part template" type="text">
			<div class="control-group">
				<label class="control-label" for="title">Text: </label>
				<div class="controls">
					<input type="text" class="input-large" name="value" placeholder="Date Value" value=""/>
					<button class="btn btn-mini btn-danger remove"><i class="icon-remove icon-white"/></button>
				</div>
			</div>
		</div>
		

	</xsl:template>

</xsl:stylesheet>