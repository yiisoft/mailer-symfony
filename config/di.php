<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Mailer\HtmlToTextBodyConverter;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageSettings;
use Yiisoft\Mailer\Symfony\Mailer;

/** @var array $params */

return [
    TransportInterface::class => $params['yiisoft/mailer-symfony']['useSendmail']
        ? SendmailTransport::class
        : static fn(EsmtpTransportFactory $factory): TransportInterface => $factory->create(
            new Dsn(
                $params['yiisoft/mailer-symfony']['esmtpTransport']['scheme'],
                $params['yiisoft/mailer-symfony']['esmtpTransport']['host'],
                $params['yiisoft/mailer-symfony']['esmtpTransport']['username'],
                $params['yiisoft/mailer-symfony']['esmtpTransport']['password'],
                $params['yiisoft/mailer-symfony']['esmtpTransport']['port'],
                $params['yiisoft/mailer-symfony']['esmtpTransport']['options'],
            )
        ),

    MailerInterface::class => Mailer::class,

    Mailer::class => [
        '__construct()' => [
            'messageSettings' => new MessageSettings(
                $params['yiisoft/mailer-symfony']['messageSettings']['charset'],
                $params['yiisoft/mailer-symfony']['messageSettings']['from'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addFrom'],
                $params['yiisoft/mailer-symfony']['messageSettings']['to'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addTo'],
                $params['yiisoft/mailer-symfony']['messageSettings']['replyTo'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addReplyTo'],
                $params['yiisoft/mailer-symfony']['messageSettings']['cc'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addCc'],
                $params['yiisoft/mailer-symfony']['messageSettings']['bcc'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addBcc'],
                $params['yiisoft/mailer-symfony']['messageSettings']['subject'],
                $params['yiisoft/mailer-symfony']['messageSettings']['date'],
                $params['yiisoft/mailer-symfony']['messageSettings']['priority'],
                $params['yiisoft/mailer-symfony']['messageSettings']['returnPath'],
                $params['yiisoft/mailer-symfony']['messageSettings']['sender'],
                $params['yiisoft/mailer-symfony']['messageSettings']['textBody'],
                $params['yiisoft/mailer-symfony']['messageSettings']['htmlBody'],
                $params['yiisoft/mailer-symfony']['messageSettings']['attachments'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addAttachments'],
                $params['yiisoft/mailer-symfony']['messageSettings']['embeddings'],
                $params['yiisoft/mailer-symfony']['messageSettings']['addEmbeddings'],
                $params['yiisoft/mailer-symfony']['messageSettings']['headers'],
                $params['yiisoft/mailer-symfony']['messageSettings']['overwriteHeaders'],
                $params['yiisoft/mailer-symfony']['messageSettings']['convertHtmlToText']
                    ? new HtmlToTextBodyConverter()
                    : null,
            ),
        ],
    ],
];
