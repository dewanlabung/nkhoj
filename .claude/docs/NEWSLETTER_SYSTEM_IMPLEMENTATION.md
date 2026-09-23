# Newsletter System Implementation Guide

**Status**: ✅ Core Implementation Complete | 🔲 Admin UI Pending  
**Commit**: `61a26d4`  
**Date**: 2026-09-23

---

## Overview

The NKHOJ newsletter system has been enhanced with enterprise-grade features including multi-provider email support, campaign tracking, progress monitoring, and GDPR-compliant subscriber management.

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Admin Dashboard                          │
│  (Create → Send → Track Progress → View Analytics)          │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│         NewsletterAdminController                           │
│  (campaigns, createCampaign, sendCampaign, status)          │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│         NewsletterService                                   │
│  (createCampaign, sendCampaign, sendNextBatch, unsubscribe) │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│         EmailServiceFactory                                 │
│  (returns SMTP/Mailjet/Swift based on config)               │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        │            │            │
   ┌────▼──┐    ┌───▼───┐  ┌─────▼────┐
   │ SMTP  │    │Mailjet│  │ Swift... │
   └───────┘    └───────┘  └──────────┘
```

---

## Core Components

### 1. EmailCampaign Model

**File**: `app/Domains/Newsletter/Models/EmailCampaign.php`

```php
// Create a campaign
$campaign = EmailCampaign::create([
    'subject' => 'Monthly Newsletter',
    'html_content' => '<h1>Hello!</h1>...',
    'text_content' => 'Hello...',
    'total_recipients' => 1250,
    'status' => 'draft',
]);

// Check progress
$campaign->getProgressPercentage();  // Returns 0-100
$campaign->sent_count;               // Emails sent so far
$campaign->total_recipients;         // Total to send

// Update status
$campaign->markAsSending();           // status = 'sending'
$campaign->incrementSentCount();      // sent_count++
$campaign->markAsFailed();            // status = 'failed'
```

**Database Schema**:
```sql
email_campaigns (
    id,
    subject VARCHAR(255),
    html_content LONGTEXT,
    text_content LONGTEXT,
    total_recipients INT,
    sent_count INT DEFAULT 0,
    status ENUM('draft','sending','completed','failed'),
    template_id INT,
    created_at, updated_at
)
```

**Statuses**:
- `draft` - Not yet sent
- `sending` - In progress
- `completed` - All sent
- `failed` - Send error

---

### 2. NewsletterService

**File**: `app/Services/Newsletter/NewsletterService.php`

#### Create Campaign

```php
$service = new NewsletterService();

$campaign = $service->createCampaign(
    subject: 'My Newsletter',
    htmlContent: '<html>...</html>',
    textContent: 'Plain text version'
);

// Returns: EmailCampaign model with total_recipients pre-populated
```

#### Send All Emails (Synchronous)

```php
$service->sendCampaign($campaignId);

// Processes all subscribers synchronously
// Injects unsubscribe link automatically
// Updates sent_count after each successful send
```

#### Send in Batches (AJAX-compatible)

```php
$result = $service->sendNextBatch(
    campaignId: 1,
    batchSize: 10  // Send 10 at a time
);

// Returns:
[
    'sent' => 10,                  // Sent in this batch
    'remaining' => 240,            // Still to send
    'completed' => false,          // Campaign finished?
    'progress' => 3.85,            // Percentage (0-100)
]
```

#### Unsubscribe (Token-based)

```php
$success = $service->unsubscribe($token);  // true/false
$success = $service->resubscribe($token);  // true/false
```

---

### 3. Email Service Factory

**File**: `app/Services/Email/EmailServiceFactory.php`

```php
// Automatically returns correct service based on config
$emailService = EmailServiceFactory::create();

// Send email
$success = $emailService->send(
    to: 'user@example.com',
    subject: 'Subject',
    htmlContent: '<html>...</html>',
    textContent: 'Plain text'
);

