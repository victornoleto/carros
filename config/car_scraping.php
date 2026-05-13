<?php

return [
    'state_filter' => env('CAR_SCRAPING_STATE_FILTER'),
    'verify_tls' => env('CAR_SCRAPING_VERIFY_TLS', true),
    'timeout' => (int) env('CAR_SCRAPING_TIMEOUT', 60),
    'max_pages' => (int) env('CAR_SCRAPING_MAX_PAGES', 50),
    'page_sleep_seconds' => (int) env('CAR_SCRAPING_PAGE_SLEEP_SECONDS', 1),
    'proxy_url' => env('CAR_SCRAPING_PROXY_URL'),
    'proxy_token' => env('CAR_SCRAPING_PROXY_TOKEN'),
    'proxy_providers' => array_filter(array_map('trim', explode(',', env('CAR_SCRAPING_PROXY_PROVIDERS', '')))),
];
