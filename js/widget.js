(function($){"use strict";$(function(){$("#sailthru-add-subscriber-form").submit(function(e){e.preventDefault();var t=$("#sailthru_ajax_php").val(),r=$(this).serialize();$.post(t,r,function(e){e=jQuery.parseJSON(e),!0==e.error?$("#sailthru-add-subscriber-errors").html(e.message):$("#sailthru-add-subscriber-form").html("Thank you for subscribing.")})})})})(jQuery);

