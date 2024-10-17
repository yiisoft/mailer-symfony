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
        'messageSettings' => [
            'charset' => null,
            'from' => null,
            'addFrom' => null,
            'to' => null,
            'addTo' => null,
            'replyTo' => null,
            'addReplyTo' => null,
            'cc' => null,
            'addCc' => null,
            'bcc' => null,
            'addBcc' => null,
            'subject' => null,
            'date' => null,
            'priority' => null,
            'returnPath' => null,
            'sender' => null,
            'textBody' => null,
            'htmlBody' => null,
            'attachments' => null,
            'addAttachments' => null,
            'embeddings' => null,
            'addEmbeddings' => null,
            'headers' => null,
            'overwriteHeaders' => null,
            'convertHtmlToText' => true,
        ],
    ],
];
