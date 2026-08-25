<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BitChest Crypto Configuration
    |--------------------------------------------------------------------------
    |
    | Nombre de jours d'historique à générer par crypto.
    | Cahier des charges initial: 30, implémentation finale: 40.
    | Modifiable via .env BITCHEST_HISTORY_DAYS
    |
    */
    'history_days' => (int) env('BITCHEST_HISTORY_DAYS', 40),

    // Liste officielle des 10 cryptos (source de vérité)
    'cryptos' => [
        'BTC' => ['name' => 'Bitcoin',       'category' => 'Payment',          'website' => 'https://bitcoin.org',       'price_eur' => 95000.00,  'market_cap' => 1900000000000.00],
        'ETH' => ['name' => 'Ethereum',      'category' => 'Smart Contracts',  'website' => 'https://ethereum.org',      'price_eur' => 3500.00,   'market_cap' => 420000000000.00],
        'XRP' => ['name' => 'Ripple',        'category' => 'Payment',          'website' => 'https://ripple.com',        'price_eur' => 2.50,     'market_cap' => 140000000000.00],
        'BCH' => ['name' => 'Bitcoin Cash',  'category' => 'Payment',          'website' => 'https://bitcoincash.org',   'price_eur' => 450.00,   'market_cap' => 9000000000.00],
        'ADA' => ['name' => 'Cardano',       'category' => 'Smart Contracts',  'website' => 'https://cardano.org',       'price_eur' => 1.05,     'market_cap' => 35000000000.00],
        'LTC' => ['name' => 'Litecoin',      'category' => 'Payment',          'website' => 'https://litecoin.org',      'price_eur' => 180.00,   'market_cap' => 14000000000.00],
        'XEM' => ['name' => 'NEM',           'category' => 'Smart Contracts',  'website' => 'https://nem.io',            'price_eur' => 0.0012,   'market_cap' => 11000000000.00],
        'XLM' => ['name' => 'Stellar',       'category' => 'Payment',          'website' => 'https://stellar.org',       'price_eur' => 0.35,     'market_cap' => 10000000000.00],
        'IOTA'=> ['name' => 'IOTA',          'category' => 'IoT',              'website' => 'https://www.iota.org',      'price_eur' => 0.30,     'market_cap' => 800000000.00],
        'DASH'=> ['name' => 'Dash',          'category' => 'Payment',          'website' => 'https://www.dash.org',      'price_eur' => 40.00,    'market_cap' => 500000000.00],
    ],
];
