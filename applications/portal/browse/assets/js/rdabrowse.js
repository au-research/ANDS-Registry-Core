$(document).ready(function() {

	$("#vocab-tree").vocab_widget({mode:'tree',repository:'anzsrc-for', endpoint:  window.default_base_url + 'apps/vocab_widget/proxy/'})
	.on('treeselect.vocab.ands', function(event) {
		var target = $(event.target);
		$('.tree_current').removeClass('tree_current');
		target.addClass('tree_current');
		var data = target.data('vocab');
		loadVocabDetail(data.about);
	});
});


function loadVocabDetail(about){
	$.ajax({
		url: base_url+'browse/loadVocab', 
		type: 'POST',
		data: {url:about},
		success: function(data){
			$('#content').html(data);
			loadSearchResult(about, 0);
		}
	});
}

function loadSearchResult(about, start){
	$.ajax({
		url:base_url+'browse/search', 
		type: 'POST',
		data:{url:about, start:start},
		success: function(data){
			for (l in data.links)
			{
				data.links[l].description = htmlDecode(data.links[l].description);
			}

			var template = $('#link_list_template').html();
			var output = Mustache.render(template, data);
			$('#vocab_search_result').html(output);

			$('.vocab-info-table tr:gt(1)').hide();
			$('#show_vocab_metadata_link').click(function(){
				$('.vocab-info-table tr:gt(1)').show();
				$(this).remove();
			});

			$('.suggestor_paging').click(function(){
				loadSearchResult(about, $(this).attr('offset'));
			});
		}
	});
}
