<?php

return [
    // Author
    'author' => env('LUKIWIKI_AUTHOR', 'owner'),
    // Password (This feature will be removed in 1.0)
    'password' => env('LUKIWIKI_PASSWORD', 'adminpass'),
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
        'default' => env('LUKIWIKI_MAINPAGE', 'MainPage'),
        // Sidebar Page Name
        'sidebar' => env('LUKIWIKI_SIDEBAR', 'SideBar'),
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
        'abbr' => App\LukiWiki\Plugins\Abbr::class,
        'br' => App\LukiWiki\Plugins\LineBreak::class,
        'calendar' => App\LukiWiki\Plugins\Calendar::class,
        'clear' => App\LukiWiki\Plugins\Clearfix::class,
        'color' => App\LukiWiki\Plugins\Color::class,
        'image' => App\LukiWiki\Plugins\Image::class,
        'navi' => App\LukiWiki\Plugins\Navi::class,
        'ruby' => App\LukiWiki\Plugins\Ruby::class,
        'size' => App\LukiWiki\Plugins\Size::class,
        'timestamp' => App\LukiWiki\Plugins\Timestamp::class,
    ],
];
