/**
 * Created by lwoods on 15/10/2014.
 * Original javascript code from 
 */


$(document).ready(function() {
	$('li[name="mint"]').on('click', function () {
    	var pageTitle = "DataCite Metadata Generator - Kernel 3.0";
   	 	var kernelVersion = "3.0";
   	 	var kernelNamespace = "http://datacite.org/schema/kernel-3";
    	var kernelSchema = "http://schema.datacite.org/meta/kernel-3/metadata.xsd";
    	var kernelSchemaLocation = kernelNamespace + " " + kernelSchema;
    	var header = "<?xml version='1.0' encoding='UTF-8'?>" + br() + "<resource xmlns='" + kernelNamespace + "' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='" + kernelSchemaLocation + "'>" + br();
    	document.title = pageTitle;

    	ps($('select#resourcetypegeneral'), resourceType);
    	ps($('select#descriptiontype'), descriptionType);
    	ps($('select#relatedidentifiertype'), relatedIdentifierType);
    	ps($('select#relationtype'), relationType);
    	ps($('select#datetype'), dateType);
   	 	ps($('select#contributortype'), contributorType);
    	ps($('select#titletype'), titleType);
    	$('h2.pagetitle').text(pageTitle);
    	$('body').on('keyup', 'input', function(event) {
       	 	event.preventDefault();
        	var xml = header;
        	var doi = $('input#doi').val().encodeXML();
        	xml += dt(doi) + br();
        	xml += ot("creators") + br();
        	$('div#creator').each(function() {
            	var cn = $(this).find('input#creatorname').val();
            	var ni = $(this).find('input#nameidentifier').val();
            	var nis = $(this).find('input#nameidentifierscheme').val();
           	 	var su = $(this).find('input#schemeuri').val();
            	if (cn) {
                	xml += tab() + ot("creator") + br();
                	xml += tab() + tab() + ot("creatorName") + cn.encodeXML() + ct("creatorName") + br();
                	if (ni) {
                    	xml += tab() + tab() + "<nameIdentifier nameIdentifierScheme='" + nis.encodeXML() + "'";
                    	if (su) {
                        	xml += " schemeURI='" + su.encodeXML() + "'";
                    	}
                   	 	xml += ">" + ni.encodeXML() + ct("nameIdentifier") + br();
                	}
                	xml += tab() + ct("creator") + br();
            	}
        	});
        	xml += ct("creators") + br();
       	 	xml += ot("titles") + br();
        	$('div#title').each(function() {
            	var t = $(this).find('input#title').val();
            	var tt = $(this).find('select#titletype option').filter(':selected').val().encodeXML();
            	if (t) {
                	xml += title(t.encodeXML(), tt);
            	}
        	});
        	xml += ct("titles") + br();
        	xml += ot("publisher") + $('input#publisher').val().encodeXML() + ct("publisher") + br();
        	xml += ot("publicationYear") + $('input#year').val().encodeXML() + ct("publicationYear") + br();
        	var subxml = "";
        	$('div#subject').each(function() {
            	var s = $(this).find('input#subject').val();
            	var ss = $(this).find('input#subjectscheme').val();
            	var su = $(this).find('input#schemeuri').val();
            	if (s) {
                	subxml += sub(s.encodeXML(), ss.encodeXML(), su.encodeXML());
            	}
        	});
        	if (subxml) {
            	xml += ot("subjects") + br() + subxml + ct("subjects") + br();
        	}
			var csxml = "";
			$('div#contributor').each(function() {
				var cn = $(this).find('input#contributorname').val();
				var cty = $(this).find('select#contributortype option').filter(':selected').val().encodeXML();
				var ni = $(this).find('input#nameidentifier').val();
				var nis = $(this).find('input#nameidentifierscheme').val();
				var su = $(this).find('input#schemeuri').val();
				if (cn) {
					csxml += tab() + "<contributor contributorType='" + cty + "'>" + br();
					csxml += tab() + tab() + ot("contributorName") + cn.encodeXML() + ct("contributorName") + br();
					if (ni) {
						csxml += tab() + tab() + "<nameIdentifier nameIdentifierScheme='" + nis.encodeXML() + "'";
						if (su) {
							csxml += " schemeURI='" + su.encodeXML() + "'";
						}
						csxml += ">" + ni.encodeXML() + ct("nameIdentifier") + br();
					}
					csxml += tab() + ct("contributor") + br();
				}
			});
			if (csxml) {
				xml += ot("contributors") + br() + csxml + ct("contributors") + br();
			}
			var l = $('input#language').val();
			if (l) {
				xml += ot("language") + l.encodeXML() + ct('language') + br();
			}
			var dsxml = "";
			$('div#date').each(function() {
				var d = $(this).find('input#date').val();
				var dt = $(this).find('select#datetype option').filter(':selected').val().encodeXML();
				if (d) {
					dsxml += tab() + "<date dateType='" + dt + "'>" + d.encodeXML() + ct("date") + br();
				}
			});
			if (dsxml) {
				xml += ot("dates") + br() + dsxml + ct("dates") + br();
			}
			var rt = $('input#resourcetype').val().encodeXML();
			var rtg = $('select#resourcetypegeneral option').filter(':selected').val().encodeXML();
			if (rt || rtg) {
				xml += "<resourceType resourceTypeGeneral='" + rtg + "'>" + rt + "</resourceType>" + br();
			}
			var aisxml = "";
			$('div#alternateid').each(function() {
				var ai = $(this).find('input#alternateid').val();
				var ait = $(this).find('input#alternateidtype').val();
				if (ai) {
					aisxml += tab() + "<alternateIdentifier alternateIdentifierType='" + ait.encodeXML() + "'>" + ai.encodeXML() + ct("alternateIdentifier") + br();
				}
			});
			if (aisxml) {
				xml += ot("alternateIdentifiers") + br() + aisxml + ct("alternateIdentifiers") + br();
			}
			var relidsxml = "";
			$('div#relatedid').each(function() {
				var ri = $(this).find('input#relatedid').val();
				var rit = $(this).find('select#relatedidentifiertype option').filter(':selected').val();
				var rt = $(this).find('select#relationtype option').filter(':selected').val();
				var rms = $(this).find('input#relatedmetadatascheme').val();
				var st = $(this).find('input#schemetype').val();
				var su = $(this).find('input#schemeuri').val();
				if (ri) {
					relidsxml += relid(ri.encodeXML(), rit.encodeXML(), rt.encodeXML(), rms.encodeXML(), st.encodeXML(), su.encodeXML());
				}
			});
			if (relidsxml) {
				xml += ot("relatedIdentifiers") + br() + relidsxml + ct("relatedIdentifiers") + br();
			}
			var ssxml = "";
			$('div#size').each(function() {
				var s = $(this).find('input#size').val();
				if (s) {
					ssxml += tab() + ot("size") + s.encodeXML() + ct("size") + br();
				}
			});
			if (ssxml) {
				xml += ot("sizes") + br() + ssxml + ct("sizes") + br();
			}
			var fsxml = "";
			$('div#format').each(function() {
				var f = $(this).find('input#format').val();
				if (f) {
					fsxml += tab() + ot("format") + f.encodeXML() + ct("format") + br();
				}
			});
			if (fsxml) {
				xml += ot("formats") + br() + fsxml + ct("formats") + br();
			}
			var v = $('input#version').val();
			if (v) {
				xml += ot("version") + v.encodeXML() + ct("version") + br();
			}
			var rsxml = "";
			$('div#rights').each(function() {
				var r = $(this).find('input#rights').val();
				var ru = $(this).find('input#rightsuri').val();
				if (r) {
					rsxml += tab() + "<rights";
					if (ru) {
						rsxml += " rightsURI='" + ru.encodeXML() + "'";
					}
					rsxml += ">" + r.encodeXML() + ct("rights") + br();
				}
			});
			if (rsxml) {
				xml += ot("rightsList") + br() + rsxml + ct("rightsList") + br();
			}
			var descxml = "";
			$('div#description').each(function() {
				var d = $(this).find('input').val();
				var dt = $(this).find('select option').filter(':selected').val();
				if (d) {
					descxml += desc(d.encodeXML(), dt.encodeXML());
				}
			});
			if (descxml) {
				xml += ot("descriptions") + br() + descxml + ct("descriptions") + br();
			}
			var gsxml = "";
			$('div#geolocation').each(function() {
				var gxml = "";
				var gpo = $(this).find('input#geolocationpoint').val();
				var gb = $(this).find('input#geolocationbox').val();
				var gpl = $(this).find('input#geolocationplace').val();
				if (gpo) {
					gxml += tab() + tab() + ot("geoLocationPoint") + gpo.encodeXML() + ct("geoLocationPoint") + br();
				}
				if (gb) {
					gxml += tab() + tab() + ot("geoLocationBox") + gb.encodeXML() + ct("geoLocationBox") + br();
				}
				if (gpl) {
					gxml += tab() + tab() + ot("geoLocationPlace") + gpl.encodeXML() + ct("geoLocationPlace") + br();
				}
				if (gxml) {
					gsxml += tab() + ot("geoLocation") + br() + gxml + tab() + ct("geoLocation") + br();
				}
			});
			if (gsxml) {
				xml += ot("geoLocations") + br() + gsxml + ct("geoLocations") + br();
			}
			xml += ct("resource");
			metadata = xml;
			$('div.right code').text(xml);
			$('.right').show();
			$("input[name='xml']").val(xml);
		});
		$('body').on('change', 'select', function(event) {
			event.preventDefault();
			$('input').eq(0).keyup();
		});
		$('#reset').bind('click', function(event) {
			event.preventDefault();
			location.reload(true);
		});
		$('#selectall').bind('click', function(event) {
			event.preventDefault();
			st($('div code').get(0));
		});
		$('button#add').bind('click', function(event) {
			event.preventDefault();
			var d = $(this).parent().find('div').eq(0).clone();
			$(d).find('input,select').val("");
			$('<button/>', {
				id: 'delete',
				text: '-',
			}).appendTo(d);
			d.appendTo($(this).parent());
		});
		$('body').on('click', 'button#delete', function(event) {
			event.preventDefault();
			$(this).parent().remove();
			$('input').eq(0).keyup();
		});
		$('body').on('click', 'button#more', function(event) {
			event.preventDefault();
			var div = $(this).parent();
			$(div).find('button#more').hide();
			$(div).find('div#subgroup,button#less').show();
		});
		$('body').on('click', 'button#less', function(event) {
			event.preventDefault();
			var div = $(this).parent();
			$(div).find('div#subgroup,button#less').hide();
			$(div).find('button#more').show();
			$(div).find('div#subgroup input,div#subgroup select').val("");
			$('input').eq(0).keyup();
		});
		$('body').on('click', 'h3.recommended,h3.other', function(event) {
			var div = $(this).next('div');
			var theDiv = ($(this).attr('class'))
			var text = $(this).html();
			if (text.charAt(0) == "+") {
				text = text.replace("+", "-");
				$(this).html(text);
				$('#'+theDiv).removeClass('hidden');
				$('#'+theDiv).show();
			} else if (text.charAt(0) == "-") {
				text = text.replace("-", "+");
				$(this).html(text);
				$(div).hide();
			}
		});
	});
 });
