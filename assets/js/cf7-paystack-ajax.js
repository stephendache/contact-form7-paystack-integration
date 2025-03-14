document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.wpcf7-form');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Stop normal form submission

            const formData = new FormData(form);

            fetch('/wp-json/cf7-paystack/v1/initiate-payment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect; // Redirect to Paystack
                } else {
                    alert(data.message || 'Payment initiation failed.');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
