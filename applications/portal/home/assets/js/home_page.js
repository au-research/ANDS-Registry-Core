$(document).ready(function() {

	//Featured Research Domain
	$.getJSON(default_base_url+'registry/services/rda/getSpotlight/',initSpotlight);
	function initSpotlight(data){
		var template = $('#spotlight_template').html();
		var output = Mustache.render(template, data);
		$('#spotlight').html(output);
		$('.flexslider').flexslider({
		    animation: "fade",
		    controlNav: false,
		    slideshowSpeed: 7500,
		    directionNav:true,
		    pauseOnHover:true,
		  });
		$('.slides li img').qtip({
			position:{my:'left center', at:'center right', viewport:$(window)},
			style: {classes: 'ui-tooltip-light ui-tooltip-shadow',width: '150px'},
		});

		$(document).on('mouseover', '#spotlight', function(){
			$('.pauseicon', this).show();
		}).on('mouseout', '#spotlight', function(){
			$('.pauseicon', this).hide();
		});
	}

	$('#show_who_contributes').qtip({
		content: {
			text: $('#who_contributes')
		},
		show:{solo:true,event:'click'},
	    hide:{delay:1000, fixed:true,event:'unfocus'},
	    position:{my:'bottom right', at:'top center', viewport:$(window)},
	    style: {
	        classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
	        width: 650
	    }
	});


	var fw_cookie = $.cookie('falling_water_dontshow');
	if(!fw_cookie){
		$('.open-popup-link').magnificPopup({
		  type:'inline',
		  midClick: true, // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
		  removalDelay: 300,
		  mainClass: 'mfp-zoom-in'
		});
		$('.open-popup-link').magnificPopup('open');
		$('#nothanks').click(function(e){
			e.preventDefault();
			$.cookie('falling_water_dontshow', 'set', { path: '/' });
			$.magnificPopup.close();
		});
		$('#fwform').submit(function(e){
			e.preventDefault();
			var name = $('#fwform input[name=name]').val();
			var email = $('#fwform input[name=email]').val();
			if (!name) alert('Please input a name');
			if (!email) alert('Please input an email address');
			if (name && email) {
				$.ajax({
			  		type:"POST",
			  		url: base_url+"/home/falling_water_register",
			  		data: {name:name, email:email},
		  			success:function(msg){
		  				$.cookie('falling_water_dontshow', 'set', { path: '/' });
		  				$.magnificPopup.close();
		  			}
		  		}); 
			}
		});
	}

});