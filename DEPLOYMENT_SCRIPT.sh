#!/bin/bash

# Comment & Notification System - Deployment Script
# Run this script to fully deploy and initialize the comment and notification system

set -e

echo "=========================================="
echo "Comment & Notification System Deployment"
echo "=========================================="
echo ""

# Step 1: Run Database Migrations
echo "Step 1: Running database migrations..."
echo "Command: php artisan migrate"
php artisan migrate

if [ $? -eq 0 ]; then
    echo "✓ Migrations completed successfully"
else
    echo "✗ Migration failed. Please check the error above."
    exit 1
fi

echo ""

# Step 2: Initialize notification subscriptions
echo "Step 2: Initializing notification subscriptions for existing users..."
echo "Command: php artisan notifications:subscribe-users --force"
php artisan notifications:subscribe-users --force

if [ $? -eq 0 ]; then
    echo "✓ User subscriptions initialized"
else
    echo "✗ Subscription initialization failed. Please check the error above."
    exit 1
fi

echo ""

# Step 3: Verify the installation
echo "Step 3: Verifying installation..."
echo ""

# Check if migrations table has the new migrations
echo "Checking database tables..."
php artisan tinker <<EOF
    echo "Tables created:" . "\n";
    echo "- comments: " . (Schema::hasTable('comments') ? "✓" : "✗") . "\n";
    echo "- notification_subscriptions: " . (Schema::hasTable('notification_subscriptions') ? "✓" : "✗") . "\n";
    echo "- notification_activity_logs: " . (Schema::hasTable('notification_activity_logs') ? "✓" : "✗") . "\n";
    echo "\n";

    $users = App\Models\User::count();
    echo "Users: $users\n";

    if ($users > 0) {
        $subs = App\Core\Models\NotificationSubscription::count();
        echo "Notification subscriptions created: $subs\n";
    }

    exit();
EOF

echo ""
echo "=========================================="
echo "✓ Deployment Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test comment creation via API:"
echo "   POST /api/v1/comments"
echo ""
echo "2. Test notification preferences:"
echo "   GET /api/v1/notification-subscriptions/{userId}"
echo ""
echo "3. Check activity logs:"
echo "   GET /api/v1/notifications/activity-logs"
echo ""
echo "Documentation:"
echo "- Deployment Guide: COMMENT_NOTIFICATION_DEPLOYMENT.md"
echo "- Quick Reference: COMMENT_NOTIFICATION_QUICK_REF.md"
echo "- Architecture: COMMENT_NOTIFICATION_ARCHITECTURE.md"
echo ""
