<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Mailer - Symfony Mailer Extension</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/mailer-symfony/v)](https://packagist.org/packages/yiisoft/mailer-symfony)
[![Total Downloads](https://poser.pugx.org/yiisoft/mailer-symfony/downloads)](https://packagist.org/packages/yiisoft/mailer-symfony)
[![Build status](https://github.com/yiisoft/mailer-symfony/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/mailer-symfony/actions/workflows/build.yml)
[![Code Coverage](https://codecov.io/gh/yiisoft/mailer-symfony/graph/badge.svg?token=5QIVH0fbPD)](https://codecov.io/gh/yiisoft/mailer-symfony)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fmailer-symfony%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/mailer-symfony/master)
[![static analysis](https://github.com/yiisoft/mailer-symfony/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/mailer-symfony/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/mailer-symfony/coverage.svg)](https://shepherd.dev/github/yiisoft/mailer-symfony)

This package is an adapter for [yiisoft/mailer](https://github.com/yiisoft/mailer) relying on
[symfony/mailer](https://github.com/symfony/mailer).

## Requirements

- PHP 8.1 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/mailer-symfony
```

## General usage

Creating a mailer:

```php
use Yiisoft\Mailer\Symfony\Mailer;

/**
 * @var \Symfony\Component\Mailer\Transport\TransportInterface $transport
 */

$mailer = new \Yiisoft\Mailer\Symfony\Mailer(
    $transport,
);
```

Sending a mail message:

```php
$message = (new \Yiisoft\Mailer\Message())
    ->withFrom('from@domain.com')
    ->withTo('to@domain.com')
    ->withSubject('Message subject')
    ->withTextBody('Plain text content')
    ->withHtmlBody('<b>HTML content</b>');
    
$mailer->send($message);
// Or several
$mailer->sendMultiple([$message]);
```

Additional methods of the `Yiisoft\Mailer\Symfony\Mailer`:

- `withEncryptor()` - Returns a new instance with the specified encryptor instance.
- `withSigner()` - Returns a new instance with the specified signer instance.

For more information about signing and encrypting messages, see the corresponding section of the
[documentation](https://symfony.com/doc/current/mailer.html#signing-and-encrypting-messages).

For use in the [Yii framework](https://www.yiiframework.com/), see the configuration files:
  - [`config/di.php`](https://github.com/yiisoft/mailer-symfony/blob/master/config/di.php)
  - [`config/params.php`](https://github.com/yiisoft/mailer-symfony/blob/master/config/params.php)

## Documentation

- [Yii guide to mailing](https://github.com/yiisoft/docs/blob/master/guide/en/tutorial/mailing.md)
- [Symfony Mailer documentation](https://symfony.com/doc/current/mailer.html)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Mailer - Symfony Mailer Extension is free software. It is released under the terms of the BSD License.
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
