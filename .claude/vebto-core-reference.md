# Vebto Common Core Reference Guide for nkhoj Auth & Notifications

## Overview

The `src/` directory contains Vebto's common core modules - a battle-tested architecture for Auth, Notifications, Email/SMTP, and Events. This guide shows how to adapt these patterns for nkhoj's clean auth implementation.

---

## 1. Auth Module Structure (`src/Auth/`)

### Directory Organization
```
src/Auth/
├── Actions/          # Reusable business logic (CreateUser, DeleteUsers, UpdateUser)
├── Commands/         # Artisan commands
├── Controllers/      # HTTP endpoints
├── Events/          # Events fired (UserCreated, SocialLogin, SocialConnected)
├── Factories/       # Data factories for testing
├── Fortify/         # Laravel Fortify customizations
├── Jobs/            # Queued jobs
├── Middleware/      # Auth middleware
├── Models/          # Auth-related models
├── Notifications/   # Email notifications (VerifyEmailWithOtp.php)
├── Resources/       # API resources
├── Traits/          # Reusable traits
└── Validators/      # Validation rules
```

### Key Pattern: Actions
**Location:** `src/Auth/Actions/`

Actions encapsulate business logic separate from Controllers:
- `CreateUser.php` - User creation logic
- `DeleteUsers.php` - User deletion with cascade
- `UpdateUser.php` - User updates
- `PaginateUsers.php` - Query building for pagination

**Benefits:**
- Reusable across Controllers, Jobs, Commands
- Testable in isolation
- Clean separation of concerns

---

## 2. Events System (`src/Auth/Events/`)

### Key Events
```php
// src/Auth/Events/UserCreated.php
class UserCreated {
    public function __construct(public User $user, public array $data = []) {}
}

// src/Auth/Events/UsersDeleted.php
class UsersDeleted {
    public function __construct(public Collection $users) {}
}

// src/Auth/Events/SocialLogin.php
// src/Auth/Events/SocialConnected.php
```

**What nkhoj already has:**
✓ UserCreated, UsersDeleted, UserBanned events in `app/Events/`

**What to add:**
- Social auth events (if supporting OAuth)
- Password change event
- Email verification completed event
- Session-related events

---

## 3. Notifications System

### Channel Preference Pattern

**File:** `src/Notifications/GetsUserPreferredChannels.php`

This trait reads user notification preferences and routes dynamically:

```php
trait GetsUserPreferredChannels {
    public function via($notifiable): array {
        if (!config('app.notif_subs_integrated')) {
            return ['database', 'mail'];
        }
        
        // Read user's subscription preferences
        $sub = $notifiable->notificationSubscriptions
            ->where('notif_id', static::NOTIF_ID)
            ->first();
            
        foreach (array_filter($sub->channels) as $channel => $isSelected) {
            if ($channel === 'browser') {
                $channels = array_merge($channels, ['database', 'broadcast']);
            } elseif ($channel === 'email') {
                $channels[] = 'mail';
            } elseif ($channel === 'mobile') {
                $channels[] = FcmChannel::class;  // Firebase Cloud Messaging
            }
        }
        
        return $channels;
    }
}
```

**nkhoj Implementation Roadmap:**
1. Create `NotificationSubscription` model to store user channel preferences
2. Create traits for each notification type
3. Support channels: email (SMTP), SMS, FCM (mobile push), database
4. Gate with `config('app.notif_subs_integrated')`

---

## 4. Email/SMTP & Mail Configuration

### Mail Credentials Validator

**File:** `src/Settings/Validators/MailCredentials/OutgoingMailCredentialsValidator.php`

Pattern for validating SMTP credentials by sending test email:

```php
class OutgoingMailCredentialsValidator implements SettingsValidator {
    const KEYS = [
        'mail_mailer',      // smtp, mailgun, ses, etc.
        'mail_host',        // smtp.gmail.com
        'mail_username',    // user@gmail.com
        'mail_password',    // app password
        'mail_port',        // 465, 587
        'mail_encryption',  // tls, ssl
        'mailgun_domain',   // for Mailgun
        'ses_key',         // for AWS SES
    ];
    
    public function fails($values) {
        // Set config dynamically
        $this->setConfigDynamically($values);
        
        try {
            // Send test email to admin
            Mail::to(Auth::user()->email)
                ->send(new MailCredentialsMailable());
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        }
        
        return null; // Success
    }
}
```

**nkhoj Implementation:**
1. Create `app/Validators/MailCredentialsValidator.php`
2. Use in admin settings to test SMTP before saving
3. Support: SMTP, Mailgun, SendGrid, AWS SES

---

## 5. OTP Email Notification

### Vebto's Pattern

**File:** `src/Auth/Notifications/VerifyEmailWithOtp.php`

```php
class VerifyEmailWithOtp extends Notification {
    public function __construct(public string $otp) {}
    
    public function via($notifiable) {
        return ['mail'];
    }
    
    public function toMail($notifiable): MailMessage {
        return (new MailMessage())
            ->subject(__('Your :site security code is :code', 
                ['site' => config('app.name'), 'code' => $this->otp]))
            ->greeting("Your OTP: {$this->otp}")
            ->line('Valid for 30 minutes')
            ->line('If you did not request this, ignore this email');
    }
}
```