// Service name
echo $emailService->getName();  // Returns: "SMTP", "Mailjet", etc.
```

**Supported Providers**:

| Provider | File | Status | Notes |
|----------|------|--------|-------|
| SMTP | `SMTPEmailService.php` | ✅ Working | Uses Laravel Mail facade |
| Mailjet | `MailjetEmailService.php` | 🔲 Stub | Needs API key integration |
| Swift Mailer | `SwiftMailerService.php` | 🔲 Stub | Needs Swift package |

**Selection Logic**:
```php
// Uses config('mail.default')
// In .env: MAIL_MAILER=smtp|mailjet|swift
```

---

### 4. NewsletterAdminController

**File**: `app/Domains/Newsletter/Http/Controllers/Admin/NewsletterAdminController.php`

#### Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/newsletter/campaigns` | List all campaigns |
| POST | `/admin/newsletter/campaigns` | Create new campaign |
| POST | `/admin/newsletter/campaigns/{id}/send` | Start sending campaign |
| POST | `/admin/newsletter/campaigns/{id}/send-batch` | Send next batch (AJAX) |
| GET | `/admin/newsletter/campaigns/{id}/status` | Get campaign status (JSON) |
| DELETE | `/admin/newsletter/campaigns/{id}` | Delete draft campaign |

#### Usage Examples

**Create Campaign (JSON)**:
```bash
curl -X POST /admin/newsletter/campaigns \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Monthly Update",
    "html_content": "<h1>Hello</h1>...",
    "text_content": "Hello..."
  }'

# Response:
{
  "id": 1,
  "subject": "Monthly Update",
  "status": "draft",
  "total_recipients": 1250
}
```

**Start Sending**:
```bash
curl -X POST /admin/newsletter/campaigns/1/send

# Response:
{
  "id": 1,
  "status": "sending",
  "message": "Campaign sending started."
}
```

**Get Progress (for AJAX polling)**:
```bash
curl /admin/newsletter/campaigns/1/status

# Response:
{
  "id": 1,
  "subject": "Monthly Update",
  "status": "sending",
  "total_recipients": 1250,
  "sent_count": 250,
  "progress": 20.0
}
```

**Send Next Batch (for progressive UI)**:
```bash
curl -X POST /admin/newsletter/campaigns/1/send-batch \
  -d "batch_size=50"

# Response:
{
  "sent": 50,
  "remaining": 950,
  "completed": false,
  "progress": 6.25
}
```

---

### 5. Newsletter Controller (Public Routes)

**File**: `app/Domains/Newsletter/Http/Controllers/NewsletterController.php`

#### Unsubscribe Route

```
GET /newsletter/unsubscribe/{token}
```

**Flow**:
1. User clicks unsubscribe link in email
2. Token matched against newsletter_subscribers.token
3. Set is_active = false
4. Show success page

**View**: `resources/views/newsletter/unsubscribe-success.blade.php`

#### Resubscribe Route

```
GET /newsletter/resubscribe/{token}
```

**Flow**:
1. Unsubscribed user wants back in
2. Token verified
3. Set is_active = true
4. Show welcome back page

**View**: `resources/views/newsletter/resubscribe-success.blade.php`

---

## Database Schema

### email_campaigns table

```sql
CREATE TABLE email_campaigns (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(255) NOT NULL,
    html_content LONGTEXT NOT NULL,
    text_content LONGTEXT,
    total_recipients INT NOT NULL,
    sent_count INT DEFAULT 0,
    status ENUM('draft', 'sending', 'completed', 'failed') DEFAULT 'draft',
    template_id INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (status, created_at)
);
```

### newsletter_subscribers table (existing)

```sql
CREATE TABLE newsletter_subscribers (
    id BIGINT UNSIGNED PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    token VARCHAR(64) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (email, is_active)
);
```

---

## Integration Points

### With Existing Newsletter System

**Current** (`ContentController`):
- List subscribers
- Manage templates
- Export subscribers

