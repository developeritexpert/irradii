if (typeof jQuery !== 'undefined' && typeof jQuery.curCSS === 'undefined') {
    jQuery.curCSS = function (element, attrib, force) {
        return jQuery(element).css(attrib);
    };
}
