# Varient Newsletter System - Skills & Architecture Documentation

## Executive Summary

The **Varient Newsletter System** is a CodeIgniter 4-based multi-provider email management platform with advanced subscriber management and bulk email capabilities. Compared to your NKHOJ system and MailWizz, it provides enterprise-grade email infrastructure with flexibility for different mail providers.

---

## 1. Architecture Overview

### Stack
- **Framework**: CodeIgniter 4
- **Mail Providers**: Mailjet, Swift Mailer, PHPMailer
- **Email Editor**: TinyMCE (rich text editor)
- **Database**: MySQL (subscribers table structure)
- **Frontend**: Bootstrap 3 with jQuery/AJAX

### Core Models
- **NewsletterModel**: Subscriber management (CRUD operations)
- **EmailModel**: Email sending logic with multi-provider support
- **Config/Email.php**: Global email configuration

---

## 2. Comparison: NKHOJ vs Varient vs MailWizz

| Feature | NKHOJ | Varient | MailWizz |
|---------|-------|---------|----------|
| **Provider Support** | Basic SMTP | Mailjet, Swift, PHPMailer | Multiple (30+) |
| **Batch Sending** | Per-email | Batch with progress tracking | Advanced queue system |
| **Subscriber Tokens** | ❌ | ✅ (Unsubscribe) | ✅ (Full tracking) |
| **Template Engine** | Simple | TinyMCE WYSIWYG | Advanced templating |
| **Progress Tracking** | ❌ | ✅ (AJAX-based) | ✅ (Real-time) |
| **Email Encryption** | TLS/SSL | TLS/SSL | TLS/SSL/DKIM/SPF |
| **API Integration** | ❌ | Partial (Mailjet) | ✅ (Full REST API) |
| **Automation** | ❌ | Manual only | ✅ (Workflows) |

---

## 3. Key Skills & Capabilities

### 3.1 Multi-Provider Email System

**Current Implementation:**
```php
// Three mail service options:
1. Mailjet (Cloud service, best for compliance)
2. Swift Mailer (PHP library, lightweight)
3. PHPMailer (Most compatible)
```

**Selection Logic:**
- `mail_service == 'mailjet'` → Use Mailjet API
- `mail_service == 'swift'` → Use Swift SMTP
- Default → Use PHPMailer

**Configuration Variables:**
- `mail_host`, `mail_port`, `mail_username`, `mail_password`
- `mail_encryption` (tls/ssl), `mail_protocol` (smtp/mail)
- `mail_title`, `mail_reply_to`

**Advantage over NKHOJ**: Built-in provider switching without code changes.

---

### 3.2 Subscriber Management with Tokens

**Core Functions:**

| Function | Purpose | NKHOJ Equivalent |
|----------|---------|------------------|
| `addSubscriber($email)` | Register new subscriber with generated token | ❌ Missing |
| `updateSubscriberToken($email)` | Generate unsubscribe token if missing | ❌ Missing |
| `getSubscriberEmailsByIds($ids)` | Batch email retrieval | Partial |
| `unsubscribeEmail($email)` | One-click unsubscribe support | ❌ Missing |
| `getSubscriberByToken($token)` | Token-based verification | ✅ Critical for GDPR |

**Database Schema:**
```sql
CREATE TABLE subscribers (
    id INT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    token VARCHAR(255),
    created_at TIMESTAMP
);
```

**Recommendation for NKHOJ**: Implement token-based unsubscribe links in emails:
```html
<a href="<?= site_url('newsletter/unsubscribe/' . $subscriber->token) ?>">
  Unsubscribe
</a>
```

---

### 3.3 Bulk Email Sending with Progress Tracking

**Workflow:**
1. Admin selects emails to send
2. Form submission collects Subject + Body (TinyMCE HTML)
3. AJAX loop processes emails one-by-one
4. UI shows real-time progress with checkmarks
5. Spinner animation during sending

**JavaScript Flow:**
```javascript
sendNewsletterEmail() {
  1. Get next unsent email from arrayEmails
  2. POST to /Admin/newsletterSendEmailPost
  3. If success: remove from pending, add to sent list
  4. Repeat until arrayEmails empty
  5. Show "Completed" message
}
```

