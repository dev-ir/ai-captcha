## aiCaptcha - AI-Powered, Frictionless Bot Protection for WordPress

> **Seamless security**. **Zero user interaction**.  
> aiCaptcha uses AI and server-side fingerprinting to block bots automatically without annoying your users.

## 🔍 Overview

aiCaptcha is a next-generation WordPress security plugin that:
- 🛡️ **Protects forms silently** (no CAPTCHA solving required)
- 🤖 **AI-powered detection** analyzes behavior patterns
- 🌐 **Server-side fingerprinting** validates requests
- ⚡ **Lightweight** (no heavy JavaScript)
- 🔌 **Works with any form** (login, registration, comments, WooCommerce, etc.)

## ✨ Key Features

### 🤖 Automatic Bot Detection
- AI analyzes mouse movements, typing speed, and interaction patterns
- Real-time scoring to distinguish humans from bots

### 🔒 Server-Side Protection
- IP reputation analysis
- TLS/HTTP fingerprinting
- Request consistency checks

### 🛠️ Easy Integration
- Works out-of-the-box with:
  - WordPress core forms (login/registration)
  - Comments
  - WooCommerce checkout
  - Contact Form 7, Gravity Forms, etc.
- Shortcode support for custom forms (`[aicaptcha]`)

### 📊 Dashboard & Analytics
- View blocked bot attempts
- Adjust sensitivity thresholds
- Whitelist/blacklist management

### 📊 Preview
<details>
![aiCaptcha Banner](https://github.com/dev-ir/wp-aiCaptcha/blob/master/screenshot.png) <!-- Optional: Add a banner image later -->
</details>

## 📦 Installation

1. **Manual Install**:
   - Download the latest `.zip` from [Releases](#)
   - Upload to WordPress via **Plugins → Add New → Upload**
   - Activate the plugin

2. **WP-CLI**:
   ```bash
   wp plugin install https://github.com/dev-ir/wp-aiCaptcha/archive/main.zip --activate