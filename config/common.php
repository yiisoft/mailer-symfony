<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Definitions\Reference;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Mailer\FileMailer;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageBodyTemplate;
use Yiisoft\Mailer\MessageFactory;
use Yiisoft\Mailer\MessageFactoryInterface;
use Yiisoft\Mailer\Symfony\Mailer;
use Yiisoft\Mailer\Symfony\Message;
use Yiisoft\View\View;

/** @var array $params */

return [
    MessageBodyRenderer::class => [
        'class' => MessageBodyRenderer::class,
        '__construct()' => [
            Reference::to(View::class),
            DynamicReference::to(static fn (Aliases $aliases) => new MessageBodyTemplate(
                $aliases->get($params['yiisoft/mailer']['messageBodyTemplate']['viewPath']),
            )),
        ],
    ],

    MessageFactoryInterface::class => [
        'class' => MessageFactory::class,
        '__construct()' => [
            Message::class,
        ],
    ],

    TransportInterface::class => $params['yiisoft/mailer']['useSendmail']
        ? SendmailTransport::class
        : static function () use ($params): TransportInterface {
            return (new EsmtpTransportFactory())->create(new Dsn(
                $params['symfony/mailer']['esmtpTransport']['scheme'],
                $params['symfony/mailer']['esmtpTransport']['host'],
                $params['symfony/mailer']['esmtpTransport']['username'],
                $params['symfony/mailer']['esmtpTransport']['password'],
                $params['symfony/mailer']['esmtpTransport']['port'],
                $params['symfony/mailer']['esmtpTransport']['options'],
            ));
        },

    FileMailer::class => [
        'class' => FileMailer::class,
        '__construct()' => [
            'path' => DynamicReference::to(fn (Aliases $aliases) => $aliases->get(
                $params['yiisoft/mailer']['fileMailer']['fileMailerStorage'],
            )),
        ],
    ],

    MailerInterface::class => $params['yiisoft/mailer']['writeToFiles'] ? FileMailer::class : Mailer::class,
];