**Key Advantage**: Frontend never waits for all emails - sends in sequence with AJAX callbacks.

---

### 3.4 Email Template System

**Available Templates:**
1. `email/email_newsletter` - Bulk newsletter template
2. `email/email_activation` - User account activation
3. `email/email_reset_password` - Password recovery
4. `email/email_contact_message` - Contact form submissions

**Template Variables Passed:**
```php
$data = [
    'subject' => 'Email Subject',
    'message' => 'HTML body content',
    'to' => 'recipient@example.com',
    'template_path' => 'email/email_newsletter',
    'subscriber' => $subscriber, // For unsubscribe token
    // ... other template-specific vars
];
```

**Recommendation**: Create Blade template versioning system:
- `email_newsletter.blade.php` - Default
- `email_newsletter_dark.blade.php` - Dark mode variant
- `email_newsletter_v2.4.3.blade.php` - Variant-specific

---

### 3.5 Mail Provider Implementations

#### **Mailjet (Recommended for Scale)**
```php
// Pros: Highest deliverability, compliance, analytics
// Cons: Third-party dependency, API quota
// Use Case: Production, HIPAA/GDPR compliance

$mj = new Mailjet\Client($api_key, $secret_key);
// Supports bounces, complaints, opens, clicks tracking
```

#### **Swift Mailer (Balanced)**
```php
// Pros: Lightweight, no external APIs
// Cons: Less robust error handling
// Use Case: Self-hosted, low-volume

$transport = new Swift_SmtpTransport($host, $port);
```

#### **PHPMailer (Most Compatible)**
```php
// Pros: Widest hosting support, fallback to mail()
// Cons: Less modern
// Use Case: Shared hosting, legacy systems

// Supports both SMTP and mail() protocol
```

---

### 3.6 UI/UX Components

**Send Email Form Features:**
- Subject input
- TinyMCE rich text editor with formatting toolbar
- Image insertion from file manager
- Email list display with scrollable container
- Send progress spinner with email checklist
- Completion notification

**Real-time Updates:**
- Recipient list preview (max-height 150px, scrollable)
- Sent emails list (max-height 300px, scrollable)
- Spinner animation during sending
- Disabled buttons until completion

---

## 4. Recommendations for NKHOJ Enhancement

### 4.1 Implement Varient's Subscriber Token System

**Step 1**: Add `token` column to `subscribers` table
```sql
ALTER TABLE subscribers ADD COLUMN token VARCHAR(255) UNIQUE;
```

**Step 2**: Generate token on subscription
```php
public function addSubscriber($email) {
    return DB::table('subscribers')->insert([
        'email' => $email,
        'token' => Str::random(40),
        'created_at' => now()
    ]);
}
```

**Step 3**: Add unsubscribe route
```php
Route::get('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);
```

**Benefit**: GDPR-compliant one-click unsubscribe.

---

### 4.2 Add Multiple Mail Provider Support

**Create Service Class:**
```php
// app/Services/EmailServiceFactory.php
class EmailServiceFactory {
    public static function create($provider) {
        return match($provider) {
            'mailjet' => new MailjetService(),
            'swift' => new SwiftMailerService(),
            'phpmailer' => new PHPMailerService(),
        };
    }
}
```

**Benefits:**
- Provider switching without code changes
- Fallback to alternative provider if primary fails
- Provider-specific analytics/tracking

---

### 4.3 Implement Progress Tracking

**Database Addition:**
```sql
CREATE TABLE email_campaigns (
    id INT PRIMARY KEY,
    subject VARCHAR(255),
    body LONGTEXT,
    total_recipients INT,
    sent_count INT,
    status ENUM('draft', 'sending', 'completed', 'failed'),
    created_at TIMESTAMP
);
```

**Benefits:**
- Resume interrupted campaigns
- Analytics: open rates, click rates per campaign
- Campaign history and archiving

---

### 4.4 Rich Email Templates

