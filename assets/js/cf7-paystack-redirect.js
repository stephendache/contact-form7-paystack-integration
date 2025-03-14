document.addEventListener('wpcf7mailsent', function(event) {
    if (event.detail.apiResponse && event.detail.apiResponse.redirect) {
        alert('Redirecting to Paystack. Please wait...');
        window.location.href = event.detail.apiResponse.redirect;
    }
}, false);
