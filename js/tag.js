window.addEventListener("load", function() {
    console.info("Sailthru onsite JS is loaded. Initializing Sailthru...");
    if (tag.isCustom) {
        jQuery(function($) {
            Sailthru.init({
                customerId: tag.options.customerId,
                isCustom: true,
                autoTrackPageview: tag.options.autoTrackPageview,
                useStoredTags: tag.options.useStoredTags,
                excludeContent: tag.options.excludeContent,
            });
        });
    } else {
        Sailthru.init({
            customerId: tag.options.customerId
        });
    }
});