<?php

return [
    'user_agents' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    ],
    'allowed_domains' => explode(',', env('SCRAPE_ALLOWED_DOMAINS', 'amazon.com,jumia.com,jumia.com.eg')),
    'request_timeout' => env('SCRAPE_TIMEOUT', 10),
    'max_concurrency' => env('SCRAPE_MAX_CONCURRENCY', 2),
    'retry_attempts' => env('SCRAPE_RETRY_ATTEMPTS', 2),
    'retry_sleep_ms' => env('SCRAPE_RETRY_SLEEP_MS', 750),
];
