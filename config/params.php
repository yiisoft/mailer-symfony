<?php

declare(strict_types=1);

return [
    'yiisoft/mailer-symfony' => [
        'useSendmail' => false,
        'esmtpTransport' => [
            'scheme' => 'smtps', // "smtps": using TLS, "smtp": without using TLS.
            'host' => 'smtp.example.com',
            'port' => 465,
            'username' => 'admin@example.com',
            'password' => '',
            'options' => [], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
        ],
    ],
];