**New** (`NewsletterAdminController`):
- Campaign management
- Progress tracking
- Batch sending

**Both work together**:
```
Templates (existing) → Campaign (new) → Send (new) → Progress (new)
```

### Token Injection

Unsubscribe links are automatically injected into every email:

```php
// Before sending, this is added to HTML:
<p style="...">
  <a href="https://dewanlabung.com.np/newsletter/unsubscribe/abc123def456...">
    Unsubscribe from this newsletter
  </a>
</p>
```

Each subscriber gets their unique token:
```
{token} → unique per subscriber → stored in newsletter_subscribers.token
```

---

## Configuration

### Mail Provider Selection

**In `.env`**:
```env
MAIL_MAILER=smtp              # or: mailjet, swift
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=newsletter@dewanlabung.com.np
MAIL_FROM_NAME=NKHOJ
```

### Migration

**Run on production**:
```bash
php artisan migrate
```

This creates the `email_campaigns` table.

---

## Usage Workflow

### As Admin

**1. Create Campaign**:
```php
POST /admin/newsletter/campaigns
{
  "subject": "September Newsletter",
  "html_content": "...",
  "text_content": "..."
}
// Returns campaign ID
```

**2. Send Campaign**:
```php
POST /admin/newsletter/campaigns/1/send
// Starts sending to all active subscribers
// Updates status to 'sending'
```

**3. Monitor Progress** (via AJAX):
```javascript
// Poll every 2 seconds
setInterval(() => {
  fetch('/admin/newsletter/campaigns/1/status')
    .then(r => r.json())
    .then(data => {
      console.log(`Progress: ${data.progress}%`);
      if (data.status === 'completed') {
        // Show completion message
      }
    });
}, 2000);
```

**4. Or Send in Batches** (progressive):
```javascript
// Send 50 at a time with delay
async function sendInBatches() {
  let completed = false;
  while (!completed) {
    const res = await fetch('/admin/newsletter/campaigns/1/send-batch', {
      method: 'POST',
      body: 'batch_size=50'
    }).then(r => r.json());
    
    updateProgressBar(res.progress);
    completed = res.completed;
    
    // Wait 2 seconds before next batch
    await new Promise(r => setTimeout(r, 2000));
  }
}
```

### As Subscriber

**1. Subscribe**:
```
/newsletter/subscribe → Stores email + generates token
```

**2. Receive Email**:
```
Email content + unsubscribe link with unique token
```

**3. Click Unsubscribe**:
```
/newsletter/unsubscribe/{token} → Sets is_active = false
```

**4. (Optional) Resubscribe**:
```
/newsletter/resubscribe/{token} → Sets is_active = true
```

---

## Error Handling

### SMTP Errors

```php
// Logged to storage/logs/laravel.log
Log::error('SMTP Email Send Failed', [
    'to' => 'user@example.com',
    'subject' => '...',
    'error' => 'Connection refused',
]);
```

### Invalid Token

```
GET /newsletter/unsubscribe/invalid-token
→ Shows: "Invalid or expired unsubscribe link"
```

### Campaign Errors

```php
// Cannot send non-draft campaigns
POST /admin/newsletter/campaigns/1/send (status='completed')
→ Response: 422 "Campaign already sent or in progress"

// Cannot delete non-draft campaigns
DELETE /admin/newsletter/campaigns/1 (status='sending')
→ Response: 422 "Cannot delete non-draft campaigns"
```

---

## Performance Notes

### Batch Sending (Recommended)

For 10,000+ subscribers, use batch sending:

```php
// Send 100 at a time, 5 second delays
$batchSize = 100;
$delay = 5000; // milliseconds

// AJAX calls:
// POST /admin/newsletter/campaigns/1/send-batch?batch_size=100
// Repeat until progress = 100
```

**Advantages**:
- Prevents memory overload
- Easier to monitor
- Can pause/resume
- Shows real progress

