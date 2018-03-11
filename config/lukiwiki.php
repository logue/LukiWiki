<?php

return [
    // Name of Site
    'sitename' => 'LukiWiki',
    // Site Description
    'description' => 'LukiWiki based site.',
    // Author
    'author' => 'Owner',
    // Directory Configure
    'directory' => [
        // Wiki data directory
        'data' => 'data',
        // Attachments file directory
        'attachments' => 'attach',
        // Backup file directory
        'backup' => 'backup',
        // Meta data directory
        'meta' => 'meta',
    ],
    // Special Page Name
    'special_page' => [
        // Default Page Name
        'default' => 'MainPage',
        // Sidebar Page Name
        'sidebar' => 'SideBar',
        // InterWikiName Page Name
        'interwikiname' => 'InterWikiName',
    ],
    // Feed
    'feed' => [
        // Feed Title
        'title' => 'LukiWiki Recent Feeds',
        // Description
        'description' => '',
    ],
    'render' => [
        // Expand media tag (img, video, audio tag) external media (such as picture, audio, movie) file
        'expand_external_media_file' => true,
    ],
];
