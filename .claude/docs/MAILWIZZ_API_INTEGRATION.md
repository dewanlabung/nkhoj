# MailWizz API Integration Guide for NKHOJ

## Overview

MailWizz is an enterprise-grade email marketing platform with a comprehensive REST API. This guide shows how to integrate MailWizz API into NKHOJ for advanced newsletter management compared to Varient's local implementation.

---

## 1. Architecture Comparison

### Varient (Local Implementation)
```
User → NKHOJ Admin UI → NewsletterModel → EmailModel → SMTP/Mailjet
                                              ↓
                                        3 Email Providers
```

### MailWizz (Cloud Service)
```
User → NKHOJ Admin UI → MailWizz API Client → MailWizz Platform → All ISPs
                                                    ↓
                        Subscriber Management, Campaign Tracking, Analytics
```

---

## 2. MailWizz Core API Endpoints

### 2.1 Subscribers Endpoint
```php
$endpoint = new EmsApi\Endpoint\ListSubscribers();

// Get all subscribers
$response = $endpoint->getSubscribers('LIST-UNIQUE-ID', $page = 1, $per_page = 10);

// Get single subscriber
$response = $endpoint->getSubscriberByEmail('LIST-UNIQUE-ID', 'user@example.com');

// Create subscriber
$subscriber = new EmsApi\Endpoint\ListSubscribers();
$subscriber->addSubscriber('LIST-UNIQUE-ID', [
    'EMAIL'  => 'user@example.com',
    'FNAME'  => 'John',
    'LNAME'  => 'Doe',
    'status' => 'subscribed'
]);

// Update subscriber
$subscriber->updateSubscriber('LIST-UNIQUE-ID', 'SUBSCRIBER-UID', [
    'EMAIL'  => 'newemail@example.com',
    'status' => 'unsubscribed'
]);

// Delete subscriber
$subscriber->deleteSubscriber('LIST-UNIQUE-ID', 'SUBSCRIBER-UID');
```

**Response Structure:**
```json
{
  "status": "success",
  "data": {
    "count": "100",
    "total_pages": 10,
    "current_page": 1,
    "next_page": 2,
    "prev_page": null,
    "records": [
      {
        "subscriber_uid": "ll381bxshm01e",
        "EMAIL": "user@example.com",
        "FNAME": "John",
        "LNAME": "Doe",
        "status": "subscribed",
        "source": "import",
        "ip_address": "192.168.1.1",
        "date_added": "2021-02-20 17:26:18"
      }
    ]
  }
}
```

---

### 2.2 Campaigns Endpoint
```php
$endpoint = new EmsApi\Endpoint\Campaigns();

// Get all campaigns
$response = $endpoint->getCampaigns($page = 1, $per_page = 10);

// Get single campaign
$response = $endpoint->getCampaignByUid('CAMPAIGN-UID');

// Create campaign
$campaign = new EmsApi\Endpoint\Campaigns();
$campaign->createCampaign([
    'name'        => 'My Newsletter',
    'list_uid'    => 'LIST-UID',
    'subject'     => 'Newsletter - September 2026',
    'from_name'   => 'NKHOJ',
    'from_email'  => 'newsletter@nkhoj.com',
    'reply_to'    => 'support@nkhoj.com',
    'html_content' => '<html>...</html>',
    'text_content' => 'Plain text...',
    'status'      => 'draft'
]);

// Send campaign
$campaign->sendCampaign('CAMPAIGN-UID');

// Get campaign stats
$response = $endpoint->getCampaignStats('CAMPAIGN-UID');
```

**Campaign Stats Response:**
```json
{
  "status": "success",
  "data": {
    "campaign_uid": "og943e5q6e158",
    "name": "Newsletter",
    "processed": "100",
    "sent": "95",
    "bounced": "2",
    "opens": "45",
    "clicks": "12",
    "unsubscribes": "1",
    "complaints": "0",
    "hard_bounces": "2",
    "soft_bounces": "0"
  }
}
```

---

### 2.3 Lists Endpoint
```php
$endpoint = new EmsApi\Endpoint\Lists();

// Get all lists
$response = $endpoint->getLists($page = 1, $per_page = 10);

// Create list
$list = new EmsApi\Endpoint\Lists();
$list->createList([
    'name' => 'My Newsletter List',
    'default_from_name' => 'NKHOJ',
    'default_from_email' => 'newsletter@nkhoj.com',
    'default_reply_to' => 'support@nkhoj.com'
]);
```

---

### 2.4 Templates Endpoint
```php
$endpoint = new EmsApi\Endpoint\Templates();

// Get all templates
$response = $endpoint->getTemplates($page = 1, $per_page = 10);

// Create template
$template = new EmsApi\Endpoint\Templates();
$template->createTemplate([
    'name' => 'Newsletter Template',
    'content' => '<html>...</html>'
]);

// Use template in campaign
$campaign->createCampaign([
    'template_uid' => 'TEMPLATE-UID',
    // ... other fields
]);
```

