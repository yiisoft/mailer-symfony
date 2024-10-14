<?php

declare(strict_types=1);

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Symfony\Mailer;

/** @var array $params */

return [
    TransportInterface::class => $params['yiisoft/mailer-symfony']['useSendmail']
        ? SendmailTransport::class
        : static fn(EsmtpTransportFactory $esmtpTransportFactory): TransportInterface => $esmtpTransportFactory->create(
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
];
