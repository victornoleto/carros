<?php

return [
    'state_filter' => env('CAR_SCRAPING_STATE_FILTER'),
    'verify_tls' => env('CAR_SCRAPING_VERIFY_TLS', true),
    'timeout' => (int) env('CAR_SCRAPING_TIMEOUT', 60),
    'max_pages' => (int) env('CAR_SCRAPING_MAX_PAGES', 50),
];