**Implement Template Inheritance:**
```blade
<!-- resources/views/emails/layouts/newsletter.blade.php -->
<body>
    <table width="100%">
        <tr>
            <td>{{ $siteConfig->logo }}</td>
        </tr>
        <tr>
            <td>
                @yield('content')
            </td>
        </tr>
        <tr>
            <td>
                <a href="{{ route('newsletter.unsubscribe', $subscriber->token) }}">
                    Unsubscribe
                </a>
            </td>
        </tr>
    </table>
</body>
```

---

## 5. Implementation Skills Checklist

- [ ] **Email Configuration**: Set mail provider, protocol, encryption settings
- [ ] **Subscriber Management**: CRUD operations with token generation
- [ ] **Bulk Sending**: Batch processing with queue or AJAX loop
- [ ] **Template System**: Multiple template versions with variable substitution
- [ ] **Provider Switching**: Factory pattern for provider abstraction
- [ ] **Error Handling**: Retry logic, fallback providers, logging
- [ ] **Compliance**: GDPR-compliant unsubscribe, DKIM/SPF/DMARC
- [ ] **Analytics**: Track opens, clicks, bounces per campaign
- [ ] **Webhooks**: Receive provider events (bounce, complaint, open)

---

## 6. Code Quality Improvements Over NKHOJ

| Aspect | NKHOJ | Varient | Recommendation |
|--------|-------|---------|-----------------|
| **Provider Abstraction** | Direct SMTP | Factory pattern | ✅ Adopt Varient |
| **Error Handling** | Generic | Try-catch per provider | ✅ Adopt Varient |
| **Token Security** | N/A | Random 40-char | ✅ Implement |
| **Async Sending** | N/A | AJAX progressive | ✅ Implement |
| **Template System** | Basic includes | View-based Blade | ✅ Implement |
| **Unsubscribe** | Manual only | Token-based URL | ✅ Implement |

---

## 7. Migration Path: NKHOJ → Varient Pattern

### Phase 1: Core Newsletter (Week 1)
- [ ] Add subscriber token field
- [ ] Implement unsubscribe route
- [ ] Create token-based unsubscribe links

### Phase 2: Multi-Provider (Week 2)
- [ ] Create EmailServiceFactory
- [ ] Implement MailjetService, SwiftMailerService
- [ ] Add provider switching UI in settings

### Phase 3: Progress Tracking (Week 3)
- [ ] Create campaigns table
- [ ] Implement AJAX progressive sending
- [ ] Add campaign history page

### Phase 4: Advanced Features (Week 4+)
- [ ] Template versioning system
- [ ] Analytics dashboard
- [ ] Webhook integration for provider events
- [ ] A/B testing framework

---

## 8. Files to Reference

**In nkhoj repo (.claude/varient/Upload):**
- `app/Models/NewsletterModel.php` - Subscriber logic
- `app/Models/EmailModel.php` - Multi-provider sending
- `app/Config/Email.php` - Configuration schema
- `app/Views/admin/newsletter/send_email.php` - UI/UX pattern
- `app/Views/email/email_newsletter.php` - Template example

---

## 9. Deployment Considerations

### Mail Provider Setup

**Mailjet (Recommended for Production):**
```env
MAIL_SERVICE=mailjet
MAILJET_API_KEY=your_api_key
MAILJET_SECRET_KEY=your_secret
MAILJET_EMAIL_ADDRESS=noreply@nkhoj.com
```

**SMTP Fallback:**
```env
MAIL_SERVICE=phpmailer
MAIL_PROTOCOL=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=app_password
```

---

## 10. Summary: Skills Acquired from Varient

1. ✅ Multi-provider email pattern
2. ✅ Subscriber token-based unsubscribe
3. ✅ AJAX progressive bulk sending
4. ✅ Try-catch error handling per provider
5. ✅ View-based email templating
6. ✅ Rich HTML email editor integration
7. ✅ Real-time progress UI patterns
8. ✅ Email configuration factory pattern

---

**Created**: 2026-09-23  
**For**: NKHOJ Newsletter System Enhancement  
**Reference**: Varient v2.4.3 Newsletter System