**nkhoj Status:**
✓ Already implemented as `VerifyEmailWithOtpNotification.php`
- Can enhance with HTML formatting like Vebto's version

---

## 6. Mail Transport Customization

### Gmail API Transport

**File:** `src/Settings/Mail/GmailApiMailTransport.php`

Custom Symfony transport for Gmail API (without SMTP):

```php
class GmailApiMailTransport extends AbstractTransport {
    public function doSend(SentMessage $message): void {
        (new GmailClient())->sendEmail($message->toString());
    }
    
    public function __toString(): string {
        return 'gmailApi';
    }
}
```

**nkhoj Option:**
- If hosting needs Gmail, use this instead of SMTP
- Or stick with standard SMTP for simplicity on cPanel

---

## 7. Clean Code Patterns to Adopt

### Pattern 1: Action Classes
```php
namespace App\Actions\Auth;

class CreateUser {
    public function execute(array $data): User {
        // Business logic here
        return User::create([...]);
    }
}
```

**Usage in Controller:**
```php
public function store(Request $request) {
    $user = app(CreateUser::class)->execute($request->validated());
    return redirect()->route('admin.users.show', $user);
}
```

### Pattern 2: Event Listeners
```php
// In AppServiceProvider or EventServiceProvider
Event::listen(UserCreated::class, function (UserCreated $event) {
    SendWelcomeEmail::dispatch($event->user);
    LogUserAction::dispatch($event->user, 'account_created');
});
```

### Pattern 3: Jobs for Async Tasks
```php
class SendWelcomeEmail implements ShouldQueue {
    public function __construct(public User $user) {}
    
    public function handle() {
        Mail::to($this->user->email)->send(new WelcomeMailable());
    }
}
```

### Pattern 4: Validation Traits
```php
trait VerifiesEmail {
    public function emailVerificationOtpIsValid(string $code): bool {
        $otp = $this->otpCodes()
            ->where('type', 'email_verification')
            ->where('expires_at', '>', now())
            ->first();
        return $otp && $otp->code === $code;
    }
}
```

---

## 8. Implementation Roadmap for nkhoj

### Phase 1: Email/SMTP (Immediate)
- [ ] Create `MailCredentialsValidator` in `app/Validators/`
- [ ] Add SMTP settings to admin panel
- [ ] Test email functionality with admin form
- [ ] Enhance `VerifyEmailWithOtpNotification` HTML formatting

### Phase 2: Notification Channels (Next)
- [ ] Create `NotificationSubscription` model
- [ ] Create `NotificationPreference` migration
- [ ] Build UI for users to select notification channels (email, SMS, push)
- [ ] Implement `GetsUserPreferredChannels` trait pattern
- [ ] Add database notifications table if not present

### Phase 3: Event-Driven Notifications (Advanced)
- [ ] Expand events (password change, session activity, etc.)
- [ ] Create event listeners for each notification type
- [ ] Use Jobs for async email sending
- [ ] Add retry logic and failure handling

### Phase 4: Multiple Providers (Future)
- [ ] Support Mailgun as alternative to SMTP
- [ ] Support SMS via Twilio
- [ ] Support FCM for mobile push notifications
- [ ] Create provider abstraction layer

---

## 9. Key Files to Reference

### Architecture
- `src/Auth/` - Full auth module organization
- `src/Notifications/GetsUserPreferredChannels.php` - Channel routing pattern
- `src/Auth/Actions/` - Reusable business logic

### Email/SMTP
- `src/Settings/Mail/GmailApiMailTransport.php` - Custom transport
- `src/Settings/Validators/MailCredentials/` - Credential validation

### Notifications
- `src/Auth/Notifications/VerifyEmailWithOtp.php` - OTP notification template
- `src/Notifications/` - Notification controllers and resources

### Events
- `src/Auth/Events/` - Example event classes

---

## 10. Quick Copy-Paste Templates

### Create Action Class
```bash
# Path: app/Actions/Auth/YourAction.php
namespace App\Actions\Auth;

class YourAction {
    public function execute(array $data): Model {
        // Implement your logic
    }
}
```

### Create Notification Trait
```bash
# Path: app/Traits/GetsUserNotificationChannels.php
namespace App\Traits;

trait GetsUserNotificationChannels {
    public function via($notifiable): array {
        // Copy from Vebto and customize
    }
}
```

### Test Validator
```bash
# In console:
php artisan tinker
>>> app(\App\Validators\MailCredentialsValidator::class)
    ->fails(['mail_host' => 'smtp.gmail.com', ...])
```

---

## Summary

**Adopt from Vebto:**
1. ✅ Actions pattern (business logic extraction)
2. ✅ Events for user lifecycle (create, delete, ban)
3. ✅ Channel preference system (email, SMS, push)
4. ✅ Credential validation by sending test email
5. ✅ Notification trait pattern for routing

**Already in nkhoj:**
- ✅ Events (UserCreated, UsersDeleted, UserBanned)
- ✅ OTP notifications
- ✅ Ban system with comments

**Next steps:** Mail credentials validator → Notification channel preferences → Event-driven workflows