var descriptionType = ["Abstract", "Methods", "SeriesInformation", "TableOfContents", "Other"];
var relatedIdentifierType = ["ARK", "DOI", "EAN13", "EISSN", "Handle", "ISBN", "ISSN", "ISTC", "LISSN", "LSID", "PMID", "PURL", "UPC", "URL", "URN"];
var relationType = ["IsCitedBy", "Cites", "IsSupplementTo", "IsSupplementedBy", "IsContinuedBy", "Continues", "HasMetadata", "IsMetadataFor", "IsNewVersionOf", "IsPreviousVersionOf", "IsPartOf", "HasPart", "IsReferencedBy", "References", "IsDocumentedBy", "Documents", "IsCompiledBy", "Compiles", "IsVariantFormOf", "IsOriginalFormOf", "IsIdenticalTo"];
var resourceType = ["Audiovisual", "Collection", "Dataset", "Event", "Image", "InteractiveResource", "Model", "PhysicalObject", "Service", "Software", "Sound", "Text", "Workflow", "Other"];
var dateType = ["Accepted", "Available", "Copyrighted", "Collected", "Created", "Issued", "Submitted", "Updated", "Valid"];
var contributorType = ["ContactPerson", "DataCollector", "DataManager", "Distributor", "Editor", "Funder", "HostingInstitution", "Producer", "ProjectLeader", "ProjectManager", "ProjectMember", "RegistrationAgency", "RegistrationAuthority", "RelatedPerson", "Researcher", "ResearchGroup", "RightsHolder", "Sponsor", "Supervisor", "WorkPackageLeader", "Other"];
var titleType = ["AlternativeTitle", "Subtitle", "TranslatedTitle"];

