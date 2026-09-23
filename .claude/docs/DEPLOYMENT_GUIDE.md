# NKHOJ Deployment Guide for dewanlabung.com.np

## 🚀 Deployment Overview

This guide covers deploying the latest NKHOJ build to the production server at **dewanlabung.com.np**.

**Current Status**: ✅ All code synced and committed to master branch
- ✅ Varient newsletter system extracted
- ✅ App reference architecture (.claude/app/) included
- ✅ Pre-built assets (public/build/) synchronized
- ✅ Skills documentation added (.claude/docs/)
- ✅ Master branch is production-ready

---

## 1. Pre-Deployment Checklist

### Server Access
- [ ] SSH access to dewanlabung.com.np
- [ ] Git credentials configured
- [ ] Web root directory identified (/home/username/public_html or similar)
- [ ] PHP version 8.0+ confirmed
- [ ] Composer installed on server

### Code Status
- [ ] Latest master branch pulled
- [ ] composer.json dependencies installed
- [ ] .env file configured for production
- [ ] Database migrations up-to-date

### Assets
- [ ] public/build/ directory exists
- [ ] Node modules not needed (pre-built assets)
- [ ] TinyMCE assets copied (public/tinymce/)

---

## 2. Deployment Steps

### 2.1 Connect to Server (SSH)

```bash
# From your local machine
ssh user@dewanlabung.com.np
cd /home/user/public_html  # or your web root
```

### 2.2 Pull Latest Code

```bash
# Get latest from master
git fetch origin master
git checkout master
git pull origin master

# Verify you have the latest commit
git log --oneline -1
# Should show: 00b155e Add MailWizz API integration guide...
```

### 2.3 Install/Update Dependencies

```bash
# Update PHP dependencies
composer install --no-dev --optimize-autoloader

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2.4 Database Migrations

```bash
# Check pending migrations
php artisan migrate:status

# Run migrations (if any new ones)
php artisan migrate

# Seed if needed
php artisan db:seed
```

### 2.5 Verify Assets

```bash
# Check pre-built assets exist
ls -la public/build/assets/
# Should show: app-*.css, app-*.js, manifest.json

# Check TinyMCE
ls -la public/tinymce/
# Should show: themes/, plugins/, skins/, tinymce.min.js

# No npm build needed on server (assets pre-built locally)
```

### 2.6 Set Permissions

```bash
# Make Laravel/storage writable
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Set web server ownership
chown -R www-data:www-data storage bootstrap/cache public
```

### 2.7 Verify Installation

```bash
# Check Laravel status
php artisan tinker
# Type: exit()

# Check website
curl https://dewanlabung.com.np
# Should return 200 OK

# Check admin panel
curl https://dewanlabung.com.np/admin
# Should redirect to login
```

---

## 3. Caching & Performance

### 3.1 Enable Caching

```bash
# Set cache driver (use redis if available)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check cache status
php artisan cache:forget --all
```

### 3.2 Optimize Autoloader

```bash
# Already done via composer install --optimize-autoloader
# Verify it worked:
ls -la vendor/composer/
# Should show autoload_classmap.php (large file)
```

### 3.3 Enable OPcache (PHP Config)

Add to php.ini or .htaccess:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

---

## 4. Important Files & Directories

### Critical Files to Verify Post-Deployment

```
dewanlabung.com.np/
├── app/
│   ├── Domains/Blog/Http/Controllers/StoryController.php  ← Media deletion fix
│   ├── Domains/Admin/Http/Controllers/ContentController.php ← Media path fix
│   └── Providers/AppServiceProvider.php  ← Story observer registration
├── public/
│   ├── build/  ← Pre-built Vite assets (no npm build needed)
│   ├── tinymce/  ← TinyMCE assets (copy-tinymce script)
│   └── uploads/  ← User uploaded files
├── resources/
│   ├── views/admin/media.blade.php  ← Path fix for delete
│   └── views/stories/  ← Story templates
├── scripts/
│   └── copy-tinymce.js  ← Improved TinyMCE copy script
├── .claude/
│   ├── app/  ← Reference architecture (186 files)
│   ├── varient/  ← Varient newsletter system
│   └── docs/  ← Skills documentation
└── .env  ← Production configuration
```

---

## 5. Post-Deployment Verification

### 5.1 Test Media Management

```bash
# Test 1: Upload a story media file
curl -X POST https://dewanlabung.com.np/api/stories \
  -F "media=@test.jpg" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Test 2: Delete media file via admin
# - Go to /admin/media
# - Delete a file in a subfolder like stories/
# - Verify file is removed from disk

# Test 3: Verify file paths
ls -la storage/app/public/stories/
ls -la public/uploads/
```

### 5.2 Test Newsletter System

```bash
# Test 1: Check subscriber token generation
php artisan tinker
# Type: DB::table('subscribers')->first();
# Should have 'token' field with random value

# Test 2: Send test email
# - Go to /admin/newsletter
# - Send to test subscriber
# - Verify email received

# Test 3: Check email logs
php artisan tinker
# Type: DB::table('newsletter_logs')->latest()->first();
```

### 5.3 Test Story Expiration (24-hour cleanup)

```bash
# Verify PurgeExpiredStories command
php artisan schedule:work  # In another terminal

