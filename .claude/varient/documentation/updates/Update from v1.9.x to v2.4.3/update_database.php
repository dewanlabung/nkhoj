<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["btnUpdate"])) {
    $data = [
        'db_host' => $_POST['db_host'],
        'db_user' => $_POST['db_user'],
        'db_password' => $_POST['db_password'],
        'db_name' => $_POST['db_name']
    ];
    try {
        $connection = new mysqli($data['db_host'], $data['db_user'], $data['db_password'], $data['db_name']);
        if ($connection->connect_error) {
            $error = "Failed to connect to database, please check your database credentials!";
        } else {
            $connection->query("SET CHARACTER SET utf8mb4");
            $connection->query("SET NAMES utf8mb4");

            update($connection);
            $success = 'The update has been successfully completed!<br> Please close this tab and delete the "update_database.php" file.';
            $connection->close();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function runQuery($sql)
{
    global $connection;
    return mysqli_query($connection, $sql);
}

if (isset($_POST["btn_submit"])) {
    update($connection);
    $success = 'The update has been successfully completed! Please delete the "update_database.php" file.';
}

function update()
{
    updateFrom19To20();
    sleep(1);
    updateFrom20To21();
    sleep(1);
    updateFrom21To22();
    sleep(1);
    updateFrom22To23();
    sleep(1);
    updateFrom23To24();
}

function updateFrom19To20()
{
    $tblSession = "CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id` varchar(128) NOT null,
    `ip_address` varchar(45) NOT null,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
    `data` blob NOT null,
    KEY `ci_sessions_timestamp` (`timestamp`));";

    $tblFonts = "CREATE TABLE `fonts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `font_name` varchar(255) DEFAULT NULL,
    `font_key` varchar(255) DEFAULT NULL,
    `font_url` varchar(2000) DEFAULT NULL,
    `font_family` varchar(500) DEFAULT NULL,
    `font_source` varchar(50) DEFAULT 'google',
    `has_local_file` tinyint(1) DEFAULT 0,
    `is_default` tinyint(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $sqlFonts = "INSERT INTO `fonts` (`id`, `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
(1, 'Arial', 'arial', NULL, 'font-family: Arial, Helvetica, sans-serif', 'local', 0, 1),
(2, 'Arvo', 'arvo', '<link href=\"https://fonts.googleapis.com/css?family=Arvo:400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Arvo\", Helvetica, sans-serif', 'google', 0, 0),
(3, 'Averia Libre', 'averia-libre', '<link href=\"https://fonts.googleapis.com/css?family=Averia+Libre:300,400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Averia Libre\", Helvetica, sans-serif', 'google', 0, 0),
(4, 'Bitter', 'bitter', '<link href=\"https://fonts.googleapis.com/css?family=Bitter:400,400i,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Bitter\", Helvetica, sans-serif', 'google', 0, 0),
(5, 'Cabin', 'cabin', '<link href=\"https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Cabin\", Helvetica, sans-serif', 'google', 0, 0),
(6, 'Cherry Swash', 'cherry-swash', '<link href=\"https://fonts.googleapis.com/css?family=Cherry+Swash:400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Cherry Swash\", Helvetica, sans-serif', 'google', 0, 0),
(7, 'Encode Sans', 'encode-sans', '<link href=\"https://fonts.googleapis.com/css?family=Encode+Sans:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Encode Sans\", Helvetica, sans-serif', 'google', 0, 0),
(8, 'Helvetica', 'helvetica', NULL, 'font-family: Helvetica, sans-serif', 'local', 0, 1),
(9, 'Hind', 'hind', '<link href=\"https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Hind\", Helvetica, sans-serif', 'google', 0, 0),
(10, 'Josefin Sans', 'josefin-sans', '<link href=\"https://fonts.googleapis.com/css?family=Josefin+Sans:300,400,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Josefin Sans\", Helvetica, sans-serif', 'google', 0, 0),
(11, 'Kalam', 'kalam', '<link href=\"https://fonts.googleapis.com/css?family=Kalam:300,400,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Kalam\", Helvetica, sans-serif', 'google', 0, 0),
(12, 'Khula', 'khula', '<link href=\"https://fonts.googleapis.com/css?family=Khula:300,400,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Khula\", Helvetica, sans-serif', 'google', 0, 0),
(13, 'Lato', 'lato', '<link href=\"https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Lato\", Helvetica, sans-serif', 'google', 0, 0),
(14, 'Lora', 'lora', '<link href=\"https://fonts.googleapis.com/css?family=Lora:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Lora\", Helvetica, sans-serif', 'google', 0, 0),
(15, 'Merriweather', 'merriweather', '<link href=\"https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Merriweather\", Helvetica, sans-serif', 'google', 0, 0),
(16, 'Montserrat', 'montserrat', '<link href=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Montserrat\", Helvetica, sans-serif', 'google', 0, 0),
(17, 'Mukta', 'mukta', '<link href=\"https://fonts.googleapis.com/css?family=Mukta:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Mukta\", Helvetica, sans-serif', 'google', 0, 0),
(18, 'Nunito', 'nunito', '<link href=\"https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Nunito\", Helvetica, sans-serif', 'google', 0, 0),
(19, 'Open Sans', 'open-sans', '<link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap\" rel=\"stylesheet\">', 'font-family: \"Open Sans\", Helvetica, sans-serif', 'google', 1, 0),
(20, 'Oswald', 'oswald', '<link href=\"https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Oswald\", Helvetica, sans-serif', 'google', 0, 0),
(21, 'Oxygen', 'oxygen', '<link href=\"https://fonts.googleapis.com/css?family=Oxygen:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Oxygen\", Helvetica, sans-serif', 'google', 0, 0),
(22, 'Poppins', 'poppins', '<link href=\"https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Poppins\", Helvetica, sans-serif', 'google', 0, 0),
(23, 'PT Sans', 'pt-sans', '<link href=\"https://fonts.googleapis.com/css?family=PT+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"PT Sans\", Helvetica, sans-serif', 'google', 0, 0),
(24, 'Raleway', 'raleway', '<link href=\"https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Raleway\", Helvetica, sans-serif', 'google', 0, 0),
(25, 'Roboto', 'roboto', '<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Roboto\", Helvetica, sans-serif', 'google', 1, 0),
(26, 'Roboto Condensed', 'roboto-condensed', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Condensed:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Condensed\", Helvetica, sans-serif', 'google', 0, 0),
(27, 'Roboto Slab', 'roboto-slab', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Slab\", Helvetica, sans-serif', 'google', 0, 0),
(28, 'Rokkitt', 'rokkitt', '<link href=\"https://fonts.googleapis.com/css?family=Rokkitt:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Rokkitt\", Helvetica, sans-serif', 'google', 0, 0),
(29, 'Source Sans Pro', 'source-sans-pro', '<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Source Sans Pro\", Helvetica, sans-serif', 'google', 0, 0),
(30, 'Titillium Web', 'titillium-web', '<link href=\"https://fonts.googleapis.com/css?family=Titillium+Web:300,400,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Titillium Web\", Helvetica, sans-serif', 'google', 0, 0),
(31, 'Ubuntu', 'ubuntu', '<link href=\"https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Ubuntu\", Helvetica, sans-serif', 'google', 0, 0),
(32, 'Verdana', 'verdana', NULL, 'font-family: Verdana, Helvetica, sans-serif', 'local', 0, 1);";


    runQuery("DROP TABLE ci_sessions;");
    runQuery("DROP TABLE fonts;");
    runQuery($tblSession);
    runQuery($tblFonts);
    runQuery($sqlFonts);
    sleep(1);
    runQuery("ALTER TABLE general_settings ADD COLUMN `theme_mode` VARCHAR(25) DEFAULT 'light';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_list_style` VARCHAR(50) DEFAULT 'vertical';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `site_color` VARCHAR(50) DEFAULT '#1abc9c';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `site_block_color` VARCHAR(50) DEFAULT '#161616';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `logo` VARCHAR(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `logo_footer` VARCHAR(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `logo_email` VARCHAR(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `favicon` VARCHAR(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `rss_content_type` VARCHAR(50) DEFAULT 'summary';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_url_structure` VARCHAR(50) DEFAULT 'slug';");
    runQuery("ALTER TABLE general_settings CHANGE custom_css_codes custom_header_codes mediumtext;");
    runQuery("ALTER TABLE general_settings CHANGE custom_javascript_codes custom_footer_codes mediumtext;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `vr_key`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `purchase_code`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `recaptcha_lang`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `aws_base_url`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `allowed_file_extensions`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `cookie_prefix`;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `allowed_file_extensions` VARCHAR(500) DEFAULT 'jpg,jpeg,png,gif,svg,csv,doc,docx,pdf,ppt,psd,mp4,mp3,zip';");
    runQuery("UPDATE general_settings SET `version` = '2.0' WHERE id = 1;");

    $result = runQuery("SELECT * FROM visual_settings WHERE id = 1");
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['post_list_style'])) {
            runQuery("UPDATE general_settings SET `post_list_style` = '" . $row['post_list_style'] . "' WHERE id = 1;");
        }
        if (!empty($row['site_color'])) {
            runQuery("UPDATE general_settings SET `site_color` = '" . $row['site_color'] . "' WHERE id = 1;");
        }
        if (!empty($row['site_block_color'])) {
            runQuery("UPDATE general_settings SET `site_block_color` = '" . $row['site_block_color'] . "' WHERE id = 1;");
        }
        if (!empty($row['logo'])) {
            runQuery("UPDATE general_settings SET `logo` = '" . $row['logo'] . "' WHERE id = 1;");
        }
        if (!empty($row['logo_footer'])) {
            runQuery("UPDATE general_settings SET `logo_footer` = '" . $row['logo_footer'] . "' WHERE id = 1;");
        }
        if (!empty($row['logo_email'])) {
            runQuery("UPDATE general_settings SET `logo_email` = '" . $row['logo_email'] . "' WHERE id = 1;");
        }
        if (!empty($row['favicon'])) {
            runQuery("UPDATE general_settings SET `favicon` = '" . $row['favicon'] . "' WHERE id = 1;");
        }
    }
    runQuery("DROP TABLE visual_settings;");
    sleep(1);
    //add new translations
    $p = array();
    $p["confirm_password"] = "Confirm Password";
    $p["custom_footer_codes"] = "Custom Footer Codes";
    $p["custom_footer_codes_exp"] = "These codes will be added to the footer of the site.";
    $p["custom_header_codes"] = "Custom Header Codes";
    $p["custom_header_codes_exp"] = "These codes will be added to the header of the site.";
    $p["distribute_only_post_summary"] = "Distribute only Post Summary";
    $p["distribute_post_content"] = "Distribute Post Content";
    $p["font_source"] = "Font Source";
    $p["font_warning"] = "Default fonts (Open Sans and Roboto) have native font files. If you do not want to use Google fonts for these fonts, you can edit these fonts and choose local option.";
    $p["leave_your_comment"] = "Leave your comment...";
    $p["local"] = "Local";
    $p["old_password"] = "Old Password";
    $p["password"] = "Password";
    $p["post_url_structure"] = "Post URL Structure";
    $p["post_url_structure_exp"] = "Changing the URL structure will not affect old records.";
    $p["post_url_structure_slug"] = "Use Slug in URLs";
    $p["post_url_structur_id"] = "Use ID Numbers in URLs";
    $p["region_code"] = "Region Code";
    $p["rss_content"] = "RSS Content";
    $p["search_in_post_content"] = "Search in Post Content";
    $p["social_login_settings"] = "Social Login Settings";
    $p["most_viewed_posts"] = "Most Viewed Posts";
    addTranslations($p);
}

function updateFrom20To21()
{
    runQuery("DROP TABLE post_pageviews_week;");
    runQuery("DROP TABLE ad_spaces;");
    $tblThemes = "CREATE TABLE `themes` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `theme` varchar(255) DEFAULT NULL,
      `theme_folder` varchar(255) NOT NULL,
      `theme_name` varchar(255) DEFAULT NULL,
      `theme_color` varchar(100) DEFAULT NULL,
      `block_color` varchar(100) DEFAULT NULL,
      `mega_menu_color` varchar(255) DEFAULT NULL,
      `is_active` tinyint(1) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tblAdSpaces = "CREATE TABLE `ad_spaces` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `lang_id` int(11) DEFAULT 1,
      `ad_space` text DEFAULT NULL,
      `ad_code_desktop` text DEFAULT NULL,
      `desktop_width` int(11) DEFAULT NULL,
      `desktop_height` int(11) DEFAULT NULL,
      `ad_code_mobile` text DEFAULT NULL,
      `mobile_width` int(11) DEFAULT NULL,
      `mobile_height` int(11) DEFAULT NULL,
      `display_category_id` int(11) DEFAULT NULL,
      `paragraph_number` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    runQuery($tblThemes);
    runQuery($tblAdSpaces);
    runQuery("ALTER TABLE general_settings DROP COLUMN `post_list_style`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `site_color`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `site_block_color`;");
    runQuery("ALTER TABLE general_settings CHANGE mail_library mail_service varchar(100) DEFAULT 'swift';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_api_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_secret_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_email_address` varchar(255);");
    runQuery("ALTER TABLE routes DROP COLUMN `visual_settings`;");
    runQuery("ALTER TABLE users ADD COLUMN `cover_image` varchar(255);");
    runQuery("ALTER TABLE widgets ADD COLUMN `display_category_id` INT;");

    $sqlThemes = "INSERT INTO `themes` (`id`, `theme`, `theme_folder`, `theme_name`, `theme_color`, `block_color`, `mega_menu_color`, `is_active`) VALUES
(1, 'classic', 'classic', 'Classic', '#19bc9c', '#161616', NULL, 0),
(2, 'magazine', 'magazine', 'Magazine', '#2d65fe', '#161616', '#f9f9f9', 1),
(3, 'news', 'magazine', 'News', '#0f88f1', '#101010', '#1e1e1e', 0);";
    runQuery($sqlThemes);
    $sqlFonts="INSERT INTO `fonts` (`font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
('Inter', 'inter', '<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap\" rel=\"stylesheet\">', 'font-family: \"Inter\", sans-serif;', 'local', 1, 0),
('PT Serif', 'pt-serif', '<link href=\"https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap\" rel=\"stylesheet\">', 'font-family: \"PT Serif\", serif;', 'local', 1, 0)";
    runQuery($sqlFonts);
    runQuery("DELETE FROM widgets WHERE `type`='random-slider-posts';");
    runQuery("UPDATE general_settings SET `version` = '2.1' WHERE id = 1;");

    sleep(1);
    //add new translations
    $p = array();
    $p["category_select_widget"] = "Select the widgets you want to show in the sidebar";
    $p["where_to_display"] = "Where To Display";
    $p["change"] = "Change";
    $p["theme_settings"] = "Theme Settings";
    $p["color_code"] = "Color Code";
    $p["mail_service"] = "Mail Service";
    $p["api_key"] = "API Key";
    $p["mailjet_email_address"] = "Mailjet Email Address";
    $p["mailjet_email_address_exp"] = "The address you created your Mailjet account with";
    $p["warning_edit_profile_image"] = "Click on the save changes button after selecting your image";
    $p["top_headlines"] = "Top Headlines";
    $p["ad_space_header"] = "Header";
    $p["ad_space_index_top"] = "Index (Top)";
    $p["ad_space_index_bottom"] = "Index (Bottom)";
    $p["ad_space_post_top"] = "Post Details (Top)";
    $p["ad_space_post_bottom"] = "Post Details (Bottom)";
    $p["ad_space_posts_top"] = "Posts (Top)";
    $p["ad_space_posts_bottom"] = "Posts (Bottom)";
    $p["ad_space_posts_exp"] = "This ad will be displayed on Posts, Category, Profile, Tag, Search and Profile pages";
    $p["logo_footer"] = "Logo Footer";
    $p["ad_space_in_article"] = "In-Article";
    $p["paragraph"] = "Paragraph";
    $p["ad_space_paragraph_exp"] = "The ad will be displayed after the paragraph number you selected";
    $p["banner_desktop"] = "Desktop Banner";
    $p["banner_mobile"] = "Mobile Banner";
    $p["banner_desktop_exp"] = "This ad will be displayed on screens larger than 992px";
    $p["banner_mobile_exp"] = "This ad will be displayed on screens smaller than 992px";
    $p["sidebar"] = "Sidebar";
    $p["ad_size"] = "Ad Size";
    $p["width"] = "Width";
    $p["height"] = "Height";
    $p["create_ad_exp"] = "If you do not have an ad code, you can create an ad code by selecting an image and adding an URL";
    $p["mega_menu_color"] = "Mega Menu Color";
    addTranslations($p);
    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='category_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='category_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='font_warning';");
    runQuery("DELETE FROM language_translations WHERE `label`='header_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='index_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='index_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='mail_library';");
    runQuery("DELETE FROM language_translations WHERE `label`='posts_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='posts_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='post_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='post_list_style';");
    runQuery("DELETE FROM language_translations WHERE `label`='post_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='reading_list_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='reading_list_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='search_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='search_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='sidebar_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='sidebar_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='tag_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='tag_top_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='visual_settings';");
}

function updateFrom21To22()
{
    $tblPostPollVotes = "CREATE TABLE `post_poll_votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` int(11) DEFAULT NULL,
    `question_id` int(11) DEFAULT NULL,
    `answer_id` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    runQuery($tblPostPollVotes);
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_poll` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `image_file_format` varchar(30) DEFAULT 'JPG'");
    runQuery("ALTER TABLE general_settings ADD COLUMN `google_news` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE posts ADD COLUMN `is_poll_public` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE quiz_answers ADD COLUMN `total_votes` INT DEFAULT 0");
    runQuery("ALTER TABLE settings ADD COLUMN `tiktok_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `tiktok_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `personal_website_url` varchar(500)");
    runQuery("UPDATE general_settings SET `version` = '2.2' WHERE id = 1;");
    sleep(1);
    //update role names
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:5:\"Admin\";}}' WHERE `role` = 'admin';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:9:\"Moderator\";}}' WHERE `role` = 'moderator';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Author\";}}' WHERE `role` = 'author';");
    runQuery("UPDATE roles_permissions SET `role_name` = 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:4:\"User\";}}' WHERE `role` = 'user';");
    //add new translations
    $p = array();
    $p["ad_space_index_top"] = "Index (Top)";
    $p["ad_space_index_bottom"] = "Index (Bottom)";
    $p["ad_space_post_top"] = "Post Details (Top)";
    $p["ad_space_post_bottom"] = "Post Details (Bottom)";
    $p["ad_space_posts_top"] = "Posts (Top)";
    $p["ad_space_posts_bottom"] = "Posts (Bottom)";
    $p["ad_space_in_article"] = "In-Article";
    $p["image_file_format"] = "Image File Format";
    $p["personal_website_url"] = "Personal Website URL";
    $p["poll_exp"] = "Get user opinions about something";
    $p["total_votes"] = "Total Votes";
    $p["google_news"] = "Google News";
    $p["generate_feed_url"] = "Generate Feed URL";
    $p["limit"] = "Limit";
    $p["google_news_exp"] = "According to Google News rules, there can be a maximum of 1000 publications in an XML file. Therefore, it is not recommended to increase this limit.";
    $p["google_news_cache_exp"] = "This system uses cache system. So the records in your XML file will be automatically updated every 15 minutes.";
    $p["accept_cookies"] = "Accept Cookies";
    addTranslations($p);
    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='add_subcategory';");
    runQuery("DELETE FROM language_translations WHERE `label`='subcategories';");
    //add indexes
    runQuery("ALTER TABLE audios ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE comments ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE files ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE followers ADD INDEX idx_following_id (following_id);");
    runQuery("ALTER TABLE followers ADD INDEX idx_follower_id (follower_id);");
    runQuery("ALTER TABLE images ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE payouts ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE poll_votes ADD INDEX idx_poll_id (poll_id);");
    runQuery("ALTER TABLE poll_votes ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE post_audios ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_audios ADD INDEX idx_audio_id (audio_id);");
    runQuery("ALTER TABLE post_files ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_files ADD INDEX idx_file_id (file_id);");
    runQuery("ALTER TABLE post_gallery_items ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_images ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_pageviews_month ADD INDEX idx_post_user_id (post_user_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_question_id (question_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE post_poll_votes ADD INDEX idx_answer_id (answer_id);");
    runQuery("ALTER TABLE post_sorted_list_items ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE quiz_answers ADD INDEX idx_question_id (question_id);");
    runQuery("ALTER TABLE quiz_images ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE quiz_questions ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE quiz_results ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reactions ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reading_lists ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE reading_lists ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE videos ADD INDEX idx_user_id (user_id);");
}

function updateFrom22To23()
{
    runQuery("ALTER TABLE categories DROP COLUMN `show_at_homepage`;");
    runQuery("ALTER TABLE categories ADD COLUMN `show_on_homepage` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_table_of_contents` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `post_format_recipe` TINYINT(1) DEFAULT 1");
    runQuery("ALTER TABLE general_settings ADD COLUMN `delete_images_with_post` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE general_settings ADD COLUMN `sticky_sidebar` TINYINT(1) DEFAULT 0");
    runQuery("ALTER TABLE posts ADD COLUMN `link_list_style` varchar(255)");
    runQuery("ALTER TABLE posts ADD COLUMN `recipe_info` TEXT");
    runQuery("ALTER TABLE posts ADD COLUMN `post_data` TEXT");
    runQuery("ALTER TABLE post_sorted_list_items ADD COLUMN `parent_link_num` INT DEFAULT 0");
    runQuery("ALTER TABLE settings ADD COLUMN `whatsapp_url` varchar(500)");
    runQuery("ALTER TABLE settings ADD COLUMN `discord_url` varchar(500)");
    runQuery("ALTER TABLE settings ADD COLUMN `twitch_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `whatsapp_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `discord_url` varchar(500)");
    runQuery("ALTER TABLE users ADD COLUMN `twitch_url` varchar(500)");
    runQuery("UPDATE general_settings SET `version` = '2.3' WHERE id = 1;");

    //add new translations
    $p = array();
    $p["progressive_web_app"] = "Progressive Web App (PWA)";
    $p["table_of_contents"] = "Table of Contents";
    $p["table_of_contents_exp"] = "List of links based on the headings";
    $p["add_table_of_contents"] = "Add Table of Contents";
    $p["table_of_contents_items"] = "Table Of Contents Items";
    $p["update_table_of_contents"] = "Update Table of Contents";
    $p["link_list_style"] = "Link List Style";
    $p["number"] = "Number";
    $p["circle"] = "Circle";
    $p["link_type"] = "Link Type";
    $p["level_1"] = "Level 1";
    $p["level_2"] = "Level 2";
    $p["level_3"] = "Level 3";
    $p["recipe"] = "Recipe";
    $p["recipe_exp"] = "A list of ingredients and directions for cooking";
    $p["show_list_style_post_text"] = "Show List Style in Post Text";
    $p["add_recipe"] = "Add Recipe";
    $p["ingredients"] = "Ingredients";
    $p["add_new"] = "Add New";
    $p["nutritional_information"] = "Nutritional Information ";
    $p["recipe_video"] = "Recipe video";
    $p["value"] = "Value";
    $p["ingredient"] = "Ingredient";
    $p["prep_time"] = "Prep Time";
    $p["cook_time"] = "Cook Time";
    $p["difficulty"] = "Difficulty";
    $p["easy"] = "Easy";
    $p["intermediate"] = "Intermediate";
    $p["advanced"] = "Advanced";
    $p["directions"] = "Directions";
    $p["serving"] = "Serving";
    $p["update_recipe"] = "Update Recipe";
    $p["info_about_recipe"] = "Information About the Recipe";
    $p["minute_short"] = "min";
    $p["delete_images_with_post"] = "Delete Images Along with Post";
    $p["sticky_sidebar"] = "Sticky Sidebar";
    $p["number_short_thousand"] = "k";
    $p["number_short_million"] = "m";
    $p["number_short_billion"] = "b";
    $p["ingredient_ex"] = "Example: 1 tablespoon olive oil";
    $p["nutritional_ex"] = "Example: Protein 34g";
    $p["show_on_homepage"] = "Show on Homepage";
    addTranslations($p);

    runQuery("DELETE FROM language_translations WHERE `label`='show_at_homepage';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_cron_scheduled';");
}

function updateFrom23To24()
{
    global $connection;

    runQuery("TRUNCATE TABLE ci_sessions");
    runQuery("RENAME TABLE tags TO tags1;");
    runQuery("RENAME TABLE quiz_images TO post_item_images;");
    runQuery("RENAME TABLE post_sorted_list_items TO post_list_items;");

    $tblTags = "CREATE TABLE `tags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tag` varchar(255) DEFAULT NULL,
            `tag_slug` varchar(255) DEFAULT NULL,
            `lang_id` int(11) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblPostTags = "CREATE TABLE `post_tags` (
            `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
            `tag_id` int(11) DEFAULT NULL,
            `post_id` int(11) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblPostSelections = "CREATE TABLE `post_selections` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `post_id` int(11) DEFAULT NULL,
            `selection_type` varchar(30) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $tblRoles = "CREATE TABLE `roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `role_name` text DEFAULT NULL,
            `permissions` text DEFAULT NULL,
            `is_default` tinyint(1) DEFAULT 0,
            `is_super_admin` tinyint(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    runQuery($tblTags);
    runQuery($tblPostTags);
    runQuery($tblPostSelections);
    runQuery($tblRoles);

    runQuery("ALTER TABLE categories CHANGE `name_slug` `slug` varchar(255);");
    runQuery("ALTER TABLE categories ADD COLUMN `category_status` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `pwa_logo` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `static_cache_system` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `newsletter_image` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `human_verification` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `ai_writer` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `google_indexing_api` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `payout_methods` TEXT;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `bulk_post_upload_for_authors` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `logo_size` varchar(30);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `routes` TEXT");
    runQuery("ALTER TABLE payouts ADD COLUMN `status` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE posts CHANGE `title_slug` `slug` varchar(500);");
    runQuery("ALTER TABLE posts CHANGE `summary` `summary` TEXT;");

    runQuery("ALTER TABLE post_pageviews_month ADD COLUMN `visit_hash` varchar(255);");
    runQuery("ALTER TABLE settings ADD COLUMN `social_media_data` TEXT;");
    runQuery("ALTER TABLE users ADD COLUMN `social_media_data` TEXT;");
    runQuery("ALTER TABLE users ADD COLUMN `role_id` INT(11) DEFAULT 3;");
    runQuery("ALTER TABLE users ADD COLUMN `payout_methods` TEXT;");
    runQuery("ALTER TABLE post_item_images ADD COLUMN `item_type` varchar(30) DEFAULT 'quiz';");

    //insert roles
    runQuery("INSERT INTO `roles` (`id`, `role_name`, `permissions`, `is_default`, `is_super_admin`) VALUES
    (1, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:11:\"Super Admin\";}}', '', 1, 1),
    (2, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Author\";}}', 'add_post,admin_panel', 1, 0),
    (3, 'a:1:{i:0;a:2:{s:7:\"lang_id\";s:1:\"1\";s:4:\"name\";s:6:\"Member\";}}', '', 1, 0);");

    //insert new font
    runQuery("INSERT INTO `fonts` ( `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
    ('Source Sans 3', 'source-sans-3', NULL, 'font-family: \"Source Sans 3\", Helvetica, sans-serif', 'local', 1, 0);");

    //set settings
    $result = runQuery("SELECT * FROM settings ORDER BY id;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $data = [
                'facebook' => !empty($row['facebook_url']) ? $row['facebook_url'] : '',
                'twitter' => !empty($row['twitter_url']) ? $row['twitter_url'] : '',
                'instagram' => !empty($row['instagram_url']) ? $row['instagram_url'] : '',
                'tiktok' => !empty($row['tiktok_url']) ? $row['tiktok_url'] : '',
                'whatsapp' => !empty($row['whatsapp_url']) ? $row['whatsapp_url'] : '',
                'youtube' => !empty($row['youtube_url']) ? $row['youtube_url'] : '',
                'discord' => !empty($row['discord_url']) ? $row['discord_url'] : '',
                'telegram' => !empty($row['telegram_url']) ? $row['telegram_url'] : '',
                'pinterest' => !empty($row['pinterest_url']) ? $row['pinterest_url'] : '',
                'linkedin' => !empty($row['linkedin_url']) ? $row['linkedin_url'] : '',
                'twitch' => !empty($row['twitch_url']) ? $row['twitch_url'] : '',
                'vk' => !empty($row['vk_url']) ? $row['vk_url'] : '',
            ];
            $socialMediaData = serialize($data);
            $stmt = $connection->prepare("UPDATE settings SET social_media_data = ? WHERE id = ?");
            $stmt->bind_param("si", $socialMediaData, $row['id']);
            $stmt->execute();
        }
    }

    //set payout settings
    $result = runQuery("SELECT * FROM general_settings WHERE id = 1");
    while ($row = mysqli_fetch_array($result)) {
        $payoutMethods = [
            'paypal_status' => !empty($row['payout_paypal_status']) ? 1 : 0,
            'paypal_min_amount' => 50,
            'bitcoin_status' => 0,
            'bitcoin_min_amount' => 50,
            'iban_status' => !empty($row['payout_iban_status']) ? 1 : 0,
            'iban_min_amount' => 50,
            'swift_status' => !empty($row['payout_swift_status']) ? 1 : 0,
            'swift_min_amount' => 100
        ];
        $payoutMethods = serialize($payoutMethods);
        $stmt = $connection->prepare("UPDATE general_settings SET payout_methods = ? WHERE id = 1");
        $stmt->bind_param("s", $payoutMethods);
        $stmt->execute();
    }

    //set users
    $result = runQuery("SELECT * FROM users;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $roleId = 3;
            if ($row['role'] == 'admin') {
                $roleId = 1;
            } elseif ($row['role'] == 'author') {
                $roleId = 2;
            }
            $data = [
                'facebook' => !empty($row['facebook_url']) ? $row['facebook_url'] : '',
                'twitter' => !empty($row['twitter_url']) ? $row['twitter_url'] : '',
                'instagram' => !empty($row['instagram_url']) ? $row['instagram_url'] : '',
                'tiktok' => !empty($row['tiktok_url']) ? $row['tiktok_url'] : '',
                'whatsapp' => !empty($row['whatsapp_url']) ? $row['whatsapp_url'] : '',
                'youtube' => !empty($row['youtube_url']) ? $row['youtube_url'] : '',
                'discord' => !empty($row['discord_url']) ? $row['discord_url'] : '',
                'telegram' => !empty($row['telegram_url']) ? $row['telegram_url'] : '',
                'pinterest' => !empty($row['pinterest_url']) ? $row['pinterest_url'] : '',
                'linkedin' => !empty($row['linkedin_url']) ? $row['linkedin_url'] : '',
                'twitch' => !empty($row['twitch_url']) ? $row['twitch_url'] : '',
                'vk' => !empty($row['vk_url']) ? $row['vk_url'] : '',
                'personal_website_url' => !empty($row['personal_website_url']) ? $row['personal_website_url'] : ''
            ];
            $socialMediaData = serialize($data);
            $stmt = $connection->prepare("UPDATE users SET social_media_data = ?, role_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $socialMediaData, $roleId, $row['id']);
            $stmt->execute();
        }
    }

    //set payout accounts
    $result = runQuery("SELECT * FROM user_payout_accounts;");
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $payout = [
                'paypal_email' => $row['payout_paypal_email'],
                'btc_address' => '',
                'iban_full_name' => $row['iban_full_name'],
                'iban_country' => $row['iban_country'],
                'iban_bank_name' => $row['iban_bank_name'],
                'iban_number' => $row['iban_number'],
                'swift_full_name' => $row['swift_full_name'],
                'swift_address' => $row['swift_address'],
                'swift_state' => $row['swift_state'],
                'swift_city' => $row['swift_city'],
                'swift_postcode' => $row['swift_postcode'],
                'swift_country' => $row['swift_country'],
                'swift_bank_account_holder_name' => $row['swift_bank_account_holder_name'],
                'swift_iban' => $row['swift_iban'],
                'swift_code' => $row['swift_code'],
                'swift_bank_name' => $row['swift_bank_name'],
                'swift_bank_branch_city' => $row['swift_bank_branch_city'],
                'swift_bank_branch_country' => $row['swift_bank_branch_country'],
                'paypal_email' => $row['payout_paypal_email'],
            ];
            $payoutMethods = serialize($payout);

            $stmt = $connection->prepare("UPDATE users SET payout_methods = ? WHERE id = ?");
            $stmt->bind_param("si", $payoutMethods, $row['user_id']);
            $stmt->execute();
        }
    }

    runQuery("ALTER TABLE posts ADD COLUMN `image_id` INT(11) DEFAULT NULL;");
    runQuery("ALTER TABLE posts ADD COLUMN `comment_count` INT(11) DEFAULT 0;");
    runQuery("ALTER TABLE posts ADD INDEX idx_image_big (image_big);");
    runQuery("ALTER TABLE images ADD INDEX idx_image_big (image_big);");
    $query = "SELECT posts.*, 
       (SELECT id FROM images WHERE images.image_big = posts.image_big LIMIT 1) AS img_id,
       (SELECT COUNT(comments.id) FROM comments WHERE comments.post_id = posts.id) AS total_comments
        FROM `posts`";
    $result = runQuery($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imgId = $row['img_id'];
            if (empty($imgId)) {
                $imgId = 0;
            }
            $commentCount = 0;
            if (!empty($row['total_comments'])) {
                $commentCount = $row['total_comments'];
            }

            $updateQuery = "UPDATE posts SET image_id = " . $imgId . ", comment_count = " . $commentCount . "  WHERE id = " . $row['id'];
            runQuery($updateQuery);

            //slider post
            if ($row['is_slider'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'slider');");
            }
            //featured post
            if ($row['is_featured'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'featured');");
            }
            //breaking post
            if ($row['is_breaking'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'breaking');");
            }
            //breaking post
            if ($row['is_recommended'] == 1) {
                runQuery("INSERT INTO post_selections (post_id, selection_type) VALUES(" . $row['id'] . ", 'recommended');");
            }

            if ($row['post_type'] == 'recipe') {
                $title = '';
                $order = 1;
                $stmt = $connection->prepare("INSERT INTO post_list_items (`post_id`, `title`, `content`, `item_order`) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $row['id'], $title, $row['content'], $order);
                $stmt->execute();
            }
        }
    }


    //rearrange tags
    runQuery("ALTER TABLE tags1 ADD COLUMN `lang_id` int DEFAULT 1;");
    runQuery("UPDATE tags1 JOIN posts ON tags1.post_id = posts.id SET tags1.lang_id = posts.lang_id;");
    runQuery("INSERT INTO tags (tag, tag_slug, lang_id) SELECT DISTINCT tag, tag_slug, lang_id FROM tags1");
    runQuery("INSERT INTO post_tags (post_id, tag_id) SELECT t.post_id, tg.id FROM tags1 t JOIN tags tg
        ON t.tag = tg.tag AND t.tag_slug = tg.tag_slug AND t.lang_id = tg.lang_id;");

    //add new translations
    $p["active_payment_request_error"] = "You already have an active payment request! Once this is complete, you can make a new request.";
    $p["add_role"] = "Add Role";
    $p["add_tag"] = "Add Tag";
    $p["ai_content_creator"] = "AI Content Creator";
    $p["ai_writer"] = "AI Writer";
    $p["automatically_calculated"] = "Automatically Calculated";
    $p["bitcoin"] = "Bitcoin";
    $p["bitcoin_address"] = "Bitcoin Address";
    $p["bulk_post_upload_for_authors"] = "Bulk Post Upload for Authors";
    $p["comments_contact"] = "Comments & Contact Messages";
    $p["discord"] = "Discord";
    $p["download"] = "Download";
    $p["edited"] = "Edited";
    $p["enter_2_characters"] = "Enter at least 2 characters";
    $p["enter_topic"] = "Enter topic";
    $p["enter_url"] = "Enter URL";
    $p["generated_text"] = "Generated Text";
    $p["generate_text"] = "Generate Text";
    $p["generating_text"] = "Generating text...";
    $p["google_indexing_api"] = "Google Indexing API";
    $p["human_verification"] = "Human Verification";
    $p["human_verification_exp"] = "Validate user activity through mouse movements, scrolling, and time spent on the page to ensure genuine interaction and prevent bots.";
    $p["instagram"] = "Instagram";
    $p["invalid_withdrawal_amount"] = "Invalid withdrawal amount!";
    $p["length_of_text"] = "Length of Text";
    $p["linkedin"] = "Linkedin";
    $p["logo_size"] = "Logo Size";
    $p["long"] = "Long";
    $p["manage_tags"] = "Manage Tags";
    $p["medium"] = "Medium";
    $p["min_mouse_movements"] = "Minimum Mouse Movements";
    $p["min_poyout_amount"] = "Minimum payout amount";
    $p["min_poyout_amounts"] = "Minimum Payout Amounts";
    $p["min_scroll_movements"] = "Minimum Scroll Movements";
    $p["min_time_spent_on_page"] = "Minimum Time Spent on the Page (Seconds)";
    $p["model"] = "Model";
    $p["msg_request_sent"] = "The request has been sent successfully!";
    $p["msg_tag_exists"] = "This tag already exists!";
    $p["msg_topic_empty"] = "Topic cannot be empty!";
    $p["my_earnings"] = "My Earnings";
    $p["new_payout_request"] = "New Payout Request";
    $p["pending"] = "Pending";
    $p["pinterest"] = "Pinterest";
    $p["pwa_logo"] = "PWA Logo";
    $p["refresh"] = "Refresh";
    $p["regenerate"] = "Regenerate";
    $p["roles"] = "Roles";
    $p["searching"] = "Searching...";
    $p["short"] = "Short";
    $p["static_cache_system"] = "Static Cache System";
    $p["submit"] = "Submit";
    $p["telegram"] = "Telegram";
    $p["temperature_response_diversity"] = "Temperature (Response Diversity)";
    $p["test_api"] = "Test API";
    $p["tiktok"] = "Tiktok";
    $p["tone_academic"] = "Academic";
    $p["tone_casual"] = "Casual";
    $p["tone_critical"] = "Critical";
    $p["tone_formal"] = "Formal";
    $p["tone_humorous"] = "Humorous";
    $p["tone_inspirational"] = "Inspirational";
    $p["tone_persuasive"] = "Persuasive";
    $p["tone_professional"] = "Professional";
    $p["tone_style"] = "Tone/Style";
    $p["topic"] = "Topic";
    $p["trending_posts"] = "Trending Posts";
    $p["twitch"] = "Twitch";
    $p["use_text"] = "Use Text";
    $p["very_long"] = "Very Long";
    $p["very_short"] = "Very Short";
    $p["view_post"] = "View Post";
    $p["vk"] = "VK";
    $p["warning_documentation"] = "Read the documentation before enabling this option";
    $p["whatsapp"] = "WhatsApp";
    $p["withdraw_amount"] = "Withdrawal Amount";
    $p["withdraw_method"] = "Withdrawal Method";
    $p["your_balance"] = "Your Balance";
    $p["youtube"] = "YouTube";
    addTranslations($p);

    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='administrators';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_role_changed';");
    runQuery("DELETE FROM language_translations WHERE `label`='no_thanks';");
    runQuery("DELETE FROM language_translations WHERE `label`='priority_none';");
    runQuery("DELETE FROM language_translations WHERE `label`='pwa_warning';");
    runQuery("DELETE FROM language_translations WHERE `label`='server_response';");
    runQuery("DELETE FROM language_translations WHERE `label`='set_default_payment_account';");
    runQuery("DELETE FROM language_translations WHERE `label`='warning_default_payout_account';");

    //indexes
    runQuery("ALTER TABLE ci_sessions ADD INDEX idx_id (id);");
    runQuery("CREATE INDEX idx_comments_optimized ON comments (post_id, parent_id, status);");
    runQuery("ALTER TABLE posts ADD INDEX idx_slug (slug);");
    runQuery("ALTER TABLE posts ADD INDEX idx_title_hash (title_hash);");
    runQuery("ALTER TABLE posts ADD INDEX idx_post_type (post_type);");
    runQuery("ALTER TABLE posts ADD INDEX idx_feed_id (feed_id);");
    runQuery("ALTER TABLE posts ADD INDEX idx_image_id (image_id);");
    runQuery("CREATE INDEX idx_latest_category_posts ON posts (is_scheduled, visibility, status, category_id, created_at);");
    runQuery("CREATE INDEX idx_posts_optimized ON posts (lang_id, is_scheduled, visibility, status, category_id, user_id);");
    runQuery("CREATE INDEX idx_posts_profile ON posts (lang_id, is_scheduled, visibility, status, user_id, created_at);");
    runQuery("CREATE FULLTEXT INDEX idx_fulltext ON posts (title, summary, content);");
    runQuery("CREATE INDEX idx_user_rewards ON post_pageviews_month (post_user_id, reward_amount, created_at);");
    runQuery("ALTER TABLE post_selections ADD INDEX idx_post_id (post_id);");
    runQuery("ALTER TABLE post_tags ADD INDEX idx_post_id (post_id);");
    runQuery("CREATE INDEX idx_tag_post ON post_tags (tag_id, post_id);");
    runQuery("ALTER TABLE tags ADD INDEX idx_tag_slug (tag_slug);");
    runQuery("ALTER TABLE tags ADD INDEX idx_lang_id (lang_id);");
    runQuery("ALTER TABLE users ADD INDEX idx_status (status);");
    runQuery("ALTER TABLE users ADD INDEX idx_reward_system_enabled (reward_system_enabled);");
    runQuery("ALTER TABLE users ADD INDEX idx_reward_balance (balance);");
    runQuery("ALTER TABLE users ADD INDEX idx_slug (slug);");
    runQuery("ALTER TABLE post_item_images ADD INDEX idx_item_type (item_type);");

    runQuery("UPDATE general_settings SET sitemap_frequency = 'auto', sitemap_last_modification = 'auto', sitemap_priority = 'auto', version = '2.4';");

    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_paypal_status`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_iban_status`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `payout_swift_status`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_slider`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_featured`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_recommended`;");
    runQuery("ALTER TABLE posts DROP COLUMN `is_breaking`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_big`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_default`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_slider`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_mid`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_small`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_mime`;");
    runQuery("ALTER TABLE posts DROP COLUMN `image_storage`;");
    runQuery("ALTER TABLE post_pageviews_month DROP COLUMN `user_agent`;");
    runQuery("ALTER TABLE settings DROP COLUMN `facebook_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `twitter_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `instagram_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `tiktok_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `whatsapp_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `youtube_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `discord_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `telegram_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `pinterest_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `linkedin_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `twitch_url`;");
    runQuery("ALTER TABLE settings DROP COLUMN `vk_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `facebook_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `twitter_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `instagram_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `tiktok_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `whatsapp_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `youtube_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `discord_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `telegram_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `pinterest_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `linkedin_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `twitch_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `vk_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `personal_website_url`;");
    runQuery("ALTER TABLE users DROP COLUMN `role`;");
    runQuery("DROP TABLE tags1;");
    runQuery("DROP TABLE roles_permissions;");
    runQuery("DROP TABLE user_payout_accounts;");
    runQuery("DROP TABLE routes;");
    runQuery("ALTER TABLE images DROP INDEX idx_image_big;");

    //clear cache
    $cacheDir = __DIR__ . '/writable/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    @unlink($file);
                }
            }
        }
    }
}

function addTranslations($translations)
{
    global $connection;

    $languages = runQuery("SELECT * FROM languages;");
    if (!empty($languages->num_rows)) {
        while ($language = mysqli_fetch_array($languages)) {
            foreach ($translations as $key => $value) {
                $trans = runQuery("SELECT * FROM language_translations WHERE label ='" . $key . "' AND lang_id = " . $language['id']);
                if (empty($trans->num_rows)) {
                    $stmt = $connection->prepare("INSERT INTO language_translations (`lang_id`, `label`, `translation`) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $language['id'], $key, $value);
                    $stmt->execute();
                }
            }
        }
    }
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Varient - Update Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #444 !important;
            font-size: 14px;
            background: #007991;
            background: -webkit-linear-gradient(to left, #007991, #6fe7c2);
            background: linear-gradient(to left, #007991, #6fe7c2);
        }

        .logo-cnt {
            text-align: center;
            color: #fff;
            padding: 60px 0 60px 0;
        }

        .logo-cnt .logo {
            font-size: 42px;
            line-height: 42px;
        }

        .logo-cnt p {
            font-size: 22px;
        }

        .install-box {
            width: 100%;
            padding: 30px;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            background-color: #fff;
            border-radius: 4px;
            display: block;
            float: left;
            margin-bottom: 100px;
        }

        .form-input {
            box-shadow: none !important;
            border: 1px solid #ddd;
            height: 44px;
            line-height: 44px;
            padding: 0 20px;
        }

        .form-input:focus {
            border-color: #239CA1 !important;
        }

        .btn-custom {
            background-color: #239CA1 !important;
            border-color: #239CA1 !important;
            border: 0 none;
            border-radius: 4px;
            box-shadow: none;
            color: #fff !important;
            font-size: 16px;
            font-weight: 300;
            height: 40px;
            line-height: 40px;
            margin: 0;
            min-width: 105px;
            padding: 0 20px;
            text-shadow: none;
            vertical-align: middle;
        }

        .btn-custom:hover, .btn-custom:active, .btn-custom:focus {
            background-color: #239CA1;
            border-color: #239CA1;
            opacity: .8;
        }

        .tab-content {
            width: 100%;
            float: left;
            display: block;
        }

        .buttons {
            display: block;
            float: left;
            width: 100%;
            margin-top: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 0;
            text-align: center;
        }

        .alert {
            text-align: center;
        }

        .alert strong {
            font-weight: 500 !important;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            font-size: 15px;
        }
        .form-control::placeholder {
            color: #9AA2AA;
            opacity: 1;
        }

        .form-control:-ms-input-placeholder {
            color: #9AA2AA;
        }

        .form-control::-ms-input-placeholder {
            color: #9AA2AA;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-12">
            <div class="row">
                <div class="col-sm-12 logo-cnt">
                    <h1>Varient</h1>
                    <p>Welcome to the Update Wizard</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="install-box">
                        <h2 class="title">Update from v1.9.x to v2.4.3</h2>
                        <br><br>
                        <div class="messages">
                            <?php if (!empty($error)) { ?>
                                <div class="alert alert-danger">
                                    <strong><?= $error; ?></strong>
                                </div>
                            <?php } ?>
                            <?php if (!empty($success)) { ?>
                                <div class="alert alert-success">
                                    <strong><?= $success; ?></strong>
                                    <style>.alert-info {
                                            display: none;
                                        }</style>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="step-contents">
                            <div class="tab-1">
                                <?php if (empty($success)): ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                        <input type="hidden" name="license_code" value="<?= !empty($license_code) ? $license_code : ''; ?>">
                                        <input type="hidden" name="purchase_code" value="<?= !empty($purchase_code) ? $purchase_code : ''; ?>">
                                        <div class="tab-content">
                                            <div class="tab_1">
                                                <div class="alert alert-primary" style="text-align: left">
                                                    <p>** Please take a backup of your database before you start. You can export this backup in .sql format using the "export" option in phpMyAdmin.</p>
                                                    <p>** Updating may take some time depending on the number of records in your database. If you have many posts (20k and above), you may need to increase
                                                        the "max_execution_time" value in your PHP settings. Otherwise, your server may stop working before the update process is completed.</p>
                                                    <p>** If there is an error during the update or if it is interrupted, you will need to delete the database, restore your database backup (with the "import" option in phpMyAdmin), and try again.</p>
                                                </div>
                                                <p class="text-success text-center" style="font-weight: 500;">Enter your database credentials and click the button to update the database.</p>
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Host</label>
                                                    <input type="text" class="form-control form-input" name="db_host" placeholder="Host" value="<?= !empty($data['db_host']) ? $data['db_host'] : 'localhost'; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Database Name</label>
                                                    <input type="text" class="form-control form-input" name="db_name" placeholder="Database Name" value="<?= !empty($data['db_name']) ? $data['db_name'] : ''; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Username</label>
                                                    <input type="text" class="form-control form-input" name="db_user" placeholder="Username" value="<?= !empty($data['db_user']) ? $data['db_user'] : ''; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="email">Password</label>
                                                    <input type="text" class="form-control form-input" name="db_password" placeholder="Password" value="<?= !empty($data['db_password']) ? $data['db_password'] : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="buttons text-center">
                                            <button type="submit" name="btnUpdate" class="btn btn-success btn-custom" style="width: 100%; height: 50px;">Update My Database</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>