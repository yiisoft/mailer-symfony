<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use RuntimeException;
use stdClass;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageInterface;
use Yiisoft\Mailer\Symfony\Message;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function openssl_pkcs7_decrypt;
use function sprintf;
use function str_replace;
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

        $this->assertSame([$this->message->getSymfonyEmail()], $transport->getSentMessages());
    }

    public function testSendFailureForNotSetSubject(): void
    {
        $mailer = $this->get(MailerInterface::class);

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Subject is required.');

        $mailer->send((new Message())
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withTextBody('Test Body')
        );
    }

    public function testSendFailureForMessageIsNotSymfonyMessageInstance(): void
    {
        $mailer = $this->get(MailerInterface::class);
        $mock = $this->createMock(MessageInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The message must be an instance of "%s". The "%s" instance is received.',
            Message::class,
            get_class($mock),
        ));

        $mailer->send($mock);
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
        $mailer = $this->get(MailerInterface::class)->withSigner(new DkimSigner(
            file_get_contents("$this->keyPath/sign.key"),
            'example.com',
            'default',
        ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $mailer->send($this->message);
        $sentMessage = $this->get(TransportInterface::class)->getSentMessages()[0];

        $this->assertNotSame($this->message->getSymfonyEmail(), $sentMessage);
        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertTrue($sentMessage->getHeaders()->has('DKIM-Signature'));
        $this->assertSame($this->message->getTextBody(), $sentMessage->getBody()->bodyToString());
    }

    public function testSendWithSMimeSigner(): void
    {
        $mailer = $this->get(MailerInterface::class)->withSigner(new SMimeSigner(
            "$this->keyPath/sign.crt",
            "$this->keyPath/sign.key",
        ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $mailer->send($this->message);
        $sentMessage = $this->get(TransportInterface::class)->getSentMessages()[0];

        $this->assertNotSame($this->message->getSymfonyEmail(), $sentMessage);
        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertStringContainsString('S/MIME', $sentMessage->getBody()->bodyToString());
        $this->assertStringContainsString($this->message->getTextBody(), $sentMessage->getBody()->bodyToString());
    }

    public function testSendWithEncryptor(): void
    {
        $mailer = $this->get(MailerInterface::class)->withEncryptor(new SMimeEncrypter(
            "$this->keyPath/encrypt.crt",
        ));

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $this->message->getSymfonyEmail()->getHeaders()->addIdHeader('Message-ID', 'some-id@example.com');

        $mailer->send($this->message);
        $sentMessage = $this->get(TransportInterface::class)->getSentMessages()[0];

        $this->assertNotSame($this->message->getSymfonyEmail(), $sentMessage);
        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertSentMessageIsEncryptedProperly($sentMessage);
    }

    public function testSendWithEncryptorAndWithSigner(): void
    {
        $mailer = $this->get(MailerInterface::class)
            ->withEncryptor(new SMimeEncrypter("$this->keyPath/encrypt.crt"))
            ->withSigner(new DkimSigner(
                file_get_contents("$this->keyPath/sign.key"),
                'example.com',
                'default',
            ))
        ;

        $this->assertNotSame($this->get(MailerInterface::class), $mailer);

        $this->message->getSymfonyEmail()->getHeaders()->addIdHeader('Message-ID', 'some-id@example.com');

        $mailer->send($this->message);
        $sentMessage = $this->get(TransportInterface::class)->getSentMessages()[0];

        $this->assertSentMessageIsEncryptedProperly($sentMessage);
        $this->assertNotSame($this->message->getSymfonyEmail(), $sentMessage);
        $this->assertInstanceOf(SymfonyMessage::class, $sentMessage);
        $this->assertTrue($sentMessage->getHeaders()->has('DKIM-Signature'));
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

        $this->assertEquals(
            str_replace("\r", '', (string) $this->message),
            str_replace("\r", '', file_get_contents($decryptedFile)),
        );

        if (file_exists($encryptedFile)) {
            unlink($encryptedFile);
        }

        if (file_exists($decryptedFile)) {
            unlink($decryptedFile);
        }
    }
}
