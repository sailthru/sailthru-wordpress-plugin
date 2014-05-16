//Enables sortable funcionality on objects IDed by sortable
jQuery(function() {
	jQuery( "#sortable" ).disableSelection();
	var sort = jQuery( "#sortable" ).sortable({
		axis: 'y',
		stop: function (event, ui) {
    		var data = sort.sortable("serialize");
			
			// sends GET to current page
			jQuery.ajax({
				data: data,
			});
			//retrieves the numbered position of the field
			data = data.match(/\d(\d?)*/g);
			jQuery(function () {
				jQuery( "#field_order" ).val( data );
			})
				
		}
	});
});



//Updates value of hidden value for deletion of widget value
jQuery(function() {
	jQuery( ".delete" ).click(function() {
		var value = jQuery( this ).val();
		jQuery( "#delete_value" ).val( value );
	});
});







/*
// Enables sortable funcionality on objects IDed by sortable
// This happens on the 'Subscribe Widget Fields'
jQuery(function() {
	jQuery( ".sortable" ).disableSelection();
	var sort = jQuery( ".sortable" ).sortable({
		axis: 'y',
		cursor: 'move',
		update: function (event, ui) {
			var order = jQuery( this ).sortable("serialize") + "&action=sailthru_update_field_order";

			jQuery.post( ajaxurl, order, function(response){
				alert(response);
				//renumber(ui.item)
			});
		}
	});
});
*/
