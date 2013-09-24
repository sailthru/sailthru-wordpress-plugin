(function ($) {
	"use strict";
	$(function () {
		$('#modal').hide();
		$( "#show_shortcode" ).on( "click", function( e ) {
			e.preventDefault();
			$('#modal').toggle();
		});
		// when a user clicks subscribe
		$("#sailthru-add-subscriber-form").submit( function( e ){
			
			e.preventDefault();
			var user_input = $(this).serialize();
			$.post(
				ajaxurl,
				user_input,
				function(data) {
					data = jQuery.parseJSON(data);
					console.log(data);
					if( data.error == true ) {
						$("#sailthru-add-subscriber-errors").html(data.message);
					} else {
						$("#sailthru-add-subscriber-form").html('');
						$("#success").show();
					}
			  		
				}
			);				

		});


	});
}(jQuery));