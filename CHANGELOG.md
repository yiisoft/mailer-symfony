# Yii Mailer - Symfony Mailer Extension Change Log

## 4.0.0 under development

- Chg #50: Raise minimal PHP version to `^8.1` (@vjik)
- Bug #56: Use file ID for attachments and embedded files (@vjik)
- Chg #49, #58, #62, #63: Adapt to Yii Mailer 6: remove `Message` class, add `$messageSettings` parameter to `Mailer`
  constructor, remove `MessageBodyRenderer` usage (@vjik)
- Enh #59: Make `psr/event-dispatcher` dependency optional (@vjik)
- Chg #59: Change order of constructor parameters in `Mailer` (@vjik)
- Chg #48: Change package configuration params prefix to `yiisoft/mailer-symfony` (@vjik)
- Chg #64: Remove `FileMailer` configuration and `writeToFiles` parameter from package configuration (@vjik)

## 3.0.1 May 24, 2024

- Enh #39: Add support for `symfony/mime` of version ^7.0 (@vjik)
- Enh #41: Add support for `symfony/mailer` of version ^7.0 (@vjik)

## 3.0.0 February 16, 2023

- Chg #27: Adapt configuration group names to Yii conventions (@vjik)

## 2.1.0 January 02, 2023

- Chg #24: Raise minimal PHP version to `^8.0` (@vjik)
- Chg #25: Raise `yiisoft/mailer` version to `^5.0`, `MessageInterface` adapt to it (@vjik)
- Enh #18: Explicitly add transitive dependencies `psr/event-dispatcher` and `symfony/mime` (@vjik)

## 2.0.0 July 25, 2022

- Chg #13: Update the `yiisoft/mailer` dependency, up to `^4.0` (@thenotsoft)
- Enh #7: Add support for version `^6.0` for `symfony/mailer` package (@devanych)

## 1.0.1 November 22, 2021

- Enh #6: Improve the configuration for passing additional options to `EsmtpTransport` instance (@devanych)

## 1.0.0 October 17, 2021

- Initial release.
