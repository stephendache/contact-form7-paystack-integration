```markdown
# 🎉 Contact Form 7 + Paystack Integration Plugin

A simple WordPress plugin that connects **Contact Form 7** with **Paystack** so people can make payments right from your forms!  
No more redirecting people to other places — they fill out a form, get taken to Paystack, pay, and you get notified. Easy!

---

## 🌐 Where is this used?
This plugin is built for [http://techoconference.com](http://techoconference.com), but you can use it on **any WordPress site running Contact Form 7**.

---

## 🚀 What can this plugin do?

- 💳 Seamless **Paystack Payment Integration** with Contact Form 7.
- ✅ **Dynamic Payment Initialization** from CF7 form submissions.
- 🔔 **Email notifications** sent **only after successful payment**.
- 📊 **Admin Dashboard** for managing transactions.
- 🌍 **Multi-currency support** (NGN, USD, GHS, etc.).
- 🔗 **Webhooks** for real-time transaction verification.

---

## ⚙️ How to Install

1. **Download the Plugin** (or clone from GitHub).
2. Go to **WordPress Admin > Plugins > Add New > Upload Plugin**.
3. Upload the zip file and activate it.
4. Head to **"CF7 Paystack Setup"** in the admin menu to add your Paystack keys and settings.
5. Done!

---

## ✍️ How to Use (Super Easy)

### 1. Add These Fields to Your Contact Form 7 Form

```plaintext
[text* your-name placeholder "Your Full Name"]
[email* email placeholder "Your Email"]
[number* amount placeholder "Amount (₦)"]
[submit "Pay with Paystack"]
```

**Note**: Make sure you use **email** and **amount** — these names are important.

---

### 2. Set Your Redirect URLs

During the setup, add:
- A **success URL** (where people land after paying — e.g., a "Thank You" page).
- A **failure URL** (if something goes wrong or payment is cancelled).

---

### 3. Set up Webhook on Paystack

To know when people actually pay, set this webhook in your Paystack dashboard:

```
http://yourwebsite.com/wp-json/cf7-paystack/v1/webhook/
```

---

## 🧑‍💻 How It Works Behind the Scenes

| Step                      | What Happens                                            |
|--------------------------|--------------------------------------------------------|
| User fills Contact Form 7 | Their info (amount, email) is collected.                |
| They hit Submit           | A secure call is made to Paystack to create a payment. |
| Redirect to Paystack     | User completes payment on Paystack's page.              |
| Webhook kicks in          | Paystack tells your site: "Payment done!"               |
| Email sent                | Confirmation email goes out **only if payment is successful**. |
| You can see transactions  | Everything is logged in WP Admin for you to review.    |

---

## 📁 File/Folder Breakdown (So You Know What’s What)

```
contact-form7-paystack-integration/
│
├── contact-form7-paystack-integration.php    // Main file
├── uninstall.php                            // Clean up on uninstall (optional)
│
├── includes/
│   ├── class-cf7-paystack-admin.php         // Admin dashboard
│   ├── class-cf7-paystack-setup.php         // Setup wizard
│   ├── class-cf7-paystack-webhook.php       // Handles webhook & sends emails
│   └── class-cf7-paystack-api.php           // NEW: Handles payment AJAX calls
│
├── assets/
│   ├── css/
│   │   └── admin-style.css                  // Admin page styles
│   └── js/
│       ├── cf7-paystack-ajax.js             // AJAX for form submission
│       └── cf7-paystack-redirect.js         // Redirect handler (optional)
│
└── languages/
    └── cf7-paystack.pot                     // Translation template (optional)
```

---

## ⚡ Tech Requirements

- WordPress 5.0 or later.
- Contact Form 7 plugin.
- Paystack merchant account (obviously!).

---

## 🤝 Want to Contribute? Cool!

If you’re a developer and want to improve this, here’s how to help:

```bash
# Clone it
git clone https://github.com/your-github-username/contact-form7-paystack-integration.git

# Make a new branch for your changes
git checkout -b feature-your-feature-name

# Make your changes, then commit
git add .
git commit -m "Added awesome feature"

# Push your branch
git push origin feature-your-feature-name

# Create a pull request on GitHub 🎉
```

---

## 🛡 Security & Clean Code Promise

- Sanitized and validated all data (email, amount, etc.).
- REST API used properly (no exposed endpoints).
- Transactions and form data are logged and handled securely.

---

## 📬 Support & Questions

Need help? Found a bug?  
- Open an **Issue** on GitHub.
- Email us: [iamstepaul@gmail.com](mailto:iamstepaul@gmail.com).

---

## 📃 License

MIT License — Free to use, modify, and share. Just give credit! 🎉

---

### 🚀 Let's Go Make Payments Easy!
```
