<?php
/**
 * nkhoj Quick Setup — single page, no sessions, no steps
 * Upload to: public/setup.php
 * Visit: https://yourdomain.com/setup.php
 * DELETE after use!
 */
$APP_ROOT = realpath(__DIR__ . '/../');
$done = []; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_port = trim($_POST['db_port'] ?? '3306');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $app_url = rtrim(trim($_POST['app_url'] ?? ''), '/');
    $app_name = trim($_POST['app_name'] ?? 'nkhoj');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass  = $_POST['admin_pass'] ?? '';

    // 1. Test DB
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $done[] = '✓ Database connected (MySQL ' . $pdo->query('SELECT VERSION()')->fetchColumn() . ')';
    } catch (Exception $e) {
        $errors[] = 'DB connection failed: ' . $e->getMessage();
    }

    if (empty($errors)) {
        // 2. Create all tables
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
"CREATE TABLE IF NOT EXISTS `migrations` (`id` int unsigned NOT NULL AUTO_INCREMENT,`migration` varchar(255) NOT NULL,`batch` int NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `prompts` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`description` varchar(255) DEFAULT NULL,`content` longtext NOT NULL,`is_active` tinyint(1) NOT NULL DEFAULT '1',`version` int unsigned NOT NULL DEFAULT '1',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `prompts_name_unique` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `categories` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`parent_id` bigint unsigned DEFAULT NULL,`slug` varchar(100) NOT NULL,`name_en` varchar(100) NOT NULL,`name_ne` varchar(100) DEFAULT NULL,`meta_title` varchar(160) DEFAULT NULL,`sort_order` smallint NOT NULL DEFAULT '0',`is_active` tinyint(1) NOT NULL DEFAULT '1',`is_exclusive` tinyint(1) NOT NULL DEFAULT '0',`color` varchar(20) DEFAULT NULL,`icon` varchar(50) DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `categories_slug_unique` (`slug`),CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `users` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`uuid` char(36) NOT NULL,`name` varchar(255) NOT NULL,`first_name` varchar(80) DEFAULT NULL,`last_name` varchar(80) DEFAULT NULL,`balance` decimal(10,2) NOT NULL DEFAULT '0.00',`reward_system` tinyint(1) NOT NULL DEFAULT '0',`profile_view_count` bigint unsigned NOT NULL DEFAULT '0',`extra_permissions` json DEFAULT NULL,`username` varchar(50) NOT NULL,`email` varchar(255) NOT NULL,`password` varchar(255) DEFAULT NULL,`avatar_url` varchar(255) DEFAULT NULL,`cover_url` varchar(500) DEFAULT NULL,`role` enum('reader','author','editor','admin') NOT NULL DEFAULT 'reader',`ai_credits_used` int unsigned NOT NULL DEFAULT '0',`ai_credits_reset_at` timestamp NULL DEFAULT NULL,`bio` text,`website` varchar(200) DEFAULT NULL,`social_links` json DEFAULT NULL,`last_seen_at` timestamp NULL DEFAULT NULL,`is_banned` tinyint(1) NOT NULL DEFAULT '0',`provider` varchar(255) DEFAULT NULL,`provider_id` varchar(255) DEFAULT NULL,`email_verified_at` timestamp NULL DEFAULT NULL,`remember_token` varchar(100) DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `users_uuid_unique` (`uuid`),UNIQUE KEY `users_username_unique` (`username`),UNIQUE KEY `users_email_unique` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `tags` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`slug` varchar(100) NOT NULL,`name_en` varchar(100) NOT NULL,`name_ne` varchar(100) DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `tags_slug_unique` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `posts` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`uuid` char(36) NOT NULL,`author_id` bigint unsigned NOT NULL,`category_id` bigint unsigned NOT NULL,`slug` varchar(200) NOT NULL,`title` varchar(300) NOT NULL,`excerpt` text,`body` longtext,`status` enum('draft','review','published','archived') NOT NULL DEFAULT 'draft',`post_format` varchar(50) NOT NULL DEFAULT 'article',`is_featured` tinyint(1) NOT NULL DEFAULT '0',`view_count` bigint unsigned NOT NULL DEFAULT '0',`thumbnail_url` varchar(255) DEFAULT NULL,`seo_title` varchar(160) DEFAULT NULL,`seo_desc` varchar(320) DEFAULT NULL,`published_at` timestamp NULL DEFAULT NULL,`scheduled_at` timestamp NULL DEFAULT NULL,`event_start_at` timestamp NULL DEFAULT NULL,`event_end_at` timestamp NULL DEFAULT NULL,`event_organizer` varchar(200) DEFAULT NULL,`event_venue` varchar(200) DEFAULT NULL,`event_address` text,`event_lat` decimal(10,6) DEFAULT NULL,`event_lng` decimal(10,6) DEFAULT NULL,`event_schedule` json DEFAULT NULL,`event_highlights` json DEFAULT NULL,`event_speakers` json DEFAULT NULL,`event_registration_type` varchar(50) NOT NULL DEFAULT 'none',`event_faq` json DEFAULT NULL,`optional_url` varchar(500) DEFAULT NULL,`sources` json DEFAULT NULL,`article_faq` json DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `posts_uuid_unique` (`uuid`),UNIQUE KEY `posts_slug_unique` (`slug`),KEY `posts_category_id_status_index` (`category_id`,`status`),KEY `posts_published_at_status_index` (`published_at`,`status`),CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `post_tag` (`post_id` bigint unsigned NOT NULL,`tag_id` bigint unsigned NOT NULL,PRIMARY KEY (`post_id`,`tag_id`),CONSTRAINT `post_tag_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,CONSTRAINT `post_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `surveys` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`post_id` bigint unsigned DEFAULT NULL,`title` varchar(300) NOT NULL,`type` enum('poll','quiz','nps','rating','form') NOT NULL DEFAULT 'poll',`status` enum('draft','active','closed') NOT NULL DEFAULT 'draft',`starts_at` timestamp NULL DEFAULT NULL,`ends_at` timestamp NULL DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `surveys_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `survey_questions` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`survey_id` bigint unsigned NOT NULL,`sort_order` smallint NOT NULL DEFAULT '0',`question` text NOT NULL,`type` enum('single','multiple','text','rating','nps') NOT NULL DEFAULT 'single',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `survey_options` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`survey_question_id` bigint unsigned NOT NULL,`label` varchar(300) NOT NULL,`sort_order` smallint NOT NULL DEFAULT '0',PRIMARY KEY (`id`),CONSTRAINT `survey_options_survey_question_id_foreign` FOREIGN KEY (`survey_question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `survey_responses` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`survey_id` bigint unsigned NOT NULL,`user_id` bigint unsigned DEFAULT NULL,`session_id` char(64) DEFAULT NULL,`ip_hash` char(64) DEFAULT NULL,`country_code` char(2) DEFAULT NULL,`completed_at` timestamp NULL DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `followers` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`follower_id` bigint unsigned NOT NULL,`following_id` bigint unsigned NOT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `followers_follower_id_following_id_unique` (`follower_id`,`following_id`),CONSTRAINT `followers_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,CONSTRAINT `followers_following_id_foreign` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `reactions` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`post_id` bigint unsigned NOT NULL,`user_id` bigint unsigned DEFAULT NULL,`session_key` char(64) DEFAULT NULL,`emoji` enum('heart','laugh','wow','sad','angry','amazing') NOT NULL DEFAULT 'heart',`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `reactions_post_id_user_id_unique` (`post_id`,`user_id`),CONSTRAINT `reactions_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,CONSTRAINT `reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `comments` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`post_id` bigint unsigned NOT NULL,`user_id` bigint unsigned DEFAULT NULL,`parent_id` bigint unsigned DEFAULT NULL,`body` text NOT NULL,`guest_name` varchar(255) DEFAULT NULL,`guest_email` varchar(255) DEFAULT NULL,`is_approved` tinyint(1) NOT NULL DEFAULT '1',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),KEY `comments_parent_id_index` (`parent_id`),CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `bookmarks` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`user_id` bigint unsigned NOT NULL,`post_id` bigint unsigned NOT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `bookmarks_user_id_post_id_unique` (`user_id`,`post_id`),CONSTRAINT `bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,CONSTRAINT `bookmarks_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `notifications` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`user_id` bigint unsigned NOT NULL,`type` varchar(255) NOT NULL,`data` json NOT NULL,`read_at` timestamp NULL DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `contact_messages` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`email` varchar(255) NOT NULL,`subject` varchar(255) DEFAULT NULL,`body` text NOT NULL,`read_at` timestamp NULL DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`email` varchar(255) NOT NULL,`name` varchar(255) DEFAULT NULL,`token` varchar(64) NOT NULL,`is_active` tinyint(1) NOT NULL DEFAULT '1',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `newsletter_subscribers_email_unique` (`email`),UNIQUE KEY `newsletter_subscribers_token_unique` (`token`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `ad_zones` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`position` varchar(255) NOT NULL,`code` text NOT NULL,`is_active` tinyint(1) NOT NULL DEFAULT '1',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `polls` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`post_id` bigint unsigned DEFAULT NULL,`author_id` bigint unsigned NOT NULL,`question` varchar(255) NOT NULL,`allow_multiple` tinyint(1) NOT NULL DEFAULT '0',`expires_at` timestamp NULL DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `polls_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,CONSTRAINT `polls_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `poll_options` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`poll_id` bigint unsigned NOT NULL,`text` varchar(255) NOT NULL,`votes_count` int unsigned NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `poll_options_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `poll_votes` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`poll_id` bigint unsigned NOT NULL,`poll_option_id` bigint unsigned NOT NULL,`user_id` bigint unsigned DEFAULT NULL,`session_key` varchar(64) DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `poll_votes_poll_id_user_id_unique` (`poll_id`,`user_id`),CONSTRAINT `poll_votes_poll_id_foreign` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,CONSTRAINT `poll_votes_poll_option_id_foreign` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `widgets` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`type` varchar(50) NOT NULL,`title` varchar(150) NOT NULL,`where_to_display` varchar(80) NOT NULL,`display_order` smallint unsigned NOT NULL DEFAULT '0',`is_active` tinyint(1) NOT NULL DEFAULT '1',`config` json DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `roles` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(80) NOT NULL,`name_ne` varchar(100) DEFAULT NULL,`slug` varchar(80) NOT NULL,`badge_label` varchar(80) DEFAULT NULL,`badge_color` varchar(30) NOT NULL DEFAULT 'gray',`badge_icon` varchar(30) NOT NULL DEFAULT 'user',`permissions` json DEFAULT NULL,`is_default` tinyint(1) NOT NULL DEFAULT '0',`is_system` tinyint(1) NOT NULL DEFAULT '0',`ai_credits` int unsigned NOT NULL DEFAULT '0',`sort_order` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `roles_slug_unique` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `navigation_items` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`label` varchar(255) NOT NULL,`url` varchar(255) DEFAULT NULL,`type` varchar(255) NOT NULL DEFAULT 'custom',`target_id` bigint unsigned DEFAULT NULL,`language` varchar(10) NOT NULL DEFAULT 'en',`sort_order` int NOT NULL DEFAULT '0',`is_active` tinyint(1) NOT NULL DEFAULT '1',`parent_id` bigint unsigned DEFAULT NULL,`open_in` varchar(10) NOT NULL DEFAULT '_self',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `pages` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`title` varchar(255) NOT NULL,`slug` varchar(255) NOT NULL,`content` longtext,`status` varchar(20) NOT NULL DEFAULT 'active',`page_type` varchar(30) NOT NULL DEFAULT 'custom',`menu_position` varchar(30) DEFAULT NULL,`language` varchar(10) NOT NULL DEFAULT 'en',`meta_title` varchar(255) DEFAULT NULL,`meta_description` varchar(255) DEFAULT NULL,`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `pages_slug_unique` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `badges` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`name_ne` varchar(255) DEFAULT NULL,`color` varchar(20) NOT NULL DEFAULT '#6366f1',`icon` varchar(255) DEFAULT NULL,`is_active` tinyint(1) NOT NULL DEFAULT '1',`sort_order` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `rss_feeds` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`url` varchar(255) NOT NULL,`language` varchar(10) NOT NULL DEFAULT 'en',`category_id` bigint unsigned DEFAULT NULL,`post_count` int NOT NULL DEFAULT '1',`auto_update` tinyint(1) NOT NULL DEFAULT '1',`show_read_more` tinyint(1) NOT NULL DEFAULT '1',`add_as_draft` tinyint(1) NOT NULL DEFAULT '0',`generate_keywords` tinyint(1) NOT NULL DEFAULT '0',`read_more_text` varchar(255) NOT NULL DEFAULT 'Read More',`default_image` varchar(255) DEFAULT NULL,`images_source` varchar(30) NOT NULL DEFAULT 'original',`imported_count` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `languages` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`name` varchar(255) NOT NULL,`short_form` varchar(10) NOT NULL,`code` varchar(20) NOT NULL,`editor_language` varchar(30) DEFAULT NULL,`direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',`is_active` tinyint(1) NOT NULL DEFAULT '1',`sort_order` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `questions` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`user_id` bigint unsigned NOT NULL,`title` varchar(255) NOT NULL,`slug` varchar(255) NOT NULL,`content` longtext,`category_id` bigint unsigned DEFAULT NULL,`featured_image` varchar(255) DEFAULT NULL,`is_poll` tinyint(1) NOT NULL DEFAULT '0',`is_anonymous` tinyint(1) NOT NULL DEFAULT '0',`is_private` tinyint(1) NOT NULL DEFAULT '0',`notify_email` tinyint(1) NOT NULL DEFAULT '1',`status` enum('open','closed','pending') NOT NULL DEFAULT 'open',`views_count` int unsigned NOT NULL DEFAULT '0',`answers_count` int unsigned NOT NULL DEFAULT '0',`best_answer_id` bigint unsigned DEFAULT NULL,`votes` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `questions_slug_unique` (`slug`),CONSTRAINT `questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `answers` (`id` bigint unsigned NOT NULL AUTO_INCREMENT,`question_id` bigint unsigned NOT NULL,`user_id` bigint unsigned NOT NULL,`content` longtext NOT NULL,`is_best` tinyint(1) NOT NULL DEFAULT '0',`is_anonymous` tinyint(1) NOT NULL DEFAULT '0',`votes` int NOT NULL DEFAULT '0',`created_at` timestamp NULL DEFAULT NULL,`updated_at` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),CONSTRAINT `answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,CONSTRAINT `answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS `question_tag` (`question_id` bigint unsigned NOT NULL,`tag_id` bigint unsigned NOT NULL,PRIMARY KEY (`question_id`,`tag_id`),CONSTRAINT `question_tag_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,CONSTRAINT `question_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        $tableCount = 0;
        foreach ($tables as $sql) {
            try { $pdo->exec($sql); $tableCount++; }
            catch (Exception $e) { $errors[] = 'Table error: '.$e->getMessage(); }
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $done[] = "✓ $tableCount tables created";

        // 3. Record migrations
        try {
            $pdo->exec("DELETE FROM migrations");
            $stmt = $pdo->prepare("INSERT INTO migrations (migration,batch) VALUES (?,1)");
            foreach (['2026_08_16_000001_create_prompts_table','2026_08_16_000002_create_core_tables','2026_08_16_000003_create_social_tables','2026_08_16_084709_create_comments_table','2026_08_16_084711_create_bookmarks_table','2026_08_16_084712_create_notifications_table','2026_08_16_131408_create_contact_messages_table','2026_08_16_131410_create_newsletter_subscribers_table','2026_08_16_132821_add_scheduled_at_to_posts_table','2026_08_16_132822_create_ad_zones_table','2026_08_16_132824_create_polls_table','2026_08_16_140000_add_profile_fields_to_users_table','2026_08_16_140001_create_widgets_table','2026_08_16_140002_add_ga_to_settings','2026_08_16_142731_create_roles_table','2026_08_16_143316_add_extra_fields_to_users_table','2026_08_16_150000_add_social_profile_fields_to_users','2026_08_16_160000_add_flags_to_categories_table','2026_08_16_170000_add_event_fields_to_posts_table','2026_08_16_180000_add_article_fields_to_posts_table','2026_08_16_190000_create_navigation_items_table','2026_08_16_190001_create_pages_table','2026_08_16_200000_create_badges_table','2026_08_16_210000_create_rss_feeds_table','2026_08_16_210001_create_languages_table','2026_08_16_230000_create_questions_table','2026_09_06_184954_add_votes_to_questions_table'] as $m) {
                $stmt->execute([$m]);
            }
            $done[] = '✓ Migration records written';
        } catch (Exception $e) { $errors[] = 'Migration records: '.$e->getMessage(); }

        // 4. Create admin user
        if (!empty($admin_email) && !empty($admin_pass)) {
            try {
                $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
                $now = date('Y-m-d H:i:s');
                $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
                $uname = 'admin';
                $exists = (int)$pdo->prepare("SELECT COUNT(*) FROM users WHERE email=?")->execute([$admin_email]) ? $pdo->query("SELECT COUNT(*) FROM users WHERE email='".addslashes($admin_email)."'")->fetchColumn() : 0;
                if ($exists) {
                    $pdo->prepare("UPDATE users SET password=?,role='admin',email_verified_at=? WHERE email=?")->execute([$hash,$now,$admin_email]);
                    $done[] = '✓ Admin password updated for '.$admin_email;
                } else {
                    $pdo->prepare("INSERT INTO users (uuid,name,username,email,password,role,email_verified_at,created_at,updated_at) VALUES (?,?,?,?,?,'admin',?,?,?)")->execute([$uuid,'Admin',$uname,$admin_email,$hash,$now,$now,$now]);
                    $done[] = '✓ Admin user created: '.$admin_email;
                }
            } catch (Exception $e) { $errors[] = 'Admin user: '.$e->getMessage(); }
        }

        // 5. Write .env
        $key = 'base64:'.base64_encode(random_bytes(32));
        $env = "APP_NAME=\"$app_name\"\nAPP_ENV=production\nAPP_KEY=$key\nAPP_DEBUG=false\nAPP_TIMEZONE=Asia/Kathmandu\nAPP_URL=$app_url\n\nLOG_CHANNEL=stack\nLOG_LEVEL=error\n\nDB_CONNECTION=mysql\nDB_HOST=$db_host\nDB_PORT=$db_port\nDB_DATABASE=$db_name\nDB_USERNAME=$db_user\nDB_PASSWORD=$db_pass\n\nCACHE_STORE=file\nQUEUE_CONNECTION=sync\nSESSION_DRIVER=file\nSESSION_LIFETIME=120\n\nFILESYSTEM_DISK=local\n\nMAIL_MAILER=smtp\nMAIL_HOST=localhost\nMAIL_PORT=587\nMAIL_FROM_ADDRESS=noreply@".parse_url($app_url,PHP_URL_HOST)."\nMAIL_FROM_NAME=\"\${APP_NAME}\"\n";
        if (file_put_contents($APP_ROOT.'/.env', $env)) {
            $done[] = '✓ .env file written (APP_KEY set)';
        } else {
            $errors[] = '.env write failed — check folder permissions on '.$APP_ROOT;
        }

        // 6. Clear caches
        @unlink($APP_ROOT.'/bootstrap/cache/config.php');
        @unlink($APP_ROOT.'/bootstrap/cache/routes-v7.php');
        @unlink($APP_ROOT.'/bootstrap/cache/events.php');
        $done[] = '✓ Cache cleared';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>nkhoj Setup</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px}
.card{background:#1e293b;border:1px solid #334155;border-radius:16px;width:100%;max-width:560px;overflow:hidden}
.card-header{padding:24px;border-bottom:1px solid #334155}
h1{font-size:20px;font-weight:700;color:#fff}
p{color:#94a3b8;font-size:14px;margin-top:4px}
.body{padding:24px}
label{display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;margin-top:16px}
label:first-child{margin-top:0}
input{width:100%;background:#0f172a;border:1px solid #334155;border-radius:8px;padding:10px 12px;font-size:14px;color:#fff}
input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.3)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sep{border:none;border-top:1px solid #334155;margin:20px 0}
button{width:100%;margin-top:20px;padding:12px;background:#6366f1;color:#fff;font-size:14px;font-weight:700;border:none;border-radius:8px;cursor:pointer}
button:hover{background:#4f46e5}
.ok{background:#052e16;border:1px solid #166534;border-radius:8px;padding:10px 14px;margin-bottom:8px;font-size:13px;color:#4ade80}
.err{background:#1c0a0a;border:1px solid #7f1d1d;border-radius:8px;padding:10px 14px;margin-bottom:8px;font-size:13px;color:#f87171}
.results{margin-bottom:20px}
.success-box{background:#052e16;border:1px solid #166534;border-radius:12px;padding:20px;margin-bottom:20px;text-align:center}
.success-box h2{color:#4ade80;font-size:18px;margin-bottom:8px}
.success-box a{color:#818cf8;font-size:14px}
.warn{background:#1c1000;border:1px solid #92400e;border-radius:8px;padding:10px 14px;margin-top:12px;font-size:12px;color:#fbbf24}
</style>
</head>
<body>
<div class="card">
<div class="card-header">
  <h1>nkhoj Quick Setup</h1>
  <p>Fills .env, creates all database tables, creates admin account</p>
</div>
<div class="body">

<?php if (!empty($done) || !empty($errors)): ?>
<div class="results">
  <?php foreach($done as $m): ?><div class="ok"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
  <?php foreach($errors as $m): ?><div class="err">✕ <?= htmlspecialchars($m) ?></div><?php endforeach; ?>
</div>

<?php if (empty($errors) && !empty($done)): ?>
<div class="success-box">
  <h2>Setup Complete!</h2>
  <p style="color:#86efac;font-size:14px;margin-bottom:12px">All tables created. Admin account ready.</p>
  <a href="<?= htmlspecialchars($_POST['app_url']??'/') ?>/login">→ Go to Login</a>&nbsp;&nbsp;
  <a href="<?= htmlspecialchars($_POST['app_url']??'/') ?>/admin">→ Go to Admin</a>
</div>
<div class="warn">⚠ Delete <strong>public/setup.php</strong> from your server now!</div>
<?php else: ?>
<?php endif; ?>
<?php endif; ?>

<?php if (empty($done) || !empty($errors)): ?>
<form method="POST">
  <p style="font-size:13px;color:#64748b;margin-bottom:16px">All fields in one form — no steps, no sessions.</p>

  <div style="background:#0f172a;border:1px solid #1e3a5f;border-radius:8px;padding:16px;margin-bottom:4px">
    <div style="font-size:11px;font-weight:700;color:#38bdf8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Database (from cPanel MySQL Databases)</div>
    <div class="grid">
      <div><label>DB Host</label><input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host']??'localhost') ?>"></div>
      <div><label>Port</label><input type="text" name="db_port" value="<?= htmlspecialchars($_POST['db_port']??'3306') ?>"></div>
    </div>
    <label>Database Name</label><input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name']??'alphaome_dewnkhoj') ?>" placeholder="alphaome_dewnkhoj">
    <div class="grid">
      <div><label>Username</label><input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user']??'') ?>" placeholder="alphaome_xxx"></div>
      <div><label>Password</label><input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass']??'') ?>"></div>
    </div>
  </div>

  <hr class="sep">

  <div style="background:#0f172a;border:1px solid #1e3a5f;border-radius:8px;padding:16px;margin-bottom:4px">
    <div style="font-size:11px;font-weight:700;color:#38bdf8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px">Site & Admin</div>
    <label>Site URL</label><input type="text" name="app_url" value="<?= htmlspecialchars($_POST['app_url']??'https://dewanlabung.com.np') ?>">
    <label>Site Name</label><input type="text" name="app_name" value="<?= htmlspecialchars($_POST['app_name']??'DEWAN') ?>">
    <label>Admin Email</label><input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email']??'hlespn@gmail.com') ?>">
    <label>Admin Password (min 8 chars)</label><input type="password" name="admin_pass" value="">
  </div>

  <button type="submit">Run Setup →</button>
</form>
<?php endif; ?>

</div>
</div>
</body>
</html>