---

### 2.5 Transactional Emails Endpoint
```php
$endpoint = new EmsApi\Endpoint\TransactionalEmails();

// Send transactional email
$response = $endpoint->sendEmail([
    'to'      => 'user@example.com',
    'subject' => 'Your Account Confirmation',
    'body'    => '<html>Confirm your email...</html>'
]);

// Similar to Varient's email activation emails
```

---

## 3. Comparison: Varient vs MailWizz

| Feature | Varient | MailWizz |
|---------|---------|----------|
| **Infrastructure** | Self-hosted | Cloud-hosted |
| **Provider Management** | Manual (3 options) | Automatic (30+ ISPs) |
| **Subscriber Storage** | Local DB | MailWizz cloud |
| **Campaign Management** | Manual UI | Full API/UI |
| **Analytics** | Basic logging | Advanced (opens, clicks, bounces) |
| **Bounce Handling** | Manual | Automatic |
| **Unsubscribe Compliance** | Token-based | Built-in compliance |
| **API Quality** | ~50 methods | 200+ methods |
| **Multi-language Fields** | No | Yes (FNAME, LNAME, custom) |
| **Webhooks** | No | Yes (delivery events) |
| **Cost** | Free (server) | $30-300+/month |
| **Scalability** | Limited | Unlimited |
| **Deliverability** | Good | Excellent (99%+) |

---

## 4. Implementation Strategy for NKHOJ

### Phase 1: MailWizz Account Setup (1 day)
1. Create MailWizz account at https://www.mailwizz.com/
2. Verify domain (DKIM, SPF, DMARC)
3. Create first mailing list
4. Generate API public & private key

### Phase 2: API Integration (3-5 days)

**Install MailWizz SDK:**
```bash
composer require mailwizz/mailwizz-php-sdk
```

**Create MailWizz Service:**
```php
// app/Services/MailWizzService.php
namespace App\Services;

use EmsApi\Config;
use EmsApi\Endpoint\ListSubscribers;
use EmsApi\Endpoint\Campaigns;

class MailWizzService
{
    protected $config;
    
    public function __construct()
    {
        $this->config = new Config([
            'apiUrl'       => config('services.mailwizz.api_url'),
            'publicKey'    => config('services.mailwizz.public_key'),
            'privateKey'   => config('services.mailwizz.private_key'),
        ]);
    }
    
    public function getSubscribers($listUid, $page = 1, $perPage = 10)
    {
        $endpoint = new ListSubscribers($this->config);
        return $endpoint->getSubscribers($listUid, $page, $perPage);
    }
    
    public function addSubscriber($listUid, $email, $firstName = '', $lastName = '')
    {
        $endpoint = new ListSubscribers($this->config);
        return $endpoint->addSubscriber($listUid, [
            'EMAIL' => $email,
            'FNAME' => $firstName,
            'LNAME' => $lastName,
            'status' => 'subscribed'
        ]);
    }
    
    public function createCampaign($data)
    {
        $endpoint = new Campaigns($this->config);
        return $endpoint->createCampaign([
            'name'          => $data['name'],
            'list_uid'      => $data['list_uid'],
            'subject'       => $data['subject'],
            'from_name'     => config('services.mailwizz.from_name'),
            'from_email'    => config('services.mailwizz.from_email'),
            'reply_to'      => config('services.mailwizz.reply_to'),
            'html_content'  => $data['html_content'],
            'text_content'  => $data['text_content'] ?? strip_tags($data['html_content']),
        ]);
    }
}
```

**Environment Configuration:**
```env
MAILWIZZ_API_URL=https://api.mailwizz.com
MAILWIZZ_PUBLIC_KEY=your_public_key
MAILWIZZ_PRIVATE_KEY=your_private_key
MAILWIZZ_LIST_UID=list_unique_id
MAILWIZZ_FROM_NAME=NKHOJ
MAILWIZZ_FROM_EMAIL=newsletter@nkhoj.com
MAILWIZZ_REPLY_TO=support@nkhoj.com
```

### Phase 3: Replace Newsletter Controller (3-5 days)

**Before (Varient pattern):**
```php
// app/Http/Controllers/NewsletterController.php
public function sendNewsletters(Request $request)
{
    $emailModel = new EmailModel();
    foreach ($request->subscriber_ids as $id) {
        $subscriber = $this->getSubscriber($id);
        $emailModel->sendEmailNewsletter($subscriber, $request->subject, $request->body);
    }
}
```

