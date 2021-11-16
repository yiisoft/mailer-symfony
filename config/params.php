<?php

declare(strict_types=1);

return [
    'yiisoft/mailer' => [
        'messageBodyTemplate' => [
            'viewPath' => '@resources/mail',
        ],
        'fileMailer' => [
            'fileMailerStorage' => '@runtime/mail',
        ],
        'useSendmail' => false,
        'writeToFiles' => true,
    ],
    'symfony/mailer' => [
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
