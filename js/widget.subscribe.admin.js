//Enables sortable funcionality on objects IDed by sortable
jQuery(document).ready(function() {
    jQuery("#sortable_widget tbody").disableSelection();
    var sort = jQuery("#sortable_widget tbody").sortable({
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
	jQuery( "sortable" ).disableSelection();
	var sort = jQuery( "#sortable" ).sortable({
		axis: 'y',
		cursor: 'move',
		stop: function (event, ui) {
    		var data = sort.sortable("serialize");
			
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

//Enables accordion funcionality on objects IDed by accordion
jQuery(function() {
	jQuery( "#accordion" ).accordion({
	 	collapsible: true,
	 	active: false
	 });
});

//Updates value of hidden value for deletion of widget value
jQuery(function() {
	jQuery( ".delete" ).click(function() {
		var value = jQuery( this ).val();
		jQuery( "#delete_value" ).val( value );
	});
});
