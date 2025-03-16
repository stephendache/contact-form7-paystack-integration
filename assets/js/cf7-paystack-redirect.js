document.addEventListener('wpcf7mailsent', function(event) {
    // Check if redirect URL is present in response
    if (event.detail.apiResponse && event.detail.apiResponse.redirect) {

        // ✅ Optional: Create a simple loading overlay (for better UX)
        const overlay = document.createElement('div');
        overlay.id = 'cf7-paystack-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.background = 'rgba(0,0,0,0.5)';
        overlay.style.color = '#fff';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.fontSize = '18px';
        overlay.style.zIndex = '9999';
        overlay.innerHTML = 'Redirecting to Paystack. Please wait...';
        document.body.appendChild(overlay);

        // ✅ Redirect after small delay to show message (optional, can remove delay if not needed)
        setTimeout(function() {
            window.location.href = event.detail.apiResponse.redirect;
        }, 1000); // 1 second delay for smoother transition

        // ✅ Fallback: If Paystack doesn't load, show error message (after 10 seconds)
        setTimeout(function() {
            overlay.innerHTML = 'Something went wrong. Please refresh the page or try again.';
        }, 10000); // 10 seconds
    } else {
        // ✅ No redirect URL found, log issue
        console.error('Paystack redirect URL not found in CF7 response.', event.detail.apiResponse);
    }
}, false);
