/**
 * Display a custom alert modal with configurable content, title, and footer.
 * @param {string} data - The message or HTML content to display in the alert.
 * @param {string} [title='Message from server'] - The title of the alert.
 * @param {string} [footer='<div class="phpl-alert-btn-danger phpl-alert-close">Close</div>'] - The footer HTML, typically for action buttons.
 * @param {string} [xid] - Optional unique ID for the alert. If not provided, a unique ID will be generated.
 * @returns {string} - The ID of the created alert element.
 */
function phpl_alert(data, title = 'Message from server', footer = '<div class="phpl-alert-btn-danger phpl-alert-close">Close</div>', xid) {
    // Generate a unique ID for the alert if not provided
    let xtot = xid || 'phpl-alert-' + ($('.phpl-alert-ctr').length + 1);

    // Check for ID conflicts and warn if necessary
    if (xid && $('#' + xtot).length) {
        console.warn(`Alert with ID "${xtot}" already exists. Overwriting.`);
    }

    // Construct the alert HTML
    const xhtml = `
        <div id="${xtot}" style="display:none;" class="phpl-alert-ctr">
            <div class="phpl-alert-bg"></div>
            <div class="phpl-alert-box-outer">
                <div class="phpl-alert-box-inner">
                    <div class="phpl-alert-box">
                        <div class="phpl-alert-box-top">
                            <div class="phpl-alert-box-top-text">${title}</div>
                            <div class="phpl-alert-box-top-close" title="Close">X</div>
                            <div style="clear:both;"></div>
                        </div>
                        <div class="phpl-alert-box-middle">${data}</div>
                        <div class="phpl-alert-box-bottom">${footer}
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Insert the alert into the DOM
        $('body').prepend(xhtml);

    // Apply visual effects to background
    $('#flscrn').addClass('blurred');
    $('#' + xtot).fadeIn("slow");

    // Return the alert ID for reference
    return xtot;
}

/**
 * Display a custom confirmation modal with configurable content and callback.
 * @param {string} data - The message or HTML content for the confirmation modal.
 * @param {function} on_confirm - Callback function to execute if "Continue" is clicked.
 * @param {string} [xid] - Optional unique ID for the modal. If not provided, a unique ID will be generated.
 */
function phpl_confirm(data, on_confirm, xid) {
    if (typeof on_confirm !== 'function') {
        console.error('on_confirm must be a valid function.');
        return;
    }

    // Generate or ensure a unique ID for the confirmation dialog
    xid = xid || 'phpl-alert-' + ($('.phpl-alert-ctr').length + 1);

    // Footer with Cancel and Continue buttons
    const footer = `
        <div class="phpl-alert-btn-danger phpl-alert-close">Cancel</div>
        <div class="phpl-alert-btn-success phpl-alert-continue" data-xid="${xid}">Continue</div>
    `;

    // Create and display the alert
    phpl_alert(data, 'Please confirm to continue', footer, xid);

    // Bind the "Continue" button's click event for this specific modal
    $(document).on('click', `.phpl-alert-continue[data-xid="${xid}"]`, function () {
        on_confirm(); // Execute the callback
        phpl_close_alert(xid); // Close the modal
        $(document).off('click', `.phpl-alert-continue[data-xid="${xid}"]`); // Unbind the event
    });
}

/**
 * Close and remove a custom alert modal from the DOM.
 * @param {string} xid - The unique ID of the alert to close.
 */
function phpl_close_alert(xid) {
    if (!xid) {
        console.warn('No xid provided. Defaulting to "phpl-alert-1".');
        xid = 'phpl-alert-1';
    }

    // Fade out and remove the alert
    $('#' + xid).fadeOut("slow", function () {
        $(this).remove(); // Remove from DOM
        $('#flscrn').removeClass('blurred'); // Remove blur effect
    });
}

/**
 * Global event binding for modal close actions.
 */
jQuery(document).ready(function () {
    // Close modals when clicking on close buttons
    $('body').on('click', '.phpl-alert-close, .phpl-alert-box-top-close', function () {
        const xid = $(this).closest('.phpl-alert-ctr').attr('id');
        phpl_close_alert(xid);
    });
});