**After (MailWizz pattern):**
```php
public function sendNewsletters(Request $request)
{
    $mailwizz = new MailWizzService();
    
    // Create campaign on MailWizz
    $campaign = $mailwizz->createCampaign([
        'name' => $request->campaign_name,
        'list_uid' => config('services.mailwizz.list_uid'),
        'subject' => $request->subject,
        'html_content' => $request->body,
    ]);
    
    if ($campaign->status == 'success') {
        // Send campaign
        $response = $mailwizz->sendCampaign($campaign->data->campaign_uid);
        
        // Campaign now tracked on MailWizz (opens, clicks, bounces)
        return redirect()->back()->with('success', 'Campaign sent!');
    }
}
```

### Phase 4: Webhooks for Events (2-3 days)

**Receive MailWizz Events:**
```php
// routes/web.php
Route::post('/webhooks/mailwizz', [WebhookController::class, 'handleMailWizzEvent']);

// app/Http/Controllers/WebhookController.php
public function handleMailWizzEvent(Request $request)
{
    $event = $request->input('event');
    $data = $request->input('data');
    
    switch($event) {
        case 'delivery.status':
            $this->handleDeliveryStatus($data);
            break;
        case 'campaign.open':
            $this->handleCampaignOpen($data);
            break;
        case 'campaign.click':
            $this->handleCampaignClick($data);
            break;
        case 'bounce':
            $this->handleBounce($data);
            break;
        case 'unsubscribe':
            $this->handleUnsubscribe($data);
            break;
    }
}
```

---

## 5. Migration Path

### Step 1: Sync Subscribers (1 week)
- Export all subscribers from NKHOJ
- Import into MailWizz using API
- Validate email counts match

### Step 2: Create Test Campaign (1 week)
- Create campaign in MailWizz
- Send to segment (10% of list)
- Monitor opens/clicks/bounces
- Compare with Varient's manual tracking

### Step 3: Full Migration (2 weeks)
- Switch NKHOJ UI to MailWizz API
- Archive old email logs
- Setup webhook receivers
- Train team on new UI

### Step 4: Optimize (Ongoing)
- Monitor deliverability metrics
- Adjust sending times based on opens
- Implement A/B testing
- Create template library

---

## 6. Cost-Benefit Analysis

### Varient (Current)
- **Cost**: $0 (self-hosted)
- **Setup Time**: Already done
- **Deliverability**: 85-90% (ISP spam filters)
- **Features**: Basic email sending
- **Scalability**: Limited (shared hosting)

### MailWizz
- **Cost**: $30-300/month (based on subscribers)
- **Setup Time**: 1-2 weeks
- **Deliverability**: 98%+ (dedicated IP option)
- **Features**: Advanced automation, analytics, compliance
- **Scalability**: Unlimited

### ROI
- **Break-even**: 5-10% improvement in deliverability × subscriber count
- **For 1000 subscribers @ $0.10 CPC**: 5% improvement = $5 additional revenue per month
- **Payback period**: 6 months

---

## 7. Hybrid Approach (Recommended)

Keep Varient + Add MailWizz:

1. **Transactional Emails**: Varient (activation, password reset)
2. **Marketing Newsletters**: MailWizz (campaigns, analytics)
3. **Contact Forms**: Varient (simple contact email)

**Benefits:**
- Use MailWizz's expertise for newsletters
- Keep costs low for transactional
- Redundancy if one fails

---

## 8. API Endpoint Reference

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/lists` | GET/POST | Manage mailing lists |
| `/lists/{id}/subscribers` | GET/POST/PUT/DELETE | Manage subscribers |
| `/campaigns` | GET/POST/PUT | Manage campaigns |
| `/campaigns/{id}/send` | POST | Send campaign |
| `/campaigns/{id}/stats` | GET | Get campaign statistics |
| `/templates` | GET/POST | Manage email templates |
| `/bounce-handlers` | GET/POST | Configure bounce handling |
| `/webhooks` | GET/POST | Manage webhook receivers |
| `/transactional-emails` | POST | Send transactional emails |

---

## 9. Recommended Next Steps

1. ✅ **Evaluate**: Request MailWizz trial account
2. ✅ **Test**: Send test campaign to 100 subscribers
3. ✅ **Measure**: Compare open rates with Varient
4. ✅ **Decide**: Cost vs benefit analysis
5. ✅ **Implement**: If ROI positive, migrate gradually

---

## References

- **MailWizz Docs**: https://api-docs.mailwizz.com/
- **MailWizz GitHub**: https://github.com/ems-api/
- **PHP SDK**: https://github.com/ems-api/mailwizz-php-sdk
- **REST API Guide**: https://api-docs.mailwizz.com/

---

**Created**: 2026-09-23  
**For**: NKHOJ Newsletter System Enhancement  
**Reference**: MailWizz API v1.0 & Varient v2.4.3
