<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Mailer Library - Symfony Mailer Extension</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/mailer-symfony/v/stable.png)](https://packagist.org/packages/yiisoft/mailer-symfony)
[![Total Downloads](https://poser.pugx.org/yiisoft/mailer-symfony/downloads.png)](https://packagist.org/packages/yiisoft/mailer-symfony)
[![Build status](https://github.com/yiisoft/mailer-symfony/workflows/build/badge.svg)](https://github.com/yiisoft/mailer-symfony/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/mailer-symfony/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/mailer-symfony/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/mailer-symfony/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/mailer-symfony/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fmailer-symfony%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/mailer-symfony/master)
[![static analysis](https://github.com/yiisoft/mailer-symfony/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/mailer-symfony/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/mailer-symfony/coverage.svg)](https://shepherd.dev/github/yiisoft/mailer-symfony)

This package is an adapter for [yiisoft/mailer](https://github.com/yiisoft/mailer) relying on
[symfony/mailer](https://github.com/symfony/mailer).

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with composer:

```shell
composer require yiisoft/mailer-symfony --prefer-dist
```

## General usage

Creating a mailer:

```php
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageBodyTemplate;
use Yiisoft\Mailer\MessageFactory;
use Yiisoft\Mailer\Symfony\Mailer;
use Yiisoft\Mailer\Symfony\Message;

/**
 * @var \Psr\EventDispatcher\EventDispatcherInterface $dispatcher
 * @var \Symfony\Component\Mailer\Transport\TransportInterface $transport
 * @var \Yiisoft\View\View $view
 */

$template = new MessageBodyTemplate('/path/to/directory/of/view-files');

$mailer = new Mailer(
    new MessageFactory(Message::class),
    new MessageBodyRenderer($view, $template),
    $dispatcher,
    $transport,
);
```

Sending a mail message:

```php
$message = $mailer->compose()
    ->withFrom('from@domain.com')
    ->withTo('to@domain.com')
    ->withSubject('Message subject')
    ->withTextBody('Plain text content')
    ->withHtmlBody('<b>HTML content</b>')
;
$mailer->send($message);
// Or several
$mailer->sendMultiple([$message]);
```

Additional methods of the `Yiisoft\Mailer\Symfony\Mailer`:

- `withEncryptor()` - Returns a new instance with the specified encryptor instance.
- `withSigner()` - Returns a new instance with the specified signer instance.

For more information about signing and encrypting messages, see the corresponding section of the
[documentation](https://symfony.com/doc/current/mailer.html#signing-and-encrypting-messages).

The `Yiisoft\Mailer\Symfony\Message` class provides a single `getSymfonyEmail()` method that returns
a [Symfony Email](https://symfony.com/doc/current/mailer.html#creating-sending-messages) instance.

For use in the [Yii framework](http://www.yiiframework.com/), see the configuration files:

- [`config/common.php`](https://github.com/yiisoft/mailer-symfony/blob/master/config/common.php)
- [`config/params.php`](https://github.com/yiisoft/mailer-symfony/blob/master/config/params.php)

See [Yii guide to mailing](https://github.com/yiisoft/docs/blob/master/guide/en/tutorial/mailing.md)
and [Symfony Mailer documentation](https://symfony.com/doc/current/mailer.html) for more info.

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Framework Symfony Mailer Extension is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
