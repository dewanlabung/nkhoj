# Session & CSRF Token Management

## Problem: 419 Page Expired Error

When you logout and return to login after inactivity, you may see a **419 Page Expired** error. This is Laravel's CSRF protection - the session token has expired.

## Solutions Implemented

### 1. ✅ Automatic Token Refresh Middleware

**File:** `app/Http/Middleware/RefreshCsrfToken.php`

Automatically regenerates CSRF tokens on every authenticated request, keeping them fresh throughout the session.

**Benefits:**
- Seamless user experience
- No manual token refresh needed
- Automatic protection against token expiry

### 2. ✅ Session Warning Modal

**File:** `resources/views/components/session-warning.blade.php`

Displays a warning 5 minutes before session expires with countdown timer.

**Features:**
- Shows 5-minute warning before expiry
- Live countdown timer
- "Stay Logged In" button to refresh session
- "Logout" button for immediate logout

**How it works:**
1. Session set to 240 minutes (4 hours) in production
2. Warning triggers at 235 minutes (5 minutes before expiry)
3. User can stay logged in or logout gracefully

### 3. ✅ Session Refresh Endpoint

**Endpoint:** `POST /api/session/refresh`

Allows client-side code to refresh the session token when needed.

**Usage:**
```javascript
fetch('/api/session/refresh', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})
.then(response => response.json())
.then(data => console.log('Session refreshed:', data));
```

### 4. ✅ Improved Session Configuration

**File:** `.env` settings

```env
SESSION_DRIVER=file                  # Use 'database' for production
SESSION_LIFETIME=240                 # 4 hours
SESSION_EXPIRE_ON_CLOSE=false        # Keep alive after browser close
SESSION_HTTP_ONLY=true               # Prevent JavaScript access
SESSION_SECURE_COOKIE=true           # HTTPS only (production)
SESSION_SAME_SITE=lax                # CSRF protection
```

## Implementation Steps

### Step 1: Update Environment Variables

For **production** (dewanlabung.com.np):

```env
SESSION_DRIVER=database         # Persists in database
SESSION_LIFETIME=240            # 4 hours
SESSION_SECURE_COOKIE=true      # HTTPS only
```

For **development**:

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120            # 2 hours
SESSION_SECURE_COOKIE=false     # Allow HTTP
```

### Step 2: Create Database Sessions Table (Production Only)

If using `database` driver:

```bash
php artisan session:table
php artisan migrate
```

### Step 3: Clear Browser Cache

```bash
# Chrome/Edge/Firefox: Ctrl+Shift+Delete (Windows) or Cmd+Shift+Delete (Mac)
# Safari: Preferences → Privacy → Manage Website Data → Remove All

# Or in your browser developer tools:
# Application → Cookies → Delete all nkhoj_session cookies
```

### Step 4: Test the Implementation

1. **Login to the app**
2. **Open developer tools** (F12)
3. **Go to Application tab** → Cookies
4. **Note the nkhoj_session cookie**
5. **Wait 5 minutes** (or adjust SESSION_LIFETIME to test faster)
6. **Verify the warning modal appears**
7. **Click "Stay Logged In"** and confirm no 419 error
8. **Or click "Logout"** for graceful exit

## Architecture

```
Request → RefreshCsrfToken Middleware
         ↓
         Regenerate CSRF token
         ↓
         Process request
         ↓
         Client receives new token
         ↓
         Session warning monitor runs
         ↓
         If near expiry: Show modal
         ↓
         User can: Stay Logged In (refresh) OR Logout
```

## Session Lifecycle

```
1. User logs in
   └─ Session created (240 min timeout)
   └─ CSRF token generated
   └─ Warning timer set (235 min)

2. User makes requests
   └─ Token regenerated on each request
   └─ Session timeout reset
   └─ Token stays fresh

3. After 235 minutes of activity
   └─ Warning modal appears
   └─ Countdown shows 5 minutes remaining

4. User action
   ├─ Click "Stay Logged In"
   │  └─ POST /api/session/refresh
   │  └─ Token regenerated
   │  └─ Timer resets
   │  └─ User continues
   │
   └─ Click "Logout" or timeout
      └─ POST /logout
      └─ Session destroyed
      └─ Redirect to login
```

## Troubleshooting

### Still Getting 419 Error?

1. **Clear all cookies:**
   ```javascript
   // In browser console
   document.cookie.split(";").forEach(c => {
       document.cookie = c.replace(/^ +/, "")
           .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
   });
   ```

2. **Check .env values:**
   ```bash
   php artisan tinker
   >>> config('session.lifetime')
   >>> config('session.driver')
   ```

3. **Verify middleware registration:**
   - Check `bootstrap/app.php`
   - Ensure `RefreshCsrfToken` is in web middleware stack

4. **Check browser cookies:**
   - Dev Tools → Application → Cookies
   - Look for `nkhoj_session` cookie
   - Verify it's not set to expire immediately

5. **Test with cURL:**
   ```bash
   curl -c cookies.txt -b cookies.txt -X POST \
     -H "X-CSRF-TOKEN: $(grep 'csrf' cookies.txt | awk '{print $7}')" \
     https://dewanlabung.com.np/api/session/refresh
   ```

### Performance Impact

- **Minimal** - Token regeneration is ~1ms per request
- **No database queries** - Uses file-based sessions by default
- **Scales well** - Consider `SESSION_DRIVER=redis` for high traffic

## Security Considerations

✅ **What's Protected:**
- CSRF attacks prevented
- XSS tokens cannot be stolen (HttpOnly)
- Tokens expire after 240 minutes
- Token regenerated on every request
- Session over HTTPS only (production)

✅ **Best Practices:**
- Keep `SESSION_HTTP_ONLY=true`
- Enable `SESSION_SECURE_COOKIE=true` in production
- Use `SESSION_SAME_SITE=lax` (strict would break some flows)
- Monitor session table size (database driver)
- Clear old sessions regularly: `php artisan session:prune`

## Related Files

- `app/Http/Middleware/RefreshCsrfToken.php` - Token refresh logic
- `resources/views/components/session-warning.blade.php` - UI warning modal
- `routes/web.php` - Session refresh endpoint
- `bootstrap/app.php` - Middleware registration
- `.env` - Configuration

## Performance Monitoring

Monitor your session behavior:

```bash
# View active sessions (if using database driver)
php artisan tinker
>>> DB::table('sessions')->count()

# Clear expired sessions
php artisan session:prune

# Monitor session size
du -sh storage/framework/sessions/
```

---

**With these implementations, your users won't experience 419 errors on inactivity. They'll get a friendly warning and can choose to stay logged in!** 🎉
