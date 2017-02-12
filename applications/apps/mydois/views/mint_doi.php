<?php 

/**
 * MINT DOI
 * 
 * @author LIZ WOODS <liz.woods@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
	<div class="box-content">

		<p id="mint_result">  </p>
        <div id="mint_form">
		    <form action="<?=base_url('mydois/manualMint/');?>" method="POST" class="form-horizontal" id="mint_form" enctype="multipart/form-data">
                <div id="loading"></div>
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>"/>
                <input type="hidden" name="app_id" value="<?php echo $app_id; ?>"/>
                <input type="hidden" name="xml"/> <a href="http://schema.datacite.org/meta/kernel-3.0/index.html" target="_blank" class="pull-right">DataCite Schema Help</a>
            <div class="control-group">
                <label class="control-label">DOI</label> <div class="controls"><input type="text" name="doi" value="<?php echo $doi_id; ?>" readonly="readonly"/></div>

            </div>
            <div class="control-group">
                <label class="control-label">URL</label> <div class="controls"> <input type="text" name="url" value="" /></div>
            </div>
            <div class="control-group">
                <label class="control-label">Metadata</label> <div class="controls"><input type="radio" name="xml_input" value="formxml" checked="checked"> Generate XML with form  <input type="radio" name="xml_input" value="uploadxml"> Upload XML Metadata</div>
            </div>
        <div class="control-group" id="uploadxml" style="display:none">
            <label class="control-label">File</label> <div class="controls"><input type="file" name="fileupload" id="fileupload"> </div>
            <div id="xmldisplay"></div>
        </div>

       <div class="control-group" id="formxml" style="display:none">
           <!-- DataCite Metadata Generator (Kernel 3.0)-->
           <!-- created: 04/10/2013 paluchm - DataCite Canada -->
           <!-- This form makes uses of styles developed by the wet-boew project (https://github.com/wet-boew/wet-boew)  -->
           <!-- Recommended browsers are Firefox or Chrome. Minimum supported IE version is 8 (save feature currently not supported in IE)-->

         <!--  <script type="text/javascript">
               $(document).ready(function(){var pageTitle="DataCite Metadata Generator - Kernel 3.0";var kernelVersion="3.0";var kernelNamespace="http://datacite.org/schema/kernel-3";var kernelSchema="http://schema.datacite.org/meta/kernel-3/metadata.xsd";var kernelSchemaLocation=kernelNamespace+" "+kernelSchema;var header="<?xml version='1.0' encoding='UTF-8'?>"+br()+"<resource xmlns='"+kernelNamespace+"' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='"+kernelSchemaLocation+"'>"+br();document.title=pageTitle;ps($('select#resourcetypegeneral'),resourceType);ps($('select#descriptiontype'),descriptionType);ps($('select#relatedidentifiertype'),relatedIdentifierType);ps($('select#relationtype'),relationType);ps($('select#datetype'),dateType);ps($('select#contributortype'),contributorType);ps($('select#titletype'),titleType);$('h2.pagetitle').text(pageTitle);$('body').on('keyup','input',function(event){event.preventDefault();var xml=header;var doi=$('input#doi').val().encodeXML();xml+=dt(doi)+br();xml+=ot("creators")+br();$('div#creator').each(function(){var cn=$(this).find('input#creatorname').val();var ni=$(this).find('input#nameidentifier').val();var nis=$(this).find('input#nameidentifierscheme').val();var su=$(this).find('input#schemeuri').val();if(cn){xml+=tab()+ot("creator")+br();xml+=tab()+tab()+ot("creatorName")+cn.encodeXML()+ct("creatorName")+br();if(ni){xml+=tab()+tab()+"<nameIdentifier nameIdentifierScheme='"+nis.encodeXML()+"'";if(su){xml+=" schemeURI='"+su.encodeXML()+"'";}xml+=">"+ni.encodeXML()+ct("nameIdentifier")+br();}xml+=tab()+ct("creator")+br();}});xml+=ct("creators")+br();xml+=ot("titles")+br();$('div#title').each(function(){var t=$(this).find('input#title').val();var tt=$(this).find('select#titletype option').filter(':selected').val().encodeXML();if(t){xml+=title(t.encodeXML(),tt);}});xml+=ct("titles")+br();xml+=ot("publisher")+$('input#publisher').val().encodeXML()+ct("publisher")+br();xml+=ot("publicationYear")+$('input#year').val().encodeXML()+ct("publicationYear")+br();var subxml="";$('div#subject').each(function(){var s=$(this).find('input#subject').val();var ss=$(this).find('input#subjectscheme').val();var su=$(this).find('input#schemeuri').val();if(s){subxml+=sub(s.encodeXML(),ss.encodeXML(),su.encodeXML());}});if(subxml){xml+=ot("subjects")+br()+subxml+ct("subjects")+br();}var csxml="";$('div#contributor').each(function(){var cn=$(this).find('input#contributorname').val();var cty=$(this).find('select#contributortype option').filter(':selected').val().encodeXML();var ni=$(this).find('input#nameidentifier').val();var nis=$(this).find('input#nameidentifierscheme').val();var su=$(this).find('input#schemeuri').val();if(cn){csxml+=tab()+"<contributor contributorType='"+cty+"'>"+br();csxml+=tab()+tab()+ot("contributorName")+cn.encodeXML()+ct("contributorName")+br();if(ni){csxml+=tab()+tab()+"<nameIdentifier nameIdentifierScheme='"+nis.encodeXML()+"'";if(su){csxml+=" schemeURI='"+su.encodeXML()+"'";}csxml+=">"+ni.encodeXML()+ct("nameIdentifier")+br();}csxml+=tab()+ct("contributor")+br();}});if(csxml){xml+=ot("contributors")+br()+csxml+ct("contributors")+br();}var l=$('input#language').val();if(l){xml+=ot("language")+l.encodeXML()+ct('language')+br();}var dsxml="";$('div#date').each(function(){var d=$(this).find('input#date').val();var dt=$(this).find('select#datetype option').filter(':selected').val().encodeXML();if(d){dsxml+=tab()+"<date dateType='"+dt+"'>"+d.encodeXML()+ct("date")+br();}});if(dsxml){xml+=ot("dates")+br()+dsxml+ct("dates")+br();}var rt=$('input#resourcetype').val().encodeXML();var rtg=$('select#resourcetypegeneral option').filter(':selected').val().encodeXML();if(rt||rtg){xml+="<resourceType resourceTypeGeneral='"+rtg+"'>"+rt+"</resourceType>"+br();}var aisxml="";$('div#alternateid').each(function(){var ai=$(this).find('input#alternateid').val();var ait=$(this).find('input#alternateidtype').val();if(ai){aisxml+=tab()+"<alternateIdentifier alternateIdentifierType='"+ait.encodeXML()+"'>"+ai.encodeXML()+ct("alternateIdentifier")+br();}});if(aisxml){xml+=ot("alternateIdentifiers")+br()+aisxml+ct("alternateIdentifiers")+br();}var relidsxml="";$('div#relatedid').each(function(){var ri=$(this).find('input#relatedid').val();var rit=$(this).find('select#relatedidentifiertype option').filter(':selected').val();var rt=$(this).find('select#relationtype option').filter(':selected').val();var rms=$(this).find('input#relatedmetadatascheme').val();var st=$(this).find('input#schemetype').val();var su=$(this).find('input#schemeuri').val();if(ri){relidsxml+=relid(ri.encodeXML(),rit.encodeXML(),rt.encodeXML(),rms.encodeXML(),st.encodeXML(),su.encodeXML());}});if(relidsxml){xml+=ot("relatedIdentifiers")+br()+relidsxml+ct("relatedIdentifiers")+br();}var ssxml="";$('div#size').each(function(){var s=$(this).find('input#size').val();if(s){ssxml+=tab()+ot("size")+s.encodeXML()+ct("size")+br();}});if(ssxml){xml+=ot("sizes")+br()+ssxml+ct("sizes")+br();}var fsxml="";$('div#format').each(function(){var f=$(this).find('input#format').val();if(f){fsxml+=tab()+ot("format")+f.encodeXML()+ct("format")+br();}});if(fsxml){xml+=ot("formats")+br()+fsxml+ct("formats")+br();}var v=$('input#version').val();if(v){xml+=ot("version")+v.encodeXML()+ct("version")+br();}var rsxml="";$('div#rights').each(function(){var r=$(this).find('input#rights').val();var ru=$(this).find('input#rightsuri').val();if(r){rsxml+=tab()+"<rights";if(ru){rsxml+=" rightsURI='"+ru.encodeXML()+"'";}rsxml+=">"+r.encodeXML()+ct("rights")+br();}});if(rsxml){xml+=ot("rightsList")+br()+rsxml+ct("rightsList")+br();}var descxml="";$('div#description').each(function(){var d=$(this).find('input').val();var dt=$(this).find('select option').filter(':selected').val();if(d){descxml+=desc(d.encodeXML(),dt.encodeXML());}});if(descxml){xml+=ot("descriptions")+br()+descxml+ct("descriptions")+br();}var gsxml="";$('div#geolocation').each(function(){var gxml="";var gpo=$(this).find('input#geolocationpoint').val();var gb=$(this).find('input#geolocationbox').val();var gpl=$(this).find('input#geolocationplace').val();if(gpo){gxml+=tab()+tab()+ot("geoLocationPoint")+gpo.encodeXML()+ct("geoLocationPoint")+br();}if(gb){gxml+=tab()+tab()+ot("geoLocationBox")+gb.encodeXML()+ct("geoLocationBox")+br();}if(gpl){gxml+=tab()+tab()+ot("geoLocationPlace")+gpl.encodeXML()+ct("geoLocationPlace")+br();}if(gxml){gsxml+=tab()+ot("geoLocation")+br()+gxml+tab()+ct("geoLocation")+br();}});if(gsxml){xml+=ot("geoLocations")+br()+gsxml+ct("geoLocations")+br();}xml+=ct("resource");metadata=xml;$('div.right code').text(xml);$('.right').show();});$('body').on('change','select',function(event){event.preventDefault();$('input').eq(0).keyup();});$('#reset').bind('click',function(event){event.preventDefault();location.reload(true);});$('#selectall').bind('click',function(event){event.preventDefault();st($('div code').get(0));});$('button#add').bind('click',function(event){event.preventDefault();var d=$(this).parent().find('div').eq(0).clone();$(d).find('input,select').val("");$('<button/>',{id:'delete',text:'-',}).appendTo(d);d.appendTo($(this).parent());});$('body').on('click','button#delete',function(event){event.preventDefault();$(this).parent().remove();$('input').eq(0).keyup();});$('body').on('click','button#more',function(event){event.preventDefault();var div=$(this).parent();$(div).find('button#more').hide();$(div).find('div#subgroup,button#less').show();});$('body').on('click','button#less',function(event){event.preventDefault();var div=$(this).parent();$(div).find('div#subgroup,button#less').hide();$(div).find('button#more').show();$(div).find('div#subgroup input,div#subgroup select').val("");$('input').eq(0).keyup();});$('body').on('click','h3.recommended,h3.other',function(event){var div=$(this).next('div');var text=$(this).html();if(text.charAt(0)=="+"){text=text.replace("+","-");$(this).html(text);$(div).show();}else if(text.charAt(0)=="-"){text=text.replace("-","+");$(this).html(text);$(div).hide();}});});var descriptionType=["Abstract","Methods","SeriesInformation","TableOfContents","Other"];var relatedIdentifierType=["ARK","DOI","EAN13","EISSN","Handle","ISBN","ISSN","ISTC","LISSN","LSID","PMID","PURL","UPC","URL","URN"];var relationType=["IsCitedBy","Cites","IsSupplementTo","IsSupplementedBy","IsContinuedBy","Continues","HasMetadata","IsMetadataFor","IsNewVersionOf","IsPreviousVersionOf","IsPartOf","HasPart","IsReferencedBy","References","IsDocumentedBy","Documents","IsCompiledBy","Compiles","IsVariantFormOf","IsOriginalFormOf","IsIdenticalTo"];var resourceType=["Audiovisual","Collection","Dataset","Event","Image","InteractiveResource","Model","PhysicalObject","Service","Software","Sound","Text","Workflow","Other"];var dateType=["Accepted","Available","Copyrighted","Collected","Created","Issued","Submitted","Updated","Valid"];var contributorType=["ContactPerson","DataCollector","DataManager","Distributor","Editor","Funder","HostingInstitution","Producer","ProjectLeader","ProjectManager","ProjectMember","RegistrationAgency","RegistrationAuthority","RelatedPerson","Researcher","ResearchGroup","RightsHolder","Sponsor","Supervisor","WorkPackageLeader","Other"];var titleType=["AlternativeTitle","Subtitle","TranslatedTitle"];function ps(s,sarr){var i=$(s).attr('title');addO(s,"","["+i+"]");for(var i=0;i<sarr.length;i++){addO(s,sarr[i],sarr[i]);}}function addO(s,v,d){$(s).append($('<option>').val(v).html(d));}function br(){return"\n";}function tab(){return"\t";}function ot(tag){return"<"+tag+">";}function ct(tag){return"</"+tag+">";}function title(t,tt){var xml=tab()+"<title";if(tt){xml+=" titleType='"+tt+"'";}xml+=">"+t+ct("title")+br();return xml;}function desc(d,dt){return tab()+"<description descriptionType='"+dt+"'>"+d+ct("description")+br();}function relid(r,rit,rt,rms,st,su){var relxml=tab();relxml+="<relatedIdentifier relatedIdentifierType='";relxml+=rit;relxml+="' relationType='"+rt+"'";if(rms){relxml+=" relatedMetadataScheme='"+rms+"'";if(st){relxml+=" schemeType='"+st+"'";}if(su){relxml+=" schemeURI='"+su+"'";}}relxml+=">"+r+ct("relatedIdentifier")+br();return relxml;}function sub(s,sc,su){var sxml=tab()+"<subject";if(sc){sxml+=" subjectScheme='"+sc+"'";}if(su){sxml+=" schemeURI='"+su+"'";}sxml+=">"+s+ct("subject")+br();return sxml;}function dt(doi){return"<identifier identifierType='DOI'>"+doi+ct("identifier");}function st(element){var doc=document,text=element,range,selection;if(doc.body.createTextRange){range=doc.body.createTextRange();range.moveToElementText(text);range.select();}else if(window.getSelection){selection=window.getSelection();range=doc.createRange();range.selectNodeContents(text);selection.removeAllRanges();selection.addRange(range);}}String.prototype.encodeXML=function(){return this.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&apos;');};var metadata="";var MIME_TYPE="application/xml";var cleanUp=function(a){setTimeout(function(){window.URL.revokeObjectURL(a.href);},1500);$('span#output').html("");};var downloadFile=function(){window.URL=window.webkitURL||window.URL;var prevLink=$('span#output a');if(prevLink){$('span#output').html("");}var bb=new Blob([metadata],{type:MIME_TYPE});var a=document.createElement('a');a.download="metadata.xml";a.href=window.URL.createObjectURL(bb);a.textContent='Click here to Save: metadata.xml';a.setAttribute("data-downloadurl",[MIME_TYPE,a.download,a.href].join(':'));a.classList.add('button');a.onclick=function(e){if($(this).is(':disabled')){return false;}cleanUp(this);};$(a).appendTo($('span#output'));};function save(){if(false){alert("Not currently supported in Internet Explorer");}else{downloadFile();}}
           </script> -->
           <!--[if IE]><script>function save(){alert("Not currently supported in Internet Explorer");}</script><![endif]-->
          <style>
               body{font-family:sans-serif}
               .hidden{display:none}.visible{display:block}
               .input-field{margin-bottom:2px;width:89%}
               input.attrib{width:43%}select.attrib{width:45%}
               .tag{float:left;margin-top:2px;width:80%}
               /*div.left{float:left;width:38%}*/
               /*div.right{float:right;width:61%}*/
               div.entry{margin-bottom:6px}
               div.form{padding:12px}
               div.form div{width:100%}
               input,select{border-radius:4px;border-style:solid;border-width:1px;margin-bottom:10px;margin-top:0;min-height:18px!important;padding:4px}
               .right button{margin:2px}
               button{float:right;font-weight:400}
               button:hover{background-position:0 -15px;outline-offset:-6px;text-decoration:none;transition:background-position .1s linear 0s}
               input:focus,select:focus{border-color:#176ca7;box-shadow:0 1px 1px rgba(0,0,0,0.05) inset,0px 0 8px #99cdf1;transition:border .2s linear 0s,box-shadow .2s linear 0s}
               div.right>div,div.left>div{background:none repeat scroll 0 center #f6f6f6;color:#222222 !important;outline:1px solid gainsboro}
               div.right h3{background-color:#666666;background-image:linear-gradient(#666666,#545454);background-repeat:repeat-x;background-size:100% auto;border-collapse:collapse;border-spacing:0;border-top-left-radius:4px;border-top-right-radius:4px;color:#ffffff;direction:ltr;font-family:sans-serif;font-size:12.8px;font-weight:700;line-height:19.2px;margin:0 0 1px;padding:5px 10px;text-align:left;text-shadow:#222222 0 1px 1px;vertical-align:bottom}div.left h3{background-color:#176ca7;background-image:linear-gradient(#176ca7,#135888);background-repeat:repeat-x;background-size:100% auto;border-collapse:collapse;border-spacing:0;border-top-left-radius:4px;border-top-right-radius:4px;color:#ffffff;direction:ltr;font-family:sans-serif;font-size:12.8px;font-weight:700;line-height:19.2px;margin:0 0 1px;padding:5px 10px;text-align:left;text-shadow:#222222 0 1px 1px;vertical-align:bottom}
               h3.recommended:hover,h3.other:hover,button:hover{cursor:pointer}
               h1,h2{color:#0084B9}
               span.divider{border-bottom:1px gainsboro solid;float:left;line-height:3px;margin-bottom:5px;width:100%}
               pre{white-space:pre-wrap;word-wrap:break-word}span.output{float:left}
               a.button{-moz-text-blink:none;-moz-text-decoration-color:#ffffff;-moz-text-decoration-line:none;-moz-text-decoration-style:solid;background-color:#176ca7;background-image:linear-gradient(#176ca7,#114f7a);background-repeat:repeat-x;background-size:100% auto;border-bottom-color:#0b324d;border-bottom-left-radius:4px;border-bottom-right-radius:4px;border-bottom-style:solid;border-bottom-width:1px;border-collapse:collapse;border-left-color:#0e4164;border-left-style:solid;border-left-width:1px;border-right-color:#0e4164;border-right-style:solid;border-right-width:1px;border-spacing:0;border-top-color:#0e4164;border-top-left-radius:4px;border-top-right-radius:4px;border-top-style:solid;border-top-width:1px;box-shadow:rgba(255,255,255,0.2) 0 1px 0 0 inset,rgba(0,0,0,0.05) 0 1px 2px 0;color:#ffffff;cursor:pointer;direction:ltr;display:inline-block;font-family:Arial,Verdana,Helvetica,sans-serif;font-size:13.3333px;font-weight:400;line-height:16px;padding:4px 10px;text-align:center;text-decoration:none;text-shadow:#222222 0 1px 1px;vertical-align:middle}
           </style>

           <!-- <h2 class="pagetitle"></h2> -->
           <div class="controls left">
               <p><em>This form supports version 3.0 of the DataCite schema</em></p>
               <h3 class="mandatory">Mandatory Elements</h3>
               <div class="form mandatory">
                   <div>
                       <span class="tag">DOI:</span>
                       <div id="doi">
                           <input type="text" class="input-field" id="doi" placeholder="[DOI]" title="DOI" value="<?php echo $doi_id;?>" readonly/>
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="titles">
                       <span class="tag">Title(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="title" class="entry">
                           <input class="input-field" type="text" id="title" placeholder="[TITLE]" title="title" value="" />
                           <select class="input-field attrib" id="titletype" title="titleType"></select>
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="creators">
                       <span class="tag">Creator(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="creator" class="entry">
                           <input class="input-field" id="creatorname" type="text" placeholder="[CREATOR NAME]" title="creatorName" value="" />
                           <input class="input-field attrib" type="text" id="nameidentifier" placeholder="[NAME IDENTIFIER]" title="nameIdentifier" value="" />
                           <input class="input-field attrib" type="text" id="nameidentifierscheme" placeholder="[NAME ID SCHEME]" title="nameIdentifierScheme" value="" />
                           <input class="input-field" type="text" id="schemeuri" placeholder="[IDENTIFIER SCHEME URI]" title="schemeURI" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div>
                       <span class="tag">Publisher:</span>
                       <div id="publisher">
                           <input type="text" class="input-field" id="publisher" placeholder="[PUBLISHER]" title="publisher" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div>
                       <span class="tag">Publication Year:</span>
                       <div class="year">
                           <input type="text" class="input-field" id="year" placeholder="[YYYY]" title="publicationYear" pattern="[0-9]{4}" value="" />
                       </div>
                   </div>
               </div>
               <h3 class="recommended">+ Recommended Elements</h3>
               <div id="recommended" class="form recommended hidden">
                   <div id="subjects">
                       <span class="tag">Subject(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="subject" class="entry">
                           <input class="input-field attrib" type="text" id="subject" placeholder="[SUBJECT]" title="subject" value="" />
                           <input class="input-field attrib" title="Subject Scheme" type="text" id="subjectscheme" placeholder="[SUBJECT SCHEME]" title="subjectScheme" value="" />
                           <input class="input-field" type="text" id="schemeuri" placeholder="[SUBJECT SCHEME URI]" title="schemeURI" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="contributors">
                       <span class="tag">Contributor(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="contributor" class="entry">
                           <input class="input-field attrib" type="text" id="contributorname" placeholder="[CONTRIBUTOR NAME]" title="contributorName" value="" />
                           <select class="input-field attrib" id="contributortype" title="contributorType"></select>
                           <input class="input-field attrib" type="text" id="nameidentifier" placeholder="[NAME IDENTIFIER]" title="nameIdentifier" value="" />
                           <input class="input-field attrib" type="text" id="nameidentifierscheme" placeholder="[NAME ID SCHEME]" title="nameIdentifierScheme" value="" />
                           <input class="input-field" type="text" id="schemeuri" placeholder="[IDENTIFIER SCHEME URI]" title="schemeURI" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="dates">
                       <span class="tag">Date(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="date" class="entry">
                           <input class="input-field attrib" id="date" type="text" placeholder="[DATE]" title="date" value="" />
                           <select class="input-field attrib" id="datetype" title="dateType"></select>
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div>
                       <span class="tag">Resource Type:</span>
                       <div id="resourcetype" style="float:left;">
                           <input type="text" class="input-field attrib" id="resourcetype" placeholder="[RESOURCE TYPE]" title="resourceType" value="" />
                           <select class="input-field attrib" id="resourcetypegeneral" title="resourceTypeGeneral"></select>
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="relatedids">
                       <span class="tag">Related Identifier(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="relatedid" class="entry">
                           <input class="input-field" id="relatedid" type="text" placeholder="[RELATED IDENTIFIER]" title="relatedIdentifier" value="" />
                           <select class="input-field attrib" id="relatedidentifiertype" title="relatedIdentifierType"></select>
                           <select class="input-field attrib" id="relationtype" title="relationType"></select>
                           <input class="input-field attrib" id="relatedmetadatascheme" type="text" placeholder="[METADATA SCHEME]" title="relatedMetadataScheme" value="" />
                           <input class="input-field attrib" id="schemetype" type="text" placeholder="[SCHEME TYPE]" title="schemeType" value="" />
                           <input class="input-field" type="text" id="schemeuri" placeholder="[SCHEME URI]" title="schemeURI" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="descriptions">
                       <span class="tag">Description:</span>
                       <button type="button" id="add">+</button>
                       <div id="description" class="entry">
                           <input class="input-field" type="text" placeholder="[DESCRIPTION]" title="description" value="" />
                           <select class="input-field attrib" id="descriptiontype" title="descriptionType"></select>
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="geolocations">
                       <span class="tag">Geo Location:</span>
                       <button type="button" id="add">+</button>
                       <div id="geolocation" class="entry">
                           <input class="input-field attrib" id="geolocationpoint" type="text" placeholder="[GEO LOCATION POINT]" title="geoLocationPoint" value="" />
                           <input class="input-field attrib" id="geolocationbox" type="text" placeholder="[GEO LOCATION BOX]" title="geoLocationBox" value="" />
                           <input class="input-field attrib" id="geolocationplace" type="text" placeholder="[GEO LOCATION PLACE]" title="geoLocationPlace" value="" />
                       </div>
                   </div>
               </div>
               <h3 class="other">+ Other Elements</h3>
               <div id="other" class="form other hidden">
                   <div>
                       <span class="tag">Language:</span>
                       <div id="language">
                           <input class="input-field attrib" type="text" id="language" placeholder="[LANGUAGE]" title="language" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="alternateids">
                       <span class="tag">Alternate Identifier(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="alternateid" class="entry">
                           <input class="input-field attrib" id="alternateid" type="text" placeholder="[ALTERNATE IDENTIFIER]" title="alternateIdentifier" value="" />
                           <input class="input-field attrib" id="alternateidtype" type="text" placeholder="[ALTERNATE ID TYPE]" title="alternateIdentifierType" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="sizes">
                       <span class="tag">Size(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="size" class="entry">
                           <input class="input-field" id="size" type="text" placeholder="[SIZE]" title="size" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="formats">
                       <span class="tag">Format(s):</span>
                       <button type="button" id="add">+</button>
                       <div id="format" class="entry">
                           <input class="input-field" id="format" type="text" placeholder="[FORMAT]" title="format" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div>
                       <span class="tag">Version:</span>
                       <div id="version" class="entry">
                           <input type="text" class="input-field attrib" id="version" placeholder="[VERSION]" title="version" value="" />
                       </div>
                   </div>
                   <span class="divider">&nbsp;</span>
                   <div id="rightslist">
                       <span class="tag">Rights List:</span>
                       <button type="button" id="add">+</button>
                       <div id="rights" class="entry">
                           <input class="input-field" type="text" id="rights" placeholder="[RIGHTS]" title="rights" value="" />
                           <input class="input-field" type="text" id="rightsuri" placeholder="[RIGHTS URI]" title="rightsURI" value="" />
                       </div>
                   </div>
               </div>
               <br />
           </div>

        </div>
                <a id="doi_mint_confirm" class="btn btn-primary pull-right" style="margin-top:-25px" data-loading-text="Minting..." href="javascript:;">Mint DOI</a></form>
	</div>

    </div>
<div class="modal hide fade" id="mintDoiResult" tabindex="-1" role="dialog" aria-labelledby="mintDoiResult" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" id="doi_mint_close">×</button>

    </div>
    <div class="modal-body">
        <p>
        <div>
            mint successful
        </div>
        </p>
    </div>
    <div class="modal-footer">
        <a id="doi_mint_close" class="btn hide" data-dismiss="modal" href="#">Close</a>
    </div>
</div>
