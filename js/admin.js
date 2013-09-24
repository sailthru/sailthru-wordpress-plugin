(function($) {
	"use strict";
	$(function() {
		// show/hide the form to add the user's API key/secret
		$("#sailthru-add-api-key").click(function(e) {
			e.preventDefault();
			$("#sailthru-add-api-key-form").toggle(600);
		});
		$('.selection').parent().parent().hide();
		$('#type').on("change", (function() {
			if ($(this).attr('value') == 'select' || $(this).attr('value') == 'radio' || $(this).attr('value') == 'hidden') {
				$('.selection').parent().parent().show();
				$('#add_value').show();
				$( "input[name*=sailthru_customfield_value1]" ).show();
				$("input[name*=sailthru_customfield_value1]").parent().parent().find('th').html('HTML value / visible value');
				if($(this).attr('value') == 'hidden'){
					$('#add_value').hide();
					$( "input[name*=sailthru_customfield_value1]" ).hide();
					$("input[name*=sailthru_customfield_value1]").parent().parent().find('th').html('Field Value');
				}
			} 
			else {
				$('.selection').parent().parent().hide();
			}
		}));
		$('#add_value').on("click", (function(e) {
			e.preventDefault();
			var new_val = parseInt($('#value_amount').attr('value'),10);
			new_val = new_val + 1;
			var second_val = new_val +1;
			console.log(new_val);
			$(this).parent().append('<br /> <input class="selection" name="sailthru_forms_options[sailthru_customfield_value' + new_val + ']" type="text"  placeholder="key"/><input class="selection" name="sailthru_forms_options[sailthru_customfield_value' + second_val + ']" type="text"  placeholder="value"/>');
			$('#value_amount').attr('value',second_val);
		}));
		$('#add_attr').on("click", (function(e) {
			e.preventDefault();
			var new_val = parseInt($('#attr_amount').attr('value'),10);
			new_val = new_val + 1;
			var second_val = new_val +1;
			console.log(new_val);
			$(this).parent().append('<br /> <input class="attribute" name="sailthru_forms_options[sailthru_customfield_attr' + new_val + ']" type="text"  placeholder="key"/><input class="attribute" name="sailthru_forms_options[sailthru_customfield_attr' + second_val + ']" type="text"  placeholder="value"/>');
			$('#attr_amount').attr('value',second_val);
		}));
		
		
		
		// validate the form for saving api keys
		$("#sailthru-add-api-key-form").submit(function(e) {
			var isFormValid = true;
			$("input").each(function() {
				if ($.trim($(this).val()).length == 0) {
					$(this).addClass("error-highlight");
					isFormValid = false;
					e.preventDefault();
				} else {
					$(this).removeClass("error-highlight");
					isFormValid = true;
				}
			});
			return isFormValid;
		}); // end validate form submit
		// add a subscriber
		$("#sailthru-add-subscriber-form").submit(function(e) {
			e.preventDefault();
		});
		// set up form. make the email template more prominent
		$("#sailthru_setup_email_template").parents('tr').addClass('grayBorder');
		// datepicker for meta box
		$('.datepicker').datepicker({
			dateFormat: 'mm-dd-yy'
		});
	});
}(jQuery));