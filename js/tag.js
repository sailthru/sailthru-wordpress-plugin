window.addEventListener("load", function() {
    // Exit early if Sailthru was blocked by an ad-blocker
    if(!window.Sailthru) return console.warn("Sailthru onsite JS failed to load.");
    
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