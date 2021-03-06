<?php

return [
    // Author
    'author' => 'Owner',
    // Password (This feature will be removed in 1.0)
    'password' => 'adminpass',
    // Theme (bootswatch only)
    'theme' => env('LUKIWIKI_THEME', 'default'),
    // Directory Configure
    'directory' => [
        // Attachments file directory
        'attach' => 'attachments',
        // Thumbnail directory
        'thumb' => 'thumbnails',
    ],
    // Special Page Name
    'special_page' => [
        // Default Page Name
        'default' => 'MainPage',
        // Sidebar Page Name
        'sidebar' => 'SideBar',
    ],
    // Feed
    'feed' => [
        // Feed Title
        'title' => 'LukiWiki Recent Feeds',
        // Description
        'description' => '',
        // Feeds
        'entries' => 20,
    ],
    'render' => [
        // Expand media tag (img, video, audio tag) external media (such as picture, audio, movie) file
        'expand_external_media_file' => true,
    ],
    'backup' => [
        // Update interval to backup. (second) If you set it to 0, you will always be backing up. (Default is 1h = 3600s)
        'interval' => 3600,
        // Maximum number of backups
        'max_entries' => 20,
    ],
    'plugin' => [
        'abbr'      => App\LukiWiki\Plugins\Abbr::class,
        'calendar'  => App\LukiWiki\Plugins\Calendar::class,
        'color'     => App\LukiWiki\Plugins\Color::class,
        'navi'      => App\LukiWiki\Plugins\Navi::class,
        'ruby'      => App\LukiWiki\Plugins\Ruby::class,
        'timestamp' => App\LukiWiki\Plugins\Timestamp::class,
    ],
];
