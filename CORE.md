# Core Foundation Architecture

This document describes the Nkhoj **Core Foundation** - a reusable, testable, and maintainable base layer for authentication, authorization, and email services.

## Overview

Following Vebto's architectural patterns, Nkhoj separates concerns into clear layers:

- **Core/** - Reusable business logic (services, contracts, traits, models)
- **Http/** - HTTP request handling (controllers, middleware)
- **Domains/** - Feature-specific code (domain logic, policies)

The Core layer is **framework-agnostic**, **fully tested**, and **independent** from HTTP concerns.

## Directory Structure

```
app/Core/
├── Models/              # Base model classes
│   └── BaseModel.php    # Abstract base for all models
├── Services/            # Business logic implementations
│   ├── Auth/
│   │   ├── OtpAuthService.php      # OTP generation, verification, sending
│   │   └── BanService.php          # User banning logic
│   └── Mail/
│       └── MailService.php         # Email sending wrapper
├── Contracts/           # Service interfaces
│   ├── Bannable.php     # Interface for bannable models
│   ├── OtpService.php   # OTP service contract
│   └── BanService.php   # Ban service contract
├── Traits/              # Reusable model behaviors
│   ├── HasBans.php      # Ban functionality for models
│   └── HasRoles.php     # Role and permission helpers
├── Events/              # Domain events
│   └── UserBanned.php   # Event fired when user is banned
├── Exceptions/          # Custom exceptions
│   ├── OtpException.php
│   └── BanException.php
└── Helpers/             # Utility functions
    └── OtpGenerator.php # OTP code generation
```

## Services

### OtpAuthService

Handles one-time password generation, sending, and verification.

```php
use App\Core\Contracts\OtpService;

class SomeController
{
    public function __construct(private OtpService $otp) {}

    public function verify(Request $request)
    {
        $otp = $this->otp->generate($request->email, 'email_verification');
        $this->otp->send($request->email, $otp);
        
        $verified = $this->otp->verify($request->email, $request->code, 'email_verification');
    }
}
```

**Methods:**
- `generate(email, purpose)` - Create OTP code
- `send(email, otp)` - Mail the OTP
- `verify(email, code, purpose)` - Validate and mark verified
- `resend(email, purpose)` - Generate and send new OTP

### BanService

Manages temporary and permanent user bans.

```php
use App\Core\Contracts\BanService;

class AdminController
{
    public function __construct(private BanService $bans) {}

    public function banUser($userId, $days = null)
    {
        $user = User::find($userId);
        $expiresAt = $days ? now()->addDays($days) : null;
        
        $ban = $this->bans->ban($user, 'Spam violation', $expiresAt, auth()->id());
    }
}
```

**Methods:**
- `ban(model, comment, expiresAt, createdById)` - Create a ban
- `unban(model)` - Expire all active bans
- `isActive(model)` - Check if model is banned
- `getActiveBan(model)` - Get current ban record

### MailService

Wrapper around Laravel's Mail facade for consistent sending.

```php
use App\Core\Services\Mail\MailService;

class NotificationService
{
    public function __construct(private MailService $mail) {}

    public function notify($email, $subject, $body)
    {
        $this->mail->send($email, $subject, $body);
    }
}
```

**Methods:**
- `send(email, subject, body)` - Send raw email
- `sendToMultiple(emails, subject, body)` - Batch emails
- `sendMailable(email, mailable)` - Send Mailable instance

## Traits

Traits provide model-level functionality that can be reused across multiple models.

### HasBans

Add ban functionality to any model:

```php
class User extends Authenticatable
{
    use HasBans;
}

// Usage:
$user->ban('Spam content');           // Ban permanently
$user->ban('Temp ban', now()->addDays(7));  // Ban for 7 days
$user->isBanned();                    // Check if banned
$user->unban();                       // Lift ban
$user->activeBan();                   // Get current ban record
```

### HasRoles

Role and permission checking:

```php
class User extends Authenticatable
{
    use HasRoles;
}

// Usage:
$user->isAdmin();                     // Check role
$user->isMod();                       // Is mod or admin
$user->roleLabel();                   // Get display label
$user->roleBadgeClass();              // Get Tailwind classes
$user->hasPermission('publish_posts'); // Custom permission
```

## Contracts (Interfaces)

Define service contracts to enable dependency injection and testing.

### OtpService Contract

```php
namespace App\Core\Contracts;

interface OtpService
{
    public function generate(string $email, string $purpose = 'email_verification');
    public function verify(string $email, string $code, string $purpose);
    public function resend(string $email, string $purpose = 'email_verification');
    public function send(string $email, $otp): void;
}
```

Type-hint the contract in controllers, not the implementation:

```php
public function __construct(OtpService $otp)  // ✓ Correct
public function __construct(OtpAuthService $otp)  // ✗ Avoid this
```

## Events

Domain events capture significant business occurrences.

### UserBanned

Dispatched when a user is banned:

```php
use App\Core\Events\UserBanned;

// Listeners can react:
class SendBanNotification implements ShouldQueue
{
    public function handle(UserBanned $event)
    {
        $event->user;       // The banned user
        $event->ban;        // The Ban model
        $event->isPermanent;  // Was it permanent?
    }
}
```

## Exceptions

Custom exceptions for clearer error handling.

```php
use App\Core\Exceptions\OtpException;
use App\Core\Exceptions\BanException;

try {
    $otp = $this->otp->verify($email, $code, $purpose);
    if (!$otp) {
        throw OtpException::invalidCode();
    }
} catch (OtpException $e) {
    return response()->json(['error' => $e->getMessage()], $e->getCode());
}
```

## Helpers

Utility functions for common tasks.

### OtpGenerator

Generate and validate OTP codes:

```php
use App\Core\Helpers\OtpGenerator;

$code = OtpGenerator::generate(6);              // Generate 6-digit code
$code = OtpGenerator::generate(8);              // Or 8-digit
$valid = OtpGenerator::isValid($code, 6);      // Validate format
```

## Service Registration

Services are registered in `CoreServiceProvider`:

```php
namespace App\Providers;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OtpServiceContract::class, OtpAuthService::class);
        $this->app->singleton(BanServiceContract::class, BanService::class);
        $this->app->singleton(MailService::class);
    }
}
```

## Testing

Core layer is **fully tested**. Run tests:

```bash
php artisan test tests/Unit/Core
```

### Test Files

- `tests/Unit/Core/Services/OtpAuthServiceTest.php` - OTP generation/verification
- `tests/Unit/Core/Services/BanServiceTest.php` - Ban operations
- `tests/Unit/Core/Traits/HasBansTraitTest.php` - Ban trait functionality
- `tests/Unit/Core/Traits/HasRolesTraitTest.php` - Role trait functionality
- `tests/Unit/Core/Helpers/OtpGeneratorTest.php` - OTP code generation

### Testing with Contracts

Test with mock implementations:

```php
class MyServiceTest extends TestCase
{
    public function test_uses_otp_service()
    {
        $mockOtpService = Mockery::mock(OtpService::class);
        $this->app->bind(OtpService::class, $mockOtpService);

        $myService = new MyService($mockOtpService);
        // Test...
    }
}
```

## Extending Core

### Adding a New Service

1. Create the contract in `app/Core/Contracts/`
2. Implement in `app/Core/Services/`
3. Register in `CoreServiceProvider`
4. Add tests in `tests/Unit/Core/Services/`

Example - Adding SMS Service:

```php
// app/Core/Contracts/SmsService.php
interface SmsService
{
    public function send(string $phone, string $message): bool;
}

// app/Core/Services/SmsNotificationService.php
class SmsNotificationService implements SmsService
{
    public function send(string $phone, string $message): bool
    {
        // Twilio integration
    }
}

// Register in CoreServiceProvider
$this->app->singleton(SmsService::class, SmsNotificationService::class);
```

### Adding a New Trait

1. Create in `app/Core/Traits/`
2. Use in models
3. Add tests in `tests/Unit/Core/Traits/`

Example - Adding CanPublish trait:

```php
// app/Core/Traits/CanPublish.php
trait CanPublish
{
    public function publish(): void
    {
        $this->published_at = now();
        $this->save();
    }
}

// In model
class Post extends Model
{
    use CanPublish;
}
```

## Best Practices

✅ **DO:**
- Depend on contracts, not implementations
- Test core services in isolation
- Keep core independent from HTTP concerns
- Use services for business logic
- Use traits for shared model behavior
- Document your services and contracts

❌ **DON'T:**
- Put HTTP logic in core services
- Import controllers/routes into core
- Mix concerns (email + banning in one service)
- Create tightly coupled dependencies
- Skip tests for new core features

## Related Files

- `app/Providers/CoreServiceProvider.php` - Service registration
- `app/Models/User.php` - Uses HasBans, HasRoles traits
- `app/Models/Ban.php` - Bannable model
- `app/Models/Otp.php` - OTP model
- `app/Http/Controllers/Auth/OtpController.php` - HTTP endpoint example

## Migration Checklist

When onboarding new features to the core:

- [ ] Create contract/interface if needed
- [ ] Implement service/trait
- [ ] Register in CoreServiceProvider
- [ ] Add comprehensive tests
- [ ] Update this documentation
- [ ] Create migration files if needed
- [ ] Update app models to use new traits
- [ ] Test in controller/HTTP layer

---

**This architecture ensures Nkhoj's foundation is robust, testable, reusable, and maintainable for years to come.**