function ps(s, sarr) {
    var i = $(s).attr('title');
    addO(s, "", "[" + i + "]");
    for (var i = 0; i < sarr.length; i++) {
        addO(s, sarr[i], sarr[i]);
    }
}

function addO(s, v, d) {
    $(s).append($('<option>').val(v).html(d));
}

function br() {
    return "\n";
}

function tab() {
    return "\t";
}

function ot(tag) {
    return "<" + tag + ">";
}

function ct(tag) {
    return "</" + tag + ">";
}

function title(t, tt) {
    var xml = tab() + "<title";
    if (tt) {
        xml += " titleType='" + tt + "'";
    }
    xml += ">" + t + ct("title") + br();
    return xml;
}

function desc(d, dt) {
    return tab() + "<description descriptionType='" + dt + "'>" + d + ct("description") + br();
}

function relid(r, rit, rt, rms, st, su) {
    var relxml = tab();
    relxml += "<relatedIdentifier relatedIdentifierType='";
    relxml += rit;
    relxml += "' relationType='" + rt + "'";
    if (rms) {
        relxml += " relatedMetadataScheme='" + rms + "'";
        if (st) {
            relxml += " schemeType='" + st + "'";
        }
        if (su) {
            relxml += " schemeURI='" + su + "'";
        }
    }
    relxml += ">" + r + ct("relatedIdentifier") + br();
    return relxml;
}

