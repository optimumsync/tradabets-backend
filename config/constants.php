<?php

/*
 * This file is part of the Laravel Paystack package.
 *
 * (c) Prosper Otemuyiwa <prosperotemuyiwa@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// return [
//     'default_bonus' => 1000,
//     'SPORTBOOK_TOKEN_URL' => 'https://sp-int-u3x.6579883.com/1.0.0/auth',
//     'SPORTBOOK_SESSION_URL' => 'https://sp-int-u3x.6579883.com/1.0.0/start-session'
// ];

return [
    'default_bonus' => 1000,
    'SPORTBOOK_TOKEN_URL' => env('SPORTBOOK_TOKEN_URL', 'https://sp-int-u3x.6579883.com/1.0.0/auth'),
    'SPORTBOOK_SESSION_URL' => env('SPORTBOOK_SESSION_URL', 'https://sp-int-u3x.6579883.com/1.0.0/start-session'),
];
