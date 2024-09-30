<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use stdClass;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;
use Yiisoft\Mailer\Symfony\Mailer;
use Yiisoft\Mailer\Symfony\Tests\TestAsset\DummyTransport;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function openssl_pkcs7_decrypt;
use function sprintf;
use function stream_get_meta_data;
use function tmpfile;
use function unlink;

final class MailerTest extends TestCase
{
    private Message $message;
    private string $keyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = (new Message())
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withSubject('Test Subject')
            ->withTextBody('Test Body')
        ;

        $this->keyPath = __DIR__ . '/TestAsset';
    }

    public function testSend(): void
    {
        $mailer = $this->get(MailerInterface::class);

        $mailer->send($this->message);
        $transport = $this->get(TransportInterface::class);

        $this->assertCount(1, $transport->getSentMessages());
    }

    public function testSendFailureForNotSetSubject(): void
    {
        $mailer = $this->get(MailerInterface::class);

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Subject is required.');

        $mailer->send(
            (new Message())
                ->withFrom('from@example.com')
                ->withTo('to@example.com')
                ->withTextBody('Test Body')
        );
    }

    public function testWithSignerFailureForObjectIsNotInstanceOfDkimSignerOrSMimeSigner(): void
    {
        $mailer = $this->get(MailerInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The signer must be an instance of "%s" or "%s". The "%s" instance is received.',
            DkimSigner::class,
            SMimeSigner::class,
            stdClass::class,
        ));

        $mailer->withSigner(new stdClass());
    }

    public function testSendWithDkimSigner(): void
    {
        $mailer = $this
            ->get(MailerInterface::class)
            ->withSigner(new DkimSigner(
                file_get_contents("$this->keyPath/sign.key"),
                'example.com',
                'default',
            ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $mailer->send($this->message);
        $sentMessage = $this
            ->get(TransportInterface::class)
            ->getSentMessages()[0];

        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertTrue($sentMessage
            ->getHeaders()
            ->has('DKIM-Signature'));
    }

    public function testSendWithSMimeSigner(): void
    {
        $mailer = $this
            ->get(MailerInterface::class)
            ->withSigner(new SMimeSigner(
                "$this->keyPath/sign.crt",
                "$this->keyPath/sign.key",
            ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $mailer->send($this->message);
        $sentMessage = $this
            ->get(TransportInterface::class)
            ->getSentMessages()[0];

        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertStringContainsString('S/MIME', $sentMessage
            ->getBody()
            ->bodyToString());
        $this->assertStringContainsString($this->message->getTextBody(), $sentMessage
            ->getBody()
            ->bodyToString());
    }

    public function testSendWithEncryptor1(): void
    {
        $mailer = $this
            ->get(MailerInterface::class)
            ->withEncryptor(new SMimeEncrypter(
                "$this->keyPath/encrypt.crt",
            ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $this->message = $this->message
            ->withAddedHeader('Date', (new DateTimeImmutable())->format(DateTimeInterface::RFC2822))
            ->withAddedHeader('Message-ID', 'some-id@example.com');

        $mailer->send($this->message);
        $sentMessage = $this
            ->get(TransportInterface::class)
            ->getSentMessages()[0];

        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertSentMessageIsEncryptedProperly($sentMessage);
    }

    public function testSendWithEncryptorAndWithSigner(): void
    {
        $mailer = $this
            ->get(MailerInterface::class)
            ->withEncryptor(new SMimeEncrypter("$this->keyPath/encrypt.crt"))
            ->withSigner(new DkimSigner(
                file_get_contents("$this->keyPath/sign.key"),
                'example.com',
                'default',
            ))
        ;

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $this->message = $this->message
            ->withAddedHeader('Date', (new DateTimeImmutable())->format(DateTimeInterface::RFC2822))
            ->withAddedHeader('Message-ID', 'some-id@example.com');

        $mailer->send($this->message);
        $sentMessage = $this
            ->get(TransportInterface::class)
            ->getSentMessages()[0];

        $this->assertSentMessageIsEncryptedProperly($sentMessage);
        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertTrue($sentMessage
            ->getHeaders()
            ->has('DKIM-Signature'));
    }

    public static function dataSendWithEmbeddings(): iterable
    {
        yield [File::fromContent(file_get_contents(__DIR__ . '/TestAsset/yii.png'), 'yii-logo.png', 'image/png')];
        yield [File::fromPath(__DIR__ . '/TestAsset/yii.png', 'yii-logo.png', 'image/png')];
    }

    #[DataProvider('dataSendWithEmbeddings')]
    public function testSendWithEmbeddings(File $file): void
    {
        $html = '<img src="' . $file->cid() . '" alt="Yii logo">';

        $message = (new Message())
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withSubject('Test Subject')
            ->withHtmlBody($html)
            ->withEmbeddings($file);

        /** @var Mailer $mailer */
        $mailer = $this->get(MailerInterface::class);
        $mailer->send($message);

        /** @var DummyTransport $transport */
        $transport = $this->get(TransportInterface::class);
        $sentMessage = $transport->getSentMessages()[0]->toString();

        $this->assertStringContainsStringIgnoringLineEndings(
            "Content-Disposition: inline; name=\"{$file->id()}\";\r\n filename=yii-logo.png",
            $sentMessage
        );
        $this->assertStringContainsStringIgnoringLineEndings('Content-ID: <' . $file->id() . '>', $sentMessage);
    }

    public static function dataSendWithAttachments(): iterable
    {
        yield [File::fromContent(file_get_contents(__DIR__ . '/TestAsset/yii.png'), 'yii-logo.png', 'image/png')];
        yield [File::fromPath(__DIR__ . '/TestAsset/yii.png', 'yii-logo.png', 'image/png')];
    }

    #[DataProvider('dataSendWithAttachments')]
    public function testSendWithAttachments(File $file): void
    {
        $message = (new Message())
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withSubject('Test Subject')
            ->withTextBody('test')
            ->withAttachments($file);

        /** @var Mailer $mailer */
        $mailer = $this->get(MailerInterface::class);
        $mailer->send($message);

        /** @var DummyTransport $transport */
        $transport = $this->get(TransportInterface::class);
        $sentMessage = $transport->getSentMessages()[0]->toString();

        $this->assertStringContainsStringIgnoringLineEndings(
            'Content-Disposition: attachment; name=yii-logo.png; filename=yii-logo.png',
            $sentMessage
        );
        $this->assertStringContainsStringIgnoringLineEndings('Content-ID: <' . $file->id() . '>', $sentMessage);
    }

    private function assertSentMessageIsEncryptedProperly(SymfonyMessage $sentMessage): void
    {
        $encryptedFile = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($encryptedFile, $sentMessage->toString());
        $decryptedFile = stream_get_meta_data(tmpfile())['uri'];

        $this->assertTrue(
            openssl_pkcs7_decrypt(
                $encryptedFile,
                $decryptedFile,
                "file://$this->keyPath/encrypt.crt",
                "file://$this->keyPath/encrypt.key",
            ),
            sprintf('Decryption of the message failed. Internal error "%s".', openssl_error_string()),
        );

        if (file_exists($encryptedFile)) {
            unlink($encryptedFile);
        }

        if (file_exists($decryptedFile)) {
            unlink($decryptedFile);
        }
    }
}