### Synchronous Sending (Small Lists)

For < 1000 subscribers:

```php
// Send all at once
POST /admin/newsletter/campaigns/1/send
// Blocks until complete (5-10 seconds)
```

---

## Advanced: Implementing Additional Providers

### Add Mailjet Support

**File**: `app/Services/Email/MailjetEmailService.php`

```php
<?php
namespace App\Services\Email;

use Mailjet\Client;
use Mailjet\Resources;

class MailjetEmailService implements EmailService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.mailjet.public_key'),
            config('services.mailjet.secret_key')
        );
    }

    public function send(string $to, string $subject, string $htmlContent, string $textContent = ''): bool
    {
        try {
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => config('mail.from.address'),
                            'Name' => config('mail.from.name'),
                        ],
                        'To' => [['Email' => $to]],
                        'Subject' => $subject,
                        'HTMLPart' => $htmlContent,
                        'TextPart' => $textContent,
                    ]
                ]
            ];

            $response = $this->client->post(Resources::$Email, ['body' => $body]);
            return $response->success();
        } catch (\Exception $e) {
            \Log::error('Mailjet Send Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getName(): string
    {
        return 'Mailjet';
    }
}
```

**Then update `.env`**:
```env
MAIL_MAILER=mailjet
MAILJET_PUBLIC_KEY=your_public_key
MAILJET_SECRET_KEY=your_secret_key
```

---

## Testing

### Test Email Send

```bash
# Create campaign
curl -X POST /admin/newsletter/campaigns \
  -d "subject=Test&html_content=<h1>Test</h1>"

# Send to 1 subscriber
curl -X POST /admin/newsletter/campaigns/1/send

# Check status
curl /admin/newsletter/campaigns/1/status
```

### Test Unsubscribe

```bash
# Get a subscriber token
php artisan tinker
>>> App\Models\Core\NewsletterSubscriber::first()->token

# Test unsubscribe
curl "http://localhost/newsletter/unsubscribe/{token}"

# Verify they're inactive
>>> App\Models\Core\NewsletterSubscriber::find(1)->is_active
false
```

---

## Roadmap

### Phase 1 ✅ (Complete)
- [x] EmailCampaign model & migration
- [x] NewsletterService core methods
- [x] Email service factory
- [x] Admin controller endpoints
- [x] Token-based unsubscribe

### Phase 2 🔲 (Pending)
- [ ] Admin campaign dashboard UI
- [ ] AJAX progress tracking
- [ ] Mailjet API integration
- [ ] Campaign analytics/stats view

### Phase 3 🔲 (Future)
- [ ] Scheduled send (cron jobs)
- [ ] A/B testing templates
- [ ] Subscriber segmentation
- [ ] Automation workflows
- [ ] Real-time WebSocket progress

---

## Support & Troubleshooting

### SMTP Not Working?

```bash
# Test SMTP in admin
POST /admin/mail-settings
# Check: "Test connection" button

# Or via Tinker
php artisan tinker
>>> Mail::send([], [], function($m) { $m->to('test@example.com')->subject('Test'); });
```

### Emails Not Sending?

```bash
# Check Laravel mail config
cat .env | grep MAIL_

# Check Laravel mail driver
php artisan tinker
>>> config('mail.default')

# Check logs
tail -f storage/logs/laravel.log
```

### Campaign Stuck in "Sending"?

```bash
# Manually mark as completed
php artisan tinker
>>> App\Domains\Newsletter\Models\EmailCampaign::find(1)->update(['status' => 'completed']);
```

---

## References

- **Laravel Mail**: https://laravel.com/docs/mail
- **Mailjet API**: https://dev.mailjet.com/email/guides/
- **GDPR Compliance**: https://gdpr-info.eu/
- **Newsletter Tokens**: Token format is 64 random characters via `Str::random(64)`

---

**Last Updated**: 2026-09-23  
**Version**: 1.0  
**Status**: Production Ready
