if (typeof jQuery !== 'undefined') {
    // Shim for curCSS which was removed in jQuery 1.8
    // Many older plugins (like jQuery UI 1.8.x) fail without this
    if (typeof jQuery.curCSS === 'undefined') {
        jQuery.curCSS = jQuery.css;
    }
}
