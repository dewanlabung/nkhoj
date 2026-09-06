<?php
/**
 * nkhoj — Web Installer (exec-free, PDO-only)
 */
session_start();
set_time_limit(300);
ignore_user_abort(true);

define('APP_ROOT', realpath(__DIR__ . '/../../'));
define('INSTALLER_VERSION', '1.1.0');

function env_path(): string { return APP_ROOT . '/.env'; }
function step(): int { return (int)($_SESSION['install_step'] ?? 1); }
function set_step(int $s): void { $_SESSION['install_step'] = $s; }
function data(): array { return $_SESSION['install_data'] ?? []; }
function set_data(array $d): void { $_SESSION['install_data'] = $d; }
function merge_data(array $d): void { $_SESSION['install_data'] = array_merge(data(), $d); }

function generate_app_key(): string {
    return 'base64:' . base64_encode(random_bytes(32));
}

function make_pdo(array $d): PDO {
    $dsn = "mysql:host={$d['db_host']};port={$d['db_port']};dbname={$d['db_name']};charset=utf8mb4";
    return new PDO($dsn, $d['db_user'], $d['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);
}

function test_db(array $d): array {
    try {
        $pdo = make_pdo($d);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        return ['ok' => true, 'version' => $ver];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

function write_env(array $d): bool {
    $key = generate_app_key();
    $_SESSION['generated_key'] = $key;
    $content = 'APP_NAME="' . addslashes($d['app_name'] ?? 'nkhoj') . '"' . "\n"
        . 'APP_ENV=production' . "\n"
        . 'APP_KEY=' . $key . "\n"
        . 'APP_DEBUG=false' . "\n"
        . 'APP_TIMEZONE=' . ($d['timezone'] ?? 'Asia/Kathmandu') . "\n"
        . 'APP_URL=' . rtrim($d['app_url'] ?? 'http://localhost', '/') . "\n\n"
        . 'LOG_CHANNEL=stack' . "\n"
        . 'LOG_LEVEL=error' . "\n\n"
        . 'DB_CONNECTION=mysql' . "\n"
        . 'DB_HOST=' . ($d['db_host'] ?? '127.0.0.1') . "\n"
        . 'DB_PORT=' . ($d['db_port'] ?? '3306') . "\n"
        . 'DB_DATABASE=' . ($d['db_name'] ?? '') . "\n"
        . 'DB_USERNAME=' . ($d['db_user'] ?? '') . "\n"
        . 'DB_PASSWORD=' . ($d['db_pass'] ?? '') . "\n\n"
        . 'CACHE_STORE=file' . "\n"
        . 'QUEUE_CONNECTION=sync' . "\n"
        . 'SESSION_DRIVER=file' . "\n"
        . 'SESSION_LIFETIME=120' . "\n\n"
        . 'FILESYSTEM_DISK=local' . "\n\n"
        . 'MAIL_MAILER=smtp' . "\n"
        . 'MAIL_HOST=' . ($d['mail_host'] ?? '127.0.0.1') . "\n"
        . 'MAIL_PORT=' . ($d['mail_port'] ?? '587') . "\n"
        . 'MAIL_USERNAME=' . ($d['mail_user'] ?? '') . "\n"
        . 'MAIL_PASSWORD=' . ($d['mail_pass'] ?? '') . "\n"
        . 'MAIL_ENCRYPTION=' . ($d['mail_enc'] ?? 'tls') . "\n"
        . 'MAIL_FROM_ADDRESS=' . ($d['mail_from'] ?? '') . "\n"
        . 'MAIL_FROM_NAME="${APP_NAME}"' . "\n";
    return file_put_contents(env_path(), $content) !== false;
}

// ── Full schema SQL (exec-free, pure PDO) ─────────────────────────────────────
function get_schema_sql(): array {
    return [
        // migrations tracking table
        "CREATE TABLE IF NOT EXISTS `migrations` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `migration` varchar(255) NOT NULL,
          `batch` int NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // prompts
        "CREATE TABLE IF NOT EXISTS `prompts` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `description` varchar(255) DEFAULT NULL,
          `content` longtext NOT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `version` int unsigned NOT NULL DEFAULT '1',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `prompts_name_unique` (`name`),
          KEY `prompts_name_is_active_index` (`name`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // categories (before users so FK can reference later)
        "CREATE TABLE IF NOT EXISTS `categories` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `parent_id` bigint unsigned DEFAULT NULL,
          `slug` varchar(100) NOT NULL,
          `name_en` varchar(100) NOT NULL,
          `name_ne` varchar(100) DEFAULT NULL,
          `meta_title` varchar(160) DEFAULT NULL,
          `sort_order` smallint NOT NULL DEFAULT '0',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `is_exclusive` tinyint(1) NOT NULL DEFAULT '0',
          `color` varchar(20) DEFAULT NULL,
          `icon` varchar(50) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `categories_slug_unique` (`slug`),
          CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // users (full combined schema)
        "CREATE TABLE IF NOT EXISTS `users` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `uuid` char(36) NOT NULL,
          `name` varchar(255) NOT NULL,
          `first_name` varchar(80) DEFAULT NULL,
          `last_name` varchar(80) DEFAULT NULL,
          `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
          `reward_system` tinyint(1) NOT NULL DEFAULT '0',
          `profile_view_count` bigint unsigned NOT NULL DEFAULT '0',
          `extra_permissions` json DEFAULT NULL,
          `username` varchar(50) NOT NULL,
          `email` varchar(255) NOT NULL,
          `password` varchar(255) DEFAULT NULL,
          `avatar_url` varchar(255) DEFAULT NULL,
          `cover_url` varchar(500) DEFAULT NULL,
          `role` enum('reader','author','editor','admin') NOT NULL DEFAULT 'reader',
          `ai_credits_used` int unsigned NOT NULL DEFAULT '0',
          `ai_credits_reset_at` timestamp NULL DEFAULT NULL,
          `bio` text,
          `website` varchar(200) DEFAULT NULL,
          `social_links` json DEFAULT NULL,
          `last_seen_at` timestamp NULL DEFAULT NULL,
          `is_banned` tinyint(1) NOT NULL DEFAULT '0',
          `provider` varchar(255) DEFAULT NULL,
          `provider_id` varchar(255) DEFAULT NULL,
          `email_verified_at` timestamp NULL DEFAULT NULL,
          `remember_token` varchar(100) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `users_uuid_unique` (`uuid`),
          UNIQUE KEY `users_username_unique` (`username`),
          UNIQUE KEY `users_email_unique` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // tags
        "CREATE TABLE IF NOT EXISTS `tags` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `slug` varchar(100) NOT NULL,
          `name_en` varchar(100) NOT NULL,
          `name_ne` varchar(100) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `tags_slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // posts (full combined schema)
        "CREATE TABLE IF NOT EXISTS `posts` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `uuid` char(36) NOT NULL,
          `author_id` bigint unsigned NOT NULL,
          `category_id` bigint unsigned NOT NULL,
          `slug` varchar(200) NOT NULL,
          `title` varchar(300) NOT NULL,
          `excerpt` text,
          `body` longtext,
          `status` enum('draft','review','published','archived') NOT NULL DEFAULT 'draft',
          `post_format` varchar(50) NOT NULL DEFAULT 'article',
          `is_featured` tinyint(1) NOT NULL DEFAULT '0',
          `view_count` bigint unsigned NOT NULL DEFAULT '0',
          `thumbnail_url` varchar(255) DEFAULT NULL,
          `seo_title` varchar(160) DEFAULT NULL,
          `seo_desc` varchar(320) DEFAULT NULL,
          `published_at` timestamp NULL DEFAULT NULL,
          `scheduled_at` timestamp NULL DEFAULT NULL,
          `event_start_at` timestamp NULL DEFAULT NULL,
          `event_end_at` timestamp NULL DEFAULT NULL,
          `event_organizer` varchar(200) DEFAULT NULL,
          `event_venue` varchar(200) DEFAULT NULL,
          `event_address` text,
          `event_lat` decimal(10,6) DEFAULT NULL,
          `event_lng` decimal(10,6) DEFAULT NULL,
          `event_schedule` json DEFAULT NULL,
          `event_highlights` json DEFAULT NULL,
          `event_speakers` json DEFAULT NULL,
          `event_registration_type` varchar(50) NOT NULL DEFAULT 'none',
          `event_faq` json DEFAULT NULL,
          `optional_url` varchar(500) DEFAULT NULL,
          `sources` json DEFAULT NULL,
          `article_faq` json DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `posts_uuid_unique` (`uuid`),
          UNIQUE KEY `posts_slug_unique` (`slug`),
          KEY `posts_category_id_status_index` (`category_id`,`status`),
          KEY `posts_published_at_status_index` (`published_at`,`status`),
          KEY `posts_is_featured_index` (`is_featured`),
          CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // post_tag
        "CREATE TABLE IF NOT EXISTS `post_tag` (
          `post_id` bigint unsigned NOT NULL,
          `tag_id` bigint unsigned NOT NULL,
          PRIMARY KEY (`post_id`,`tag_id`),
          CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
          CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // surveys
        "CREATE TABLE IF NOT EXISTS `surveys` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `post_id` bigint unsigned DEFAULT NULL,
          `title` varchar(300) NOT NULL,
          `type` enum('poll','quiz','nps','rating','form') NOT NULL DEFAULT 'poll',
          `status` enum('draft','active','closed') NOT NULL DEFAULT 'draft',
          `starts_at` timestamp NULL DEFAULT NULL,
          `ends_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          CONSTRAINT `surveys_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `survey_questions` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` bigint unsigned NOT NULL,
          `sort_order` smallint NOT NULL DEFAULT '0',
          `question` text NOT NULL,
          `type` enum('single','multiple','text','rating','nps') NOT NULL DEFAULT 'single',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `survey_options` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `survey_question_id` bigint unsigned NOT NULL,
          `label` varchar(300) NOT NULL,
          `sort_order` smallint NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          CONSTRAINT `survey_options_survey_question_id_foreign` FOREIGN KEY (`survey_question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `survey_responses` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `survey_id` bigint unsigned NOT NULL,
          `user_id` bigint unsigned DEFAULT NULL,
          `session_id` char(64) DEFAULT NULL,
          `ip_hash` char(64) DEFAULT NULL,
          `country_code` char(2) DEFAULT NULL,
          `completed_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `survey_responses_survey_id_created_at_index` (`survey_id`,`created_at`),
          CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
          CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // followers
        "CREATE TABLE IF NOT EXISTS `followers` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `follower_id` bigint unsigned NOT NULL,
          `following_id` bigint unsigned NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `followers_follower_id_following_id_unique` (`follower_id`,`following_id`),
          CONSTRAINT `followers_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `followers_following_id_foreign` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // reactions
        "CREATE TABLE IF NOT EXISTS `reactions` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `post_id` bigint unsigned NOT NULL,
          `user_id` bigint unsigned DEFAULT NULL,
          `session_key` char(64) DEFAULT NULL,
          `emoji` enum('heart','laugh','wow','sad','angry','amazing') NOT NULL DEFAULT 'heart',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `reactions_post_id_user_id_unique` (`post_id`,`user_id`),
          KEY `reactions_post_id_emoji_index` (`post_id`,`emoji`),
          CONSTRAINT `reactions_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
          CONSTRAINT `reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // comments
        "CREATE TABLE IF NOT EXISTS `comments` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `post_id` bigint unsigned NOT NULL,
          `user_id` bigint unsigned DEFAULT NULL,
          `parent_id` bigint unsigned DEFAULT NULL,
          `body` text NOT NULL,
          `guest_name` varchar(255) DEFAULT NULL,
          `guest_email` varchar(255) DEFAULT NULL,
          `is_approved` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `comments_parent_id_index` (`parent_id`),
          KEY `comments_post_id_parent_id_index` (`post_id`,`parent_id`),
          CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
          CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // bookmarks
        "CREATE TABLE IF NOT EXISTS `bookmarks` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint unsigned NOT NULL,
          `post_id` bigint unsigned NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `bookmarks_user_id_post_id_unique` (`user_id`,`post_id`),
          CONSTRAINT `bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `bookmarks_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // notifications
        "CREATE TABLE IF NOT EXISTS `notifications` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint unsigned NOT NULL,
          `type` varchar(255) NOT NULL,
          `data` json NOT NULL,
          `read_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
          CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // contact_messages
        "CREATE TABLE IF NOT EXISTS `contact_messages` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `subject` varchar(255) DEFAULT NULL,
          `body` text NOT NULL,
          `read_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // newsletter_subscribers
        "CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `email` varchar(255) NOT NULL,
          `name` varchar(255) DEFAULT NULL,
          `token` varchar(64) NOT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `newsletter_subscribers_email_unique` (`email`),
          UNIQUE KEY `newsletter_subscribers_token_unique` (`token`),
          KEY `newsletter_subscribers_email_is_active_index` (`email`,`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ad_zones
        "CREATE TABLE IF NOT EXISTS `ad_zones` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `position` varchar(255) NOT NULL,
          `code` text NOT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // polls
        "CREATE TABLE IF NOT EXISTS `polls` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `post_id` bigint unsigned DEFAULT NULL,
          `author_id` bigint unsigned NOT NULL,
          `question` varchar(255) NOT NULL,
          `allow_multiple` tinyint(1) NOT NULL DEFAULT '0',
          `expires_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          CONSTRAINT `polls_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
          CONSTRAINT `polls_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `poll_options` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `poll_id` bigint unsigned NOT NULL,
          `text` varchar(255) NOT NULL,
          `votes_count` int unsigned NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          CONSTRAINT `poll_options_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS `poll_votes` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `poll_id` bigint unsigned NOT NULL,
          `poll_option_id` bigint unsigned NOT NULL,
          `user_id` bigint unsigned DEFAULT NULL,
          `session_key` varchar(64) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `poll_votes_poll_id_user_id_unique` (`poll_id`,`user_id`),
          CONSTRAINT `poll_votes_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
          CONSTRAINT `poll_votes_poll_option_id_foreign` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,
          CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // widgets
        "CREATE TABLE IF NOT EXISTS `widgets` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `type` varchar(50) NOT NULL,
          `title` varchar(150) NOT NULL,
          `where_to_display` varchar(80) NOT NULL,
          `display_order` smallint unsigned NOT NULL DEFAULT '0',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `config` json DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // roles
        "CREATE TABLE IF NOT EXISTS `roles` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(80) NOT NULL,
          `name_ne` varchar(100) DEFAULT NULL,
          `slug` varchar(80) NOT NULL,
          `badge_label` varchar(80) DEFAULT NULL,
          `badge_color` varchar(30) NOT NULL DEFAULT 'gray',
          `badge_icon` varchar(30) NOT NULL DEFAULT 'user',
          `permissions` json DEFAULT NULL,
          `is_default` tinyint(1) NOT NULL DEFAULT '0',
          `is_system` tinyint(1) NOT NULL DEFAULT '0',
          `ai_credits` int unsigned NOT NULL DEFAULT '0',
          `sort_order` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `roles_slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // navigation_items
        "CREATE TABLE IF NOT EXISTS `navigation_items` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `label` varchar(255) NOT NULL,
          `url` varchar(255) DEFAULT NULL,
          `type` varchar(255) NOT NULL DEFAULT 'custom',
          `target_id` bigint unsigned DEFAULT NULL,
          `language` varchar(10) NOT NULL DEFAULT 'en',
          `sort_order` int NOT NULL DEFAULT '0',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `parent_id` bigint unsigned DEFAULT NULL,
          `open_in` varchar(10) NOT NULL DEFAULT '_self',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // pages
        "CREATE TABLE IF NOT EXISTS `pages` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `slug` varchar(255) NOT NULL,
          `content` longtext,
          `status` varchar(20) NOT NULL DEFAULT 'active',
          `page_type` varchar(30) NOT NULL DEFAULT 'custom',
          `menu_position` varchar(30) DEFAULT NULL,
          `language` varchar(10) NOT NULL DEFAULT 'en',
          `meta_title` varchar(255) DEFAULT NULL,
          `meta_description` varchar(255) DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `pages_slug_unique` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // badges
        "CREATE TABLE IF NOT EXISTS `badges` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `name_ne` varchar(255) DEFAULT NULL,
          `color` varchar(20) NOT NULL DEFAULT '#6366f1',
          `icon` varchar(255) DEFAULT NULL,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `sort_order` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // rss_feeds
        "CREATE TABLE IF NOT EXISTS `rss_feeds` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `url` varchar(255) NOT NULL,
          `language` varchar(10) NOT NULL DEFAULT 'en',
          `category_id` bigint unsigned DEFAULT NULL,
          `post_count` int NOT NULL DEFAULT '1',
          `auto_update` tinyint(1) NOT NULL DEFAULT '1',
          `show_read_more` tinyint(1) NOT NULL DEFAULT '1',
          `add_as_draft` tinyint(1) NOT NULL DEFAULT '0',
          `generate_keywords` tinyint(1) NOT NULL DEFAULT '0',
          `read_more_text` varchar(255) NOT NULL DEFAULT 'Read More',
          `default_image` varchar(255) DEFAULT NULL,
          `images_source` varchar(30) NOT NULL DEFAULT 'original',
          `imported_count` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // languages
        "CREATE TABLE IF NOT EXISTS `languages` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `short_form` varchar(10) NOT NULL,
          `code` varchar(20) NOT NULL,
          `editor_language` varchar(30) DEFAULT NULL,
          `direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `sort_order` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // questions
        "CREATE TABLE IF NOT EXISTS `questions` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint unsigned NOT NULL,
          `title` varchar(255) NOT NULL,
          `slug` varchar(255) NOT NULL,
          `content` longtext,
          `category_id` bigint unsigned DEFAULT NULL,
          `featured_image` varchar(255) DEFAULT NULL,
          `is_poll` tinyint(1) NOT NULL DEFAULT '0',
          `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
          `is_private` tinyint(1) NOT NULL DEFAULT '0',
          `notify_email` tinyint(1) NOT NULL DEFAULT '1',
          `status` enum('open','closed','pending') NOT NULL DEFAULT 'open',
          `views_count` int unsigned NOT NULL DEFAULT '0',
          `answers_count` int unsigned NOT NULL DEFAULT '0',
          `best_answer_id` bigint unsigned DEFAULT NULL,
          `votes` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `questions_slug_unique` (`slug`),
          CONSTRAINT `questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // answers
        "CREATE TABLE IF NOT EXISTS `answers` (
          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
          `question_id` bigint unsigned NOT NULL,
          `user_id` bigint unsigned NOT NULL,
          `content` longtext NOT NULL,
          `is_best` tinyint(1) NOT NULL DEFAULT '0',
          `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
          `votes` int NOT NULL DEFAULT '0',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          CONSTRAINT `answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
          CONSTRAINT `answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // question_tag
        "CREATE TABLE IF NOT EXISTS `question_tag` (
          `question_id` bigint unsigned NOT NULL,
          `tag_id` bigint unsigned NOT NULL,
          PRIMARY KEY (`question_id`,`tag_id`),
          CONSTRAINT `question_tag_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
          CONSTRAINT `question_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
}

function run_schema(PDO $pdo): array {
    $log = [];
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach (get_schema_sql() as $sql) {
        try {
            $pdo->exec($sql);
            // extract table name for logging
            preg_match('/CREATE TABLE IF NOT EXISTS `(\w+)`/', $sql, $m);
            $log[] = ['ok' => true, 'msg' => 'Table OK: ' . ($m[1] ?? '?')];
        } catch (Exception $e) {
            preg_match('/CREATE TABLE IF NOT EXISTS `(\w+)`/', $sql, $m);
            $log[] = ['ok' => false, 'msg' => 'Table FAIL ' . ($m[1] ?? '?') . ': ' . $e->getMessage()];
        }
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    // Record migrations as run
    try {
        $pdo->exec("DELETE FROM `migrations`");
        $batch = 1;
        $migrations = [
            '2026_08_16_000001_create_prompts_table',
            '2026_08_16_000002_create_core_tables',
            '2026_08_16_000003_create_social_tables',
            '2026_08_16_084709_create_comments_table',
            '2026_08_16_084711_create_bookmarks_table',
            '2026_08_16_084712_create_notifications_table',
            '2026_08_16_131408_create_contact_messages_table',
            '2026_08_16_131410_create_newsletter_subscribers_table',
            '2026_08_16_132821_add_scheduled_at_to_posts_table',
            '2026_08_16_132822_create_ad_zones_table',
            '2026_08_16_132824_create_polls_table',
            '2026_08_16_140000_add_profile_fields_to_users_table',
            '2026_08_16_140001_create_widgets_table',
            '2026_08_16_140002_add_ga_to_settings',
            '2026_08_16_142731_create_roles_table',
            '2026_08_16_143316_add_extra_fields_to_users_table',
            '2026_08_16_150000_add_social_profile_fields_to_users',
            '2026_08_16_160000_add_flags_to_categories_table',
            '2026_08_16_170000_add_event_fields_to_posts_table',
            '2026_08_16_180000_add_article_fields_to_posts_table',
            '2026_08_16_190000_create_navigation_items_table',
            '2026_08_16_190001_create_pages_table',
            '2026_08_16_200000_create_badges_table',
            '2026_08_16_210000_create_rss_feeds_table',
            '2026_08_16_210001_create_languages_table',
            '2026_08_16_230000_create_questions_table',
            '2026_09_06_184954_add_votes_to_questions_table',
        ];
        $stmt = $pdo->prepare("INSERT INTO `migrations` (migration, batch) VALUES (?, ?)");
        foreach ($migrations as $m) { $stmt->execute([$m, $batch]); }
        $log[] = ['ok' => true, 'msg' => 'Migration records written (' . count($migrations) . ')'];
    } catch (Exception $e) {
        $log[] = ['ok' => false, 'msg' => 'Migration records: ' . $e->getMessage()];
    }
    return $log;
}

function seed_prompts(PDO $pdo): array {
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM prompts")->fetchColumn();
        if ($count > 0) return [['ok' => true, 'msg' => 'Prompts already seeded.']];

        $now = date('Y-m-d H:i:s');
        $prompts = [
            ['author_assistant', 'Content writing, SEO, and translation', 'You are the editorial assistant for a Nepali news platform. Help authors write, translate, and optimize content.'],
            ['content_moderation', 'Content safety system', 'You are the content safety system. Review content for policy violations and output JSON: {"action":"approve|review|reject","confidence":0.9,"violations":[],"reason":"...","severity":"low"}'],
            ['search_enhancer', 'Search query enhancement', 'You enhance search queries. Output JSON: {"expanded_terms":[],"suggested_categories":[],"intent":"informational","corrected_query":null,"nepali_translation":null}'],
            ['survey_narrator', 'Analytics report generator', 'You generate analytics reports for survey data. Structure: Executive Summary, Key Findings, Demographic Patterns, Outliers, Recommendations.'],
            ['newsletter_generator', 'Weekly email digest generator', 'You write weekly email digests. Include subject line, preview text, intro, article summaries, and closing.'],
        ];
        $stmt = $pdo->prepare("INSERT INTO prompts (name, description, content, is_active, version, created_at, updated_at) VALUES (?,?,?,1,1,?,?)");
        foreach ($prompts as $p) {
            $stmt->execute([$p[0], $p[1], $p[2], $now, $now]);
        }
        return [['ok' => true, 'msg' => 'Prompts seeded (5 records).']];
    } catch (Exception $e) {
        return [['ok' => false, 'msg' => 'Prompts seed: ' . $e->getMessage()]];
    }
}

function create_admin_user(PDO $pdo, array $d): array {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$d['admin_email']]);
        $exists = (int)$stmt->fetchColumn() > 0;

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $d['admin_name'])) ?: 'admin';
        $hash = password_hash($d['admin_pass'], PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE users SET name=?, password=?, role='admin', email_verified_at=? WHERE email=?");
            $stmt->execute([$d['admin_name'], $hash, $now, $d['admin_email']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (uuid,name,username,email,password,role,email_verified_at,created_at,updated_at) VALUES (?,?,?,?,?,'admin',?,?,?)");
            $stmt->execute([$uuid, $d['admin_name'], $username, $d['admin_email'], $hash, $now, $now, $now]);
        }
        return [['ok' => true, 'msg' => 'Admin account ' . ($exists ? 'updated' : 'created') . ': ' . $d['admin_email']]];
    } catch (Exception $e) {
        return [['ok' => false, 'msg' => 'Admin user: ' . $e->getMessage()]];
    }
}

function requirements(): array {
    $checks = [];
    $checks[] = ['name' => 'PHP Version (≥ 8.1)', 'pass' => version_compare(PHP_VERSION, '8.1.0', '>='), 'value' => PHP_VERSION];
    foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'fileinfo'] as $ext) {
        $checks[] = ['name' => "Extension: $ext", 'pass' => extension_loaded($ext), 'value' => extension_loaded($ext) ? 'Loaded' : 'Missing'];
    }
    foreach ([APP_ROOT . '/storage', APP_ROOT . '/bootstrap/cache'] as $dir) {
        $label = str_replace(APP_ROOT . '/', '', $dir);
        $checks[] = ['name' => "$label (writable)", 'pass' => is_writable($dir), 'value' => is_writable($dir) ? 'Writable' : 'Not writable'];
    }
    $checks[] = ['name' => '.env writable', 'pass' => is_writable(APP_ROOT), 'value' => is_writable(APP_ROOT) ? 'OK' : 'Not writable'];
    return $checks;
}

function all_critical_pass(array $checks): bool {
    foreach ($checks as $c) { if (!$c['pass']) return false; }
    return true;
}

// ── POST handling ─────────────────────────────────────────────────────────────
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset') { session_destroy(); header('Location: ' . $_SERVER['PHP_SELF']); exit; }

    if ($action === 'step1') {
        if (all_critical_pass(requirements())) set_step(2);
        else $errors[] = 'Fix the failing requirements first.';
    }

    if ($action === 'test_db') {
        $d = ['db_host'=>trim($_POST['db_host']??'127.0.0.1'),'db_port'=>trim($_POST['db_port']??'3306'),'db_name'=>trim($_POST['db_name']??''),'db_user'=>trim($_POST['db_user']??''),'db_pass'=>$_POST['db_pass']??''];
        $r = test_db($d);
        $_SESSION['db_test_ok'] = $r['ok'];
        $_SESSION['db_test_msg'] = $r['ok'] ? 'Connected! MySQL '.$r['version'] : $r['error'];
    }

    if ($action === 'step2') {
        $d = ['db_host'=>trim($_POST['db_host']??'127.0.0.1'),'db_port'=>trim($_POST['db_port']??'3306'),'db_name'=>trim($_POST['db_name']??''),'db_user'=>trim($_POST['db_user']??''),'db_pass'=>$_POST['db_pass']??''];
        if (empty($d['db_name']) || empty($d['db_user'])) { $errors[] = 'Database name and username are required.'; }
        else { $t = test_db($d); if (!$t['ok']) $errors[] = 'Cannot connect: '.$t['error']; else { merge_data($d); set_step(3); } }
    }

    if ($action === 'step3') {
        $d = ['app_name'=>trim($_POST['app_name']??'nkhoj'),'app_url'=>rtrim(trim($_POST['app_url']??''),'/'),'timezone'=>trim($_POST['timezone']??'Asia/Kathmandu'),'admin_name'=>trim($_POST['admin_name']??''),'admin_email'=>trim($_POST['admin_email']??''),'admin_pass'=>$_POST['admin_pass']??'','admin_pass2'=>$_POST['admin_pass2']??'','mail_host'=>trim($_POST['mail_host']??''),'mail_port'=>trim($_POST['mail_port']??'587'),'mail_user'=>trim($_POST['mail_user']??''),'mail_pass'=>$_POST['mail_pass']??'','mail_enc'=>trim($_POST['mail_enc']??'tls'),'mail_from'=>trim($_POST['mail_from']??'')];
        if (empty($d['app_name'])) $errors[] = 'App name required.';
        if (empty($d['app_url'])) $errors[] = 'App URL required.';
        if (empty($d['admin_name'])) $errors[] = 'Admin name required.';
        if (!filter_var($d['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email required.';
        if (strlen($d['admin_pass']) < 8) $errors[] = 'Password must be ≥ 8 chars.';
        if ($d['admin_pass'] !== $d['admin_pass2']) $errors[] = 'Passwords do not match.';
        if (empty($errors)) { merge_data($d); set_step(4); }
    }

    if ($action === 'install') {
        set_time_limit(300);
        $d = data();
        $log = [];
        $fatal = false;

        // 1. Write .env
        if (!write_env($d)) {
            $log[] = ['ok'=>false,'msg'=>'Failed to write .env — check permissions on '.APP_ROOT];
            $fatal = true;
        } else {
            $log[] = ['ok'=>true,'msg'=>'.env written. APP_KEY set.'];
        }

        if (!$fatal) {
            try {
                $pdo = make_pdo($d);
                // 2. Run schema
                $schemaLog = run_schema($pdo);
                $log = array_merge($log, $schemaLog);
                $hasFail = !empty(array_filter($schemaLog, fn($r) => !$r['ok']));

                // 3. Seed prompts
                $log = array_merge($log, seed_prompts($pdo));

                // 4. Create admin
                $log = array_merge($log, create_admin_user($pdo, $d));

            } catch (Exception $e) {
                $log[] = ['ok'=>false,'msg'=>'Database error: '.$e->getMessage()];
                $fatal = true;
            }
        }

        if (!$fatal) {
            // 5. Mark installed
            @file_put_contents(APP_ROOT.'/storage/installed', date('Y-m-d H:i:s'));
            // 6. Clear config cache file if exists
            @unlink(APP_ROOT.'/bootstrap/cache/config.php');
            @unlink(APP_ROOT.'/bootstrap/cache/routes-v7.php');
            $log[] = ['ok'=>true,'msg'=>'Installation complete!'];
            set_step(5);
        }

        $_SESSION['install_log'] = $log;
        $_SESSION['install_fatal'] = $fatal;
        header('Location: '.$_SERVER['PHP_SELF']); exit;
    }

    header('Location: '.$_SERVER['PHP_SELF']); exit;
}

$step = step();
$d = data();
$reqs = ($step === 1) ? requirements() : [];
$all_pass = ($step === 1) ? all_critical_pass($reqs) : true;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>nkhoj Installer</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:#0f172a}
.step-active{background:#6366f1;color:#fff}
.step-done{background:#10b981;color:#fff}
.step-todo{background:#1e293b;color:#64748b;border:1px solid #334155}
input:focus,select:focus{box-shadow:0 0 0 2px #6366f1;outline:none}
.log-ok{color:#4ade80}.log-fail{color:#f87171}
</style>
</head>
<body class="min-h-screen flex flex-col items-center py-12 px-4">
<div class="w-full max-w-2xl">

<div class="text-center mb-8">
  <span class="text-3xl font-black text-white">nkhoj</span>
  <span class="ml-2 text-xs font-bold text-indigo-400 bg-indigo-900/40 border border-indigo-700 px-2 py-0.5 rounded-full">Installer v<?= INSTALLER_VERSION ?></span>
  <p class="text-slate-400 text-sm mt-1">Set up your platform in minutes — no SSH required</p>
</div>

<div class="flex items-center justify-center gap-1 mb-8">
<?php $labels=['Requirements','Database','Configuration','Installing','Complete']; foreach($labels as $i=>$label): $n=$i+1; $cls=$n<$step?'step-done':($n===$step?'step-active':'step-todo'); ?>
<div class="flex items-center gap-1">
  <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold <?= $cls ?>"><?= $n<$step?'✓':$n ?></div>
  <span class="text-xs hidden sm:block <?= $n===$step?'text-white':'text-slate-500' ?>"><?= $label ?></span>
</div><?php if($i<4): ?><div class="w-4 h-px bg-slate-700"></div><?php endif; endforeach; ?>
</div>

<?php if(!empty($errors)): ?>
<div class="mb-5 bg-red-900/30 border border-red-700 rounded-xl p-4">
<?php foreach($errors as $e): ?><p class="text-red-300 text-sm">✕ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">

<?php if($step===1): ?>
<div class="px-6 py-5 border-b border-slate-700">
  <h2 class="text-lg font-bold text-white">Server Requirements</h2>
  <p class="text-slate-400 text-sm mt-0.5">Checking minimum requirements.</p>
</div>
<div class="p-6">
  <div class="space-y-2 mb-6">
  <?php foreach($reqs as $r): ?>
  <div class="flex justify-between py-2 px-3 rounded-lg <?= $r['pass']?'bg-slate-700/50':'bg-red-900/20 border border-red-800' ?>">
    <span class="text-sm <?= $r['pass']?'text-slate-300':'text-red-300' ?>"><?= htmlspecialchars($r['name']) ?></span>
    <span class="text-xs font-semibold <?= $r['pass']?'text-green-400':'text-red-400' ?>"><?= $r['pass']?'✓ ':'✕ ' ?><?= htmlspecialchars($r['value']) ?></span>
  </div>
  <?php endforeach; ?>
  </div>
  <form method="POST"><input type="hidden" name="action" value="step1">
  <button <?= !$all_pass?'disabled':'' ?> class="w-full py-3 rounded-xl text-sm font-bold <?= $all_pass?'bg-indigo-600 hover:bg-indigo-500 text-white':'bg-slate-700 text-slate-500 cursor-not-allowed' ?>">
    Continue →
  </button></form>
</div>

<?php elseif($step===2): ?>
<div class="px-6 py-5 border-b border-slate-700">
  <h2 class="text-lg font-bold text-white">Database Configuration</h2>
  <p class="text-slate-400 text-sm mt-0.5">Enter your cPanel MySQL credentials.</p>
</div>
<div class="p-6">
<?php if(isset($_SESSION['db_test_ok'])): ?>
<div class="mb-5 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 <?= $_SESSION['db_test_ok']?'bg-green-900/30 border border-green-700 text-green-400':'bg-red-900/30 border border-red-700 text-red-400' ?>">
  <?= $_SESSION['db_test_ok']?'✓ ':'✕ ' ?><?= htmlspecialchars($_SESSION['db_test_msg']??'') ?>
</div>
<?php unset($_SESSION['db_test_ok'],$_SESSION['db_test_msg']); endif; ?>
<div class="grid grid-cols-2 gap-4 mb-4">
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">DB Host</label>
    <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($d['db_host']??'127.0.0.1') ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Port</label>
    <input type="number" id="db_port" name="db_port" value="<?= htmlspecialchars($d['db_port']??'3306') ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div class="col-span-2"><label class="block text-xs font-semibold text-slate-400 mb-1.5">Database Name *</label>
    <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($d['db_name']??'') ?>" placeholder="e.g. alphaome_dewnkhoj" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Username *</label>
    <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($d['db_user']??'') ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Password</label>
    <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($d['db_pass']??'') ?>" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
</div>
<form method="POST" class="mb-3" id="fTest"><input type="hidden" name="action" value="test_db">
  <button onclick="sync()" type="submit" class="w-full py-2.5 border border-indigo-600 text-indigo-400 hover:bg-indigo-900/30 rounded-xl text-sm font-semibold transition-colors">Test Connection</button>
</form>
<form method="POST" id="f2"><input type="hidden" name="action" value="step2">
  <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold">Save & Continue →</button>
</form>
<script>
function sync(){['db_host','db_port','db_name','db_user','db_pass'].forEach(id=>{const v=document.getElementById(id).value;document.querySelectorAll('[name="'+id+'"]').forEach(el=>el.value=v);})}
document.getElementById('f2').addEventListener('submit',sync);
</script>
</div>

<?php elseif($step===3): ?>
<div class="px-6 py-5 border-b border-slate-700">
  <h2 class="text-lg font-bold text-white">Application Configuration</h2>
</div>
<form method="POST" class="p-6 space-y-5"><input type="hidden" name="action" value="step3">
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Site Name *</label>
    <input type="text" name="app_name" value="<?= htmlspecialchars($d['app_name']??'nkhoj') ?>" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Site URL *</label>
    <input type="url" name="app_url" value="<?= htmlspecialchars($d['app_url']??'https://dewanlabung.com.np') ?>" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
  <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Timezone</label>
    <select name="timezone" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white">
    <?php foreach(['Asia/Kathmandu','Asia/Kolkata','UTC','America/New_York','Europe/London','Asia/Dubai','Asia/Singapore'] as $tz): ?>
    <option value="<?= $tz ?>" <?= ($d['timezone']??'Asia/Kathmandu')===$tz?'selected':'' ?>><?= $tz ?></option>
    <?php endforeach; ?>
    </select></div>
  <div class="border-t border-slate-700 pt-5">
    <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-3">Admin Account</p>
    <div class="space-y-3">
      <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Full Name *</label>
        <input type="text" name="admin_name" value="<?= htmlspecialchars($d['admin_name']??'') ?>" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
      <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Email *</label>
        <input type="email" name="admin_email" value="<?= htmlspecialchars($d['admin_email']??'') ?>" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Password * (min 8)</label>
          <input type="password" name="admin_pass" required minlength="8" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
        <div><label class="block text-xs font-semibold text-slate-400 mb-1.5">Confirm</label>
          <input type="password" name="admin_pass2" required class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2.5 text-sm text-white"></div>
      </div>
    </div>
  </div>
  <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold">Review & Install →</button>
</form>

<?php elseif($step===4): ?>
<div class="px-6 py-5 border-b border-slate-700">
  <h2 class="text-lg font-bold text-white">Ready to Install</h2>
  <p class="text-slate-400 text-sm mt-0.5">No exec() or SSH needed — runs entirely via PDO.</p>
</div>
<div class="p-6">
  <div class="bg-slate-700/50 rounded-xl p-4 mb-5 space-y-2 text-sm">
    <div class="flex justify-between"><span class="text-slate-400">Site</span><span class="text-white font-medium"><?= htmlspecialchars($d['app_name']??'') ?></span></div>
    <div class="flex justify-between"><span class="text-slate-400">URL</span><span class="text-white font-medium"><?= htmlspecialchars($d['app_url']??'') ?></span></div>
    <div class="flex justify-between"><span class="text-slate-400">Database</span><span class="text-white font-medium"><?= htmlspecialchars($d['db_name']??'') ?> @ <?= htmlspecialchars($d['db_host']??'') ?></span></div>
    <div class="flex justify-between"><span class="text-slate-400">Admin</span><span class="text-white font-medium"><?= htmlspecialchars($d['admin_email']??'') ?></span></div>
  </div>
  <div class="bg-amber-900/30 border border-amber-700 rounded-xl p-4 mb-5">
    <p class="text-amber-300 text-sm font-semibold">⚠ Database must already exist</p>
    <p class="text-amber-400 text-xs mt-1">Make sure <strong><?= htmlspecialchars($d['db_name']??'') ?></strong> exists in cPanel MySQL Databases before clicking Install.</p>
  </div>
  <form method="POST" id="iForm"><input type="hidden" name="action" value="install">
    <button id="iBtn" type="submit" onclick="go()" class="w-full py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2">
      Install nkhoj (no SSH required)
    </button>
  </form>
  <div id="prog" class="hidden mt-4 text-center">
    <svg class="inline w-5 h-5 animate-spin text-indigo-400 mr-2" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" class="opacity-75"/></svg>
    <span class="text-indigo-400 text-sm">Creating tables and admin account…</span>
  </div>
  <script>function go(){document.getElementById('iBtn').disabled=true;document.getElementById('prog').classList.remove('hidden');}</script>
</div>

<?php elseif($step===5): ?>
<?php $log=$_SESSION['install_log']??[]; $fatal=$_SESSION['install_fatal']??false; ?>
<div class="px-6 py-5 border-b border-slate-700">
  <?php if(!$fatal): ?>
  <h2 class="text-lg font-bold text-green-400">✓ Installation Complete!</h2>
  <?php else: ?>
  <h2 class="text-lg font-bold text-amber-400">⚠ Partially Completed</h2>
  <?php endif; ?>
</div>
<div class="p-6">
  <div class="bg-slate-900 rounded-xl p-4 mb-5 font-mono text-xs space-y-1.5 max-h-64 overflow-y-auto">
  <?php foreach($log as $entry): ?>
  <div class="<?= $entry['ok']?'log-ok':'log-fail' ?>"><?= $entry['ok']?'[OK]  ':'[ERR] ' ?><?= htmlspecialchars($entry['msg']) ?></div>
  <?php endforeach; ?>
  </div>
  <?php if(!$fatal): ?>
  <div class="bg-green-900/20 border border-green-800 rounded-xl p-4 mb-5">
    <p class="text-green-300 text-sm font-semibold mb-2">Next Steps</p>
    <ul class="text-green-400 text-xs space-y-1 list-disc list-inside">
      <li>Log in at <a href="<?= htmlspecialchars(rtrim($d['app_url']??'','/'))?>/login" class="text-indigo-400 underline" target="_blank">/login</a> with your admin email</li>
      <li>Then visit <a href="<?= htmlspecialchars(rtrim($d['app_url']??'','/'))?>/admin" class="text-indigo-400 underline" target="_blank">/admin</a></li>
      <li>Delete <code>public/install/</code> from your server for security</li>
    </ul>
  </div>
  <a href="<?= htmlspecialchars(rtrim($d['app_url']??'/','/'))?>/admin" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold flex items-center justify-center">Go to Admin Panel →</a>
  <?php else: ?>
  <div class="bg-red-900/20 border border-red-800 rounded-xl p-4 mb-5">
    <p class="text-red-300 text-sm font-semibold">Check the errors above and try again.</p>
  </div>
  <?php endif; ?>
  <div class="bg-red-900/30 border border-red-800 rounded-xl p-4 mt-4">
    <p class="text-red-300 text-sm font-bold">🔒 Delete the installer after use!</p>
    <p class="text-red-400 text-xs mt-1">In cPanel File Manager: delete <code>public/install/</code> folder.</p>
  </div>
  <form method="POST" class="mt-4"><input type="hidden" name="action" value="reset">
    <button class="w-full py-2 text-slate-500 hover:text-slate-300 text-xs">Start Over</button>
  </form>
</div>
<?php endif; ?>

</div>
<p class="text-center text-slate-600 text-xs mt-6">nkhoj Installer <?= INSTALLER_VERSION ?> · Powered by Laravel</p>
</div>
</body>
</html>
