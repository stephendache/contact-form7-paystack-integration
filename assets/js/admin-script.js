document.addEventListener('DOMContentLoaded', function () {
    console.log('CF7 Paystack Admin Script Loaded');

    const actionButtons = document.querySelectorAll('.cf7-paystack-action');

    actionButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to perform this action?')) {
                e.preventDefault();
            }
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert('Settings saved successfully!');
    }
});


// Handle CF7 form redirect to Paystack after AJAX success
document.addEventListener('wpcf7mailsent', function(event) {
    if (event.detail.apiResponse && event.detail.apiResponse.redirect) {
        window.location.href = event.detail.apiResponse.redirect;
    }
}, false);
