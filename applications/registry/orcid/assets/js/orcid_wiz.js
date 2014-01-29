$(function(){
	$('.form-search').submit();
	load_imported();
	$('.stick').sticky();
	bootstro.start('#start-bootstro');
});

$(document).on('click', '.remove', function(){
	$(this).parent().remove();
}).on('submit', '.form-search', function(e){
	e.preventDefault();
	e.stopPropagation();
	var term = $(this).find('.search-query').val();
	if(term!=''){
		do_search(term,0,true);
	}
}).on('click', '.import_to_orcid', function(){
	var ids=[];
	ids.push($(this).attr('ro_id'));
	import_to_orcid(ids, this);
}).on('click', '.import_all_to_orcid', function(){
	var ids = [];
	$('.suggested_collections').each(function(){
		ids.push($(this).attr('ro_id'));
	});
	import_to_orcid(ids, this);
}).on('click', '.load_more', function(){
	var term = $('.form-search .search-query').val();
	var next = $(this).attr('start');
	do_search(term, next, false);
	$(this).remove();
});

function import_to_orcid(ids, button){
	$(button).button('loading');
	$.ajax({
	   type:"POST",
	   url:base_url+"orcid/import_to_orcid/",
	   data:{ro_ids:ids},
	   success:function(data){
	     if(data=='1'){
	     	$(button).text('Imported').removeClass('import_to_orcid').addClass('disabled').attr('disabled', true);
	     	load_imported();
	     }else{
	     	console.log(data)
	     }
	   },
	   error: function(data){
	   	log(data);
	   }
	});
}

function load_imported(){
	var orcid_id = $('#orcid').val();
	$.getJSON(base_url+"orcid/imported/"+orcid_id, function(data){
		var template = $('#imported').html();
		var output = Mustache.render(template, data);
		$('#imported_records').html(output);
		$(data.imported).each(function(){
			$('a.import_to_orcid[ro_id='+this.id+']')
				.text('Re-import to ORCID')
				.removeClass('btn-primary');
		});
	});
}

function do_search(query, page,reload){
	$.ajax({
	   type:"GET",
	   async:false,
	   // url: base_url+'services/registry/post_solr_search',
	   url:base_url+"services/registry/solr_search/?query="+encodeURIComponent(query)+'&start='+page+'&fq=class:collection',
	   success:function(data){
	      	var template = $('#template').html();
			var output = Mustache.render(template, data);
			if(!reload){
				$('#result').append(output);
			}else{
				$('#result').html(output);
			}
			load_imported();
			var rows = parseInt(data.solr_header.params.rows);
		   	var start = parseInt(data.solr_header.params.start);
		   	var numFound = parseInt(data.numFound);
		   	var hasMore = true;
		   	if(start+rows >= numFound){
		   		hasMore = false;
		   	}
		   	if(hasMore){
		   		var next = start+rows;
		   		$('#result').append('<a href="javascript:;" class="load_more" start="'+next+'">Load More</a>');	
		   	}
		   	// log(rows, start, numFound);
	   }
	});
}