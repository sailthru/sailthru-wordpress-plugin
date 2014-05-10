//Enables sortable funcionality on objects IDed by sortable
jQuery(document).ready(function() {
    jQuery(".sortable_widget tbody").disableSelection();
    var sort = jQuery(".sortable_widget tbody").sortable({
    	axis: 'y',
		stop: function (event, ui) {
    		var data = jQuery( this ).sortable("serialize");
			//console.log( data );
			//retrieves the numbered position of the field
			data = data.match(/\d(\d?)*/g);
			jQuery(function () {
				jQuery( "#widget-sailthru-subscribe-id-2-field_order" ).val( data );
			});
				
		}
    });
});

//Enables sortable funcionality on objects IDed by sortable
jQuery(function() {
	jQuery( ".sortable" ).disableSelection();
	var sort = jQuery( ".sortable" ).sortable({
		axis: 'y',
		cursor: 'move',
		update: function () {
			var order = jQuery( this ).sortable("serialize") + "&action=sailthru_update_field_order";
			jQuery.post( ajaxurl, order, function(response){

				alert(response);

			});
		}
	});
});


//Updates value of hidden value for deletion of widget value
jQuery(function() {
	jQuery( ".delete" ).click(function(e) {
		
		e.preventDefault();
		var value = jQuery( this ).val();
		jQuery( "#delete_value" ).val( value );
	});
});
