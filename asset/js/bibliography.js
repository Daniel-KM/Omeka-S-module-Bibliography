(function($) {

    $(document).ready(function() {

        $('.record-citation').on('click', function(e) {
            const button = $(this);
            const text = button.data('citation');
            // Navigator clipboard requires a secure connection (https).
            if (navigator.clipboard) {
                navigator.clipboard
                    .writeText(text)
                    .then(() => {
                        alert(button.data('textCopied') ? button.data('textCopied') : 'Bibliographic reference copied in clipboard!');
                    })
                    .catch(() => {
                        // Display the citation instead of a message of issue.
                        alert(text);
                    });
            } else {
                alert(text);
            }
        });

    });

})(jQuery);