function sub(s, sc, su) {
    var sxml = tab() + "<subject";
    if (sc) {
        sxml += " subjectScheme='" + sc + "'";
    }
    if (su) {
        sxml += " schemeURI='" + su + "'";
    }
    sxml += ">" + s + ct("subject") + br();
    return sxml;
}

function dt(doi) {
    return "<identifier identifierType='DOI'>" + doi + ct("identifier");
}

function st(element) {
    var doc = document,
        text = element,
        range, selection;
    if (doc.body.createTextRange) {
        range = doc.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        selection = window.getSelection();
        range = doc.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}
String.prototype.encodeXML = function() {
    return this.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
};
var metadata = "";
var MIME_TYPE = "application/xml";
var cleanUp = function(a) {
    setTimeout(function() {
        window.URL.revokeObjectURL(a.href);
    }, 1500);
    $('span#output').html("");
};
var downloadFile = function() {
    window.URL = window.webkitURL || window.URL;
    var prevLink = $('span#output a');
    if (prevLink) {
        $('span#output').html("");
    }
    var bb = new Blob([metadata], {
        type: MIME_TYPE
    });
    var a = document.createElement('a');
    a.download = "metadata.xml";
    a.href = window.URL.createObjectURL(bb);
    a.textContent = 'Click here to Save: metadata.xml';
    a.setAttribute("data-downloadurl", [MIME_TYPE, a.download, a.href].join(':'));
    a.classList.add('button');
    a.onclick = function(e) {
        if ($(this).is(':disabled')) {
            return false;
        }
        cleanUp(this);
    };
    $(a).appendTo($('span#output'));
};

function save() {
    if (false) {
        alert("Not currently supported in Internet Explorer");
    } else {
        downloadFile();
    }
}