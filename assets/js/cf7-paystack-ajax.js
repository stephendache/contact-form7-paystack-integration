document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form.wpcf7-form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // ✅ Stop default CF7 submission

            const formData = new FormData(form);

            // ✅ Clean up previous error (if any)
            const previousError = form.querySelector('.cf7-paystack-error');
            if (previousError) {
                previousError.remove();
            }

            // ✅ Show loading overlay
            const overlay = createOverlay();
            document.body.appendChild(overlay);

            // ✅ Fetch API URL dynamically via localized object
            fetch(cf7_paystack_ajax.api_url, { // cf7_paystack_ajax.api_url passed from wp_localize_script
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    document.body.removeChild(overlay); // ✅ Remove overlay when done

                    if (data.redirect) {
                        // ✅ Redirect to Paystack checkout
                        window.location.href = data.redirect;
                    } else {
                        // ✅ Display inline error
                        const errorMessage = data.message || 'Payment initiation failed. Please try again.';
                        showCF7InlineError(form, errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Payment API Error:', error);
                    document.body.removeChild(overlay); // ✅ Remove overlay on error
                    showCF7InlineError(form, 'An unexpected error occurred. Please try again later.');
                });
        });
    });

    // ✅ Overlay creation function (for loading feedback)
    function createOverlay() {
        const overlay = document.createElement('div');
        overlay.id = 'cf7-paystack-ajax-overlay';
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
        overlay.innerHTML = 'Processing payment... Please wait.';
        return overlay;
    }

    // ✅ Inline Error Display Function (Better UX than alerts)
    function showCF7InlineError(form, message) {
        let errorDiv = form.querySelector('.cf7-paystack-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'cf7-paystack-error';
            errorDiv.style.color = 'red';
            errorDiv.style.margin = '10px 0';
            errorDiv.style.padding = '10px';
            errorDiv.style.border = '1px solid red';
            errorDiv.style.backgroundColor = '#ffecec';
            form.prepend(errorDiv);
        }
        errorDiv.textContent = message;
    }
});