# Or manually trigger
php artisan purge:expired-stories

# Check deleted stories
php artisan tinker
# Type: DB::table('stories')->where('expires_at', '<', now())->count();
# Should be 0 (all expired deleted)
```

### 5.4 Performance Check

```bash
# Check page load time
time curl -o /dev/null -s https://dewanlabung.com.np

# Check memory usage
php -r "echo round(memory_get_peak_usage(true) / 1048576, 2) . ' MB';"

# Check slow queries (if logging enabled)
tail -100 storage/logs/laravel.log | grep "SLOW QUERY"
```

---

## 6. Troubleshooting

### Issue: Pre-built assets not loading

```bash
# Check if assets exist
ls -la public/build/

# If missing, copy from local machine
# On local machine:
npm run build
git add public/build/
git commit -m "Rebuild assets"
git push origin master

# On server:
git pull origin master
```

### Issue: TinyMCE not working

```bash
# Run copy script
php scripts/copy-tinymce.js

# Or manually copy
cp -r node_modules/tinymce public/tinymce

# Verify
curl https://dewanlabung.com.np/tinymce/tinymce.min.js | head -c 100
```

### Issue: Media files not deleting

```bash
# Check file permissions
ls -la storage/app/public/stories/
chmod -R 775 storage/app/public/

# Check StoryObserver is registered
php artisan tinker
# Type: App\Domains\Blog\Models\Story::observe(App\Domains\Blog\Observers\StoryObserver::class);

# Test deletion
php artisan tinker
# Type: App\Models\MediaContent\Story::find(1)->delete();
```

### Issue: CORS errors on API

```bash
# Check CORS middleware in app/Http/Middleware/CORS.php
# Verify allowed origins include dewanlabung.com.np

# Clear config cache
php artisan config:clear
php artisan config:cache
```

---

## 7. Rollback Plan

If deployment fails:

```bash
# Revert to previous commit
git log --oneline -5
git revert HEAD --no-edit
git push origin master

# OR reset to specific commit
git reset --hard <commit-hash>
git push --force-with-lease origin master

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
```

**Safe rollback** (keeps current data):
```bash
# Just revert code, keep database
git checkout <previous-commit> -- app/
git checkout <previous-commit> -- resources/
git commit -m "Revert to stable version"
git push origin master
```

---

## 8. Monitoring & Logs

### Real-time Logs

```bash
# Watch application logs
tail -f storage/logs/laravel.log

# Watch web server logs (Nginx)
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Watch web server logs (Apache)
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

### Disk Usage

```bash
# Check disk space
df -h /

# Check storage directory size
du -sh storage/
du -sh public/uploads/
du -sh public/build/

# Clean old logs
php artisan log:clear
php artisan cache:forget --all
```

### Database Backups

```bash
# Automatic daily backup (add to crontab)
0 2 * * * php /home/user/public_html/artisan backup:run

# Manual backup
php artisan backup:run

# List backups
php artisan backup:list
```

---

## 9. SSL/HTTPS Verification

```bash
# Check SSL certificate
openssl s_client -connect dewanlabung.com.np:443 -servername dewanlabung.com.np

# Test SSL strength
curl -I https://dewanlabung.com.np

# Should show:
# HTTP/2 200
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
```

---

## 10. Post-Deployment Checklist

- [ ] Git pull master successful
- [ ] Composer install completed
- [ ] Database migrations run
- [ ] Cache cleared and rebuilt
- [ ] Assets (public/build/) verified
- [ ] TinyMCE assets present
- [ ] File permissions set (775 for storage)
- [ ] Website loads without errors
- [ ] Admin panel accessible
- [ ] API endpoints responding
- [ ] Story media upload working
- [ ] Newsletter sending working
- [ ] SSL certificate valid
- [ ] Logs monitoring active
- [ ] Backups configured

---

## 11. Maintenance Schedule

### Daily
- [ ] Monitor error logs
- [ ] Check disk space usage
- [ ] Verify cron jobs running

### Weekly
- [ ] Check backup status
- [ ] Monitor slow queries
- [ ] Review email delivery stats

### Monthly
- [ ] Update dependencies: `composer update`
- [ ] Review newsletter analytics
- [ ] Check SSL certificate expiry

### Quarterly
- [ ] Security audit
- [ ] Performance optimization
- [ ] Database optimization: `php artisan optimize:tables`

---

## 12. Contact & Support

**Server Details:**
- Host: dewanlabung.com.np
- User: [Your SSH username]
- Web root: [Your document root]

**Emergency Contacts:**
- Server admin: [Contact info]
- Email support: support@dewanlabung.com.np

---

## Summary

✅ **Your deployment is ready!**

**What changed:**
1. Varient newsletter system architecture documented
2. App reference architecture (186 files) included
3. Pre-built assets synced (no server build needed)
4. Media deletion bugs fixed (images & videos)
5. Cross-platform npm build support added
6. MailWizz API integration guide provided
7. Skills documentation created

**Next 24 hours:**
- Deploy to server
- Run migrations
- Verify media management
- Test newsletter sending
- Monitor error logs

**Ready to deploy? Follow steps 2-7 above!**

---

**Created**: 2026-09-23  
**Status**: 🚀 Production Ready  
**Last Updated**: Master branch commit 00b155e
