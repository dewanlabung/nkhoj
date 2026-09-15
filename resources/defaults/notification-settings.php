<?php

return [
    /**
     * Available notification channels for users to enable/disable
     */
    'available_channels' => [
        'browser',  // Database + Broadcasting
        'email',    // Email notifications
        'mobile',   // FCM push notifications
    ],

    /**
     * Notification categories and types
     * Each user can subscribe with granular control per channel
     */
    'subscriptions' => [
        'activity' => [
            'name' => 'Activity',
            'description' => 'Comments, mentions, and interactions',
            'subscriptions' => [
                [
                    'notif_id' => 'comment_replied',
                    'name' => 'Comment Reply',
                    'description' => 'Someone replies to your comment',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'post_commented',
                    'name' => 'Post Comment',
                    'description' => 'Someone comments on your post',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'user_mentioned',
                    'name' => 'User Mention',
                    'description' => 'You are mentioned in a comment or post',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'content_liked',
                    'name' => 'Content Liked',
                    'description' => 'Someone likes your post or comment',
                    'permissions' => [],
                ],
            ],
        ],

        'social' => [
            'name' => 'Social',
            'description' => 'Follows, followers, and connections',
            'subscriptions' => [
                [
                    'notif_id' => 'user_followed',
                    'name' => 'New Follower',
                    'description' => 'Someone follows you',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'user_unfollowed',
                    'name' => 'Unfollowed',
                    'description' => 'Someone unfollows you',
                    'permissions' => [],
                ],
            ],
        ],

        'content' => [
            'name' => 'Content',
            'description' => 'Posts, articles, and content updates',
            'subscriptions' => [
                [
                    'notif_id' => 'post_published',
                    'name' => 'Post Published',
                    'description' => 'A user you follow publishes a new post',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'article_published',
                    'name' => 'Article Published',
                    'description' => 'A new article is published',
                    'permissions' => [],
                ],
            ],
        ],

        'system' => [
            'name' => 'System',
            'description' => 'System alerts and important notifications',
            'subscriptions' => [
                [
                    'notif_id' => 'system_error',
                    'name' => 'System Errors',
                    'description' => 'System errors and critical alerts',
                    'permissions' => ['admin.view_system_errors'],
                ],
                [
                    'notif_id' => 'account_security',
                    'name' => 'Account Security',
                    'description' => 'Login attempts, password changes, and security alerts',
                    'permissions' => [],
                ],
                [
                    'notif_id' => 'account_updates',
                    'name' => 'Account Updates',
                    'description' => 'Email verification, profile updates, and account changes',
                    'permissions' => [],
                ],
            ],
        ],
    ],
];
