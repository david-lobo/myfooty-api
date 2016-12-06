<?php

$scrapeLiveUrls = env('SCRAPE_LIVE_URLS', false);

$configs = config()->get('scraping.configs');

$configKey = $scrapeLiveUrls ? 'live' : 'local';

$settings = $configs[$configKey];

$settings["archive"] = env('SCRAPE_ARCHIVE_FILES', false);

return $settings;
