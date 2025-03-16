document.addEventListener('DOMContentLoaded', function () {
    console.log('CF7 Paystack Admin Script Loaded ✅');

    // ✅ Confirmations for critical actions (like reset, delete)
    const actionButtons = document.querySelectorAll('.cf7-paystack-action');

    actionButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const confirmMessage = button.dataset.confirm || 'Are you sure you want to perform this action?';
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });

    // ✅ Inline Admin Notice for Settings Saved (instead of alert)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const notice = document.createElement('div');
        notice.className = 'notice notice-success is-dismissible';
        notice.innerHTML = '<p>Settings saved successfully!</p>';
        const wrap = document.querySelector('.wrap h1');
        if (wrap) {
            wrap.insertAdjacentElement('afterend', notice);
        }
    }
});
