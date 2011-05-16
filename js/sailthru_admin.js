var templates;

function sailthru_admin() {
	var t = this;
	
	this.init = function() {
		
		jQuery('[id^=list-]').each(function() {
			t.update_optins(this);
		});
		
		
		jQuery('[id^=list-]').click(function() {
			t.update_optins(this);
		});
		
		jQuery('#save_datafeeds').click(function() {
            var name = jQuery('#name').val();
            var url = jQuery('#url').val();
            var selected = jQuery('#datafeeds option:selected').text();
            var del = '';
            
            if (jQuery('#datafeeds').val() != 'new' && jQuery('#datafeeds').val()) {
                if (name != selected) {
                    var del = selected;
                }
            }

            if (name && url) {
                jQuery('#spinner').show();
                jQuery.post(ajaxurl, {
                    name: name, 
                    url: url, 
                    del: del, 
                    action: 'sailthru_save_datafeed'
                }, function(response) {
                    t.rebuild_select(response, name);
                    jQuery('#spinner').hide();
                }, 'json');
            } else {
                jQuery('#errors').text('You must enter a name and a url.');
            }
	    });
	    
	    jQuery('#delete_datafeed').click(function () {
	        if (jQuery('#datafeeds').val() && jQuery('#datafeeds').val() != 'new') {
    	        jQuery('#spinner').show();
    	        jQuery.post(ajaxurl, {
    	            name: jQuery('#datafeeds option:selected').text(), 
    	            action: 'sailthru_delete_datafeed'
                }, function(response) {
                    t.rebuild_select(response, '');
                    jQuery('#name').val('');
    	            jQuery('#url').val('');
                    jQuery('#spinner').hide();
                }, 'json');
            }
        });
	    
	    jQuery('#datafeeds').change(function() {
	        if (jQuery(this).val() == 'new' || !jQuery(this).val()) {
	            jQuery('#name').val('');
	            jQuery('#url').val('');
            } else {
                jQuery('#name').val(jQuery('#datafeeds option:selected').text());
                jQuery('#url').val(jQuery(this).val());
            }
        });

        // jQuery.post(ajaxurl, {
        //     action: 'sailthru_get_template_list'
        // }, function(response) {
        //     templates = response;
        // 
        //     if (!templates.length) {
        //         templates = {
        //           Wordpress_RSS: {
        //               subject: '{subject}', 
        //               html: '{foreach rss.channel.item as i}<a href="{i.link}">{i.title}</a><br/><br/>{/foreach}', 
        //               text: '{foreach rss.channel.item as i} {i.title} <{i.link}>{/foreach}', 
        //               name: 'Wordpress_RSS'
        //           }
        //       }
        //     }
        // 
        //     for (var i in templates) {
        //         jQuery('#template').append('<option value="' + templates[i]['name'] + '">' + templates[i]['name'] + '</option>');
        //     }
        // }, 'json');

        jQuery('#get_template').click(function() {
            t.load_template();
            return false;
        });
        
        jQuery('#prefill').click(function() {
            var datafeed = jQuery('#datafeeds').val();
            if (datafeed) {
                jQuery('#prefill_spinner').show();
                jQuery.post(ajaxurl, {
                    action: 'sailthru_get_datafeed_html', 
                    datafeed: datafeed, 
                    template: jQuery('#template').val()
                }, function(response) {
                    jQuery('#from_name').val(response['from_name']);
                    jQuery('#subject').val(response['subject']);
                    tinyMCE.getInstanceById('content').setContent(response['html']);
                    jQuery('#text_body').val(response['text']);
                    jQuery('#prefill_spinner').hide();
                }, 'json');
            }
            return false;
        });
        
        if (jQuery('#template').val()) {
            jQuery('#datafeed_tr').show();
            t.load_template();
        }
	};
	
	this.load_template = function() {
	    var template = jQuery('#template').val();

        if (template) {
            jQuery('#template_spinner').show();
            jQuery.post(ajaxurl, {
                template: template, 
                action: 'sailthru_get_template_info'
            }, function(response) {
                jQuery('#subject').val(response['subject']);
                try {
                    tinyMCE.activeEditor.setContent(response['content_html']);
                } catch (err) {}
                jQuery('#content').val(response['content_html']);
                jQuery('#text_body').val(response['content_text']);
                jQuery('#from_name').val(response['from_name']);
                jQuery('#from_email').val(response['from_email']);
                jQuery('#datafeed_tr').show();
                jQuery('#template_spinner').hide();
            }, 'json');
        } else {
            jQuery('#datafeed_tr').hide();
        }
    }
	
	this.rebuild_select = function(options, selected) {
	    jQuery('#datafeeds').empty().append('<option value=""></option><option value="new">Add New</option>');
        for (var i in options) {
            jQuery('#datafeeds').append('<option value="' + options[i] + '">' + i + '</option>');
        }
        jQuery('#datafeeds').val(selected);
    }
	
	this.update_optins = function(checkbox) {
		var id = jQuery(checkbox).attr('id').split('-')[1];

		if(jQuery(checkbox).attr('checked')) {
			jQuery('#fields').append(
			'<tr id="custom_field-' + id + '">\n' +
				'<td>\n' +
					jQuery('#list_label-' + id).text() + ' Opt-in\n' +
				'</td>\n' +
				'<td>\n' +
					'[optin-' + id + ' "class"]\n' +
				'</td>\n' +
			'</tr>\n'
			);
		}
		else {
			jQuery('#custom_field-' + id).remove();
		}
	}
	
}

jQuery(document).ready(function() {
	var t = new sailthru_admin();
	t.init();
});