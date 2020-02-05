;(function ($, window, document) {
    "use strict";

    let badgeHtmlTextAreaWidth = 568,
        badgePreviewMargin = 20,
        badgeWrappers = $("#dmca-badge-settings-badges").find(".badge-option-wrapper"),
        badgeUrlElem = $("#dmca-badge-settings-badge-url"),
        badgeUrl = badgeUrlElem.val(),
        badgeHtmlTextArea = $("#dmca-badge-settings-badge-html"),
        badgeInputArea = $("#field-html-input");


    $(document).on('ready', function () {
        badgeWrappers.find("img").click(function () {
            badgeWrappers.find("img.selected").removeClass("selected");
            $(this).addClass("selected");
            updateBadgePreview($(this).attr("src"), true);
        });

        if (typeof badgeUrl !== 'undefined' && badgeUrl.length) {
            updateBadgePreview(badgeUrl, false);
        }
    });


    /**
     * Update badge preview image
     *
     * @param badgeUrl
     * @param reinitialize
     */
    function updateBadgePreview(badgeUrl, reinitialize) {
        badgeInputArea.show();
        var badgeHtml = $("#badge-template").text().replace("{{badge_url}}", badgeUrl);
        if (reinitialize) {
            badgeUrlElem.val(badgeUrl);
            badgeHtmlTextArea.val(badgeHtml);
        }
        $("#badge-preview").remove();
        badgeHtmlTextArea.before("<div id=\"badge-preview\"><img src=\"" + badgeUrl + "\"/></div>");
        waitForImageLoad(badgeUrl, function () {
            var badgeWidth = parseInt($("#badge-preview").css("width").replace("px", ""));
            var newTextAreaWidth = badgeHtmlTextAreaWidth - badgeWidth - badgePreviewMargin;
            badgeHtmlTextArea.css("width", newTextAreaWidth + "px");
        });
    }

    /**
     * @see https://stackoverflow.com/a/1820460/102699
     */
    function waitForImageLoad(url, callback) {
        let image = new Image();
        image.onload = callback;
        image.src = url;
    }

})(jQuery, window, document);