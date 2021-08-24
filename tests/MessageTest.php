<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\Symfony\Message;

use function basename;
use function file_get_contents;
use function function_exists;
use function serialize;
use function substr_count;
use function unserialize;

final class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        parent::setUp();
        $this->message = new Message();
    }

    public function testSubject(): void
    {
        $subject = 'Test subject';
        $message = $this->message->withSubject($subject);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($subject, $message->getSubject());
    }

    public function charsetDataProvider(): array
    {
        return [['utf-8'], ['iso-8859-2']];
    }

    /**
     * @dataProvider charsetDataProvider
     *
     * @param string $charset
     */
    public function testCharset(string $charset): void
    {
        $message = $this->message->withCharset($charset);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($charset, $message->getCharset());
    }

    public function addressesDataProvider(): array
    {
        return [
            [
                'foo@example.com',
                ['foo@example.com' => ''],
            ],
            [
                ['foo@example.com', 'bar@example.com'],
                ['foo@example.com' => '', 'bar@example.com' => ''],
            ],
            [
                ['foo@example.com' => 'foo'],
                ['foo@example.com' => 'foo'],
            ],
            [
                ['foo@example.com' => 'foo', 'bar@example.com' => 'bar'],
                ['foo@example.com' => 'foo', 'bar@example.com' => 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider addressesDataProvider
     *
     * @param array|string $from
     * @param array $expected
     */
    public function testFrom($from, array $expected): void
    {
        $message = $this->message->withFrom($from);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getFrom());
    }

    /**
     * @dataProvider addressesDataProvider
     *
     * @param array|string $to
     * @param array $expected
     */
    public function testTo($to, array $expected): void
    {
        $message = $this->message->withTo($to);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getTo());
    }

    /**
     * @dataProvider addressesDataProvider
     *
     * @param array|string $replyTo
     * @param array $expected
     */
    public function testReplyTo($replyTo, array $expected): void
    {
        $message = $this->message->withReplyTo($replyTo);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getReplyTo());
    }

    /**
     * @dataProvider addressesDataProvider
     *
     * @param array|string $cc
     * @param array $expected
     */
    public function testCc($cc, array $expected): void
    {
        $message = $this->message->withCc($cc);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getCc());
    }

    /**
     * @dataProvider addressesDataProvider
     *
     * @param array|string $bcc
     * @param array $expected
     */
    public function testBcc($bcc, array $expected): void
    {
        $message = $this->message->withBcc($bcc);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getBcc());
    }

    public function testDate(): void
    {
        $date = new DateTime();
        $message = $this->message->withDate($date);

        $this->assertNotSame($message, $this->message);
        $this->assertNotSame($date, $message->getDate());
        $this->assertInstanceOf(DateTimeImmutable::class, $message->getDate());
        $this->assertSame($date->getTimestamp(), $message->getDate()->getTimestamp());
    }

    public function priorityDataProvider(): array
    {
        return [
            [Email::PRIORITY_HIGHEST],
            [Email::PRIORITY_HIGH],
            [Email::PRIORITY_NORMAL],
            [Email::PRIORITY_LOW],
            [Email::PRIORITY_LOWEST],
        ];
    }

    /**
     * @dataProvider priorityDataProvider
     *
     * @param int $priority
     */
    public function testPriority(int $priority): void
    {
        $message = $this->message->withPriority($priority);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($priority, $message->getPriority());
    }

    public function testReturnPath(): void
    {
        $address = 'foo@exmaple.com';
        $message = $this->message->withReturnPath($address);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($address, $message->getReturnPath());
    }

    public function testSender(): void
    {
        $address = 'foo@exmaple.com';
        $message = $this->message->withSender($address);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($address, $message->getSender());
    }

    public function headerDataProvider(): array
    {
        return [
            ['X-Foo', 'Bar', ['Bar']],
            ['X-Fuzz', ['Bar', 'Baz'], ['Bar', 'Baz']],
        ];
    }

    /**
     * @dataProvider headerDataProvider
     *
     * @param string $name
     * @param array|string $value
     * @param array $expected
     */
    public function testHeader(string $name, $value, array $expected): void
    {
        $message = $this->message->withHeader($name, $value);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getHeader($name));
    }

    /**
     * @dataProvider headerDataProvider
     *
     * @param string $name
     * @param array|string $value
     * @param array $expected
     */
    public function testHeaders(string $name, $value, array $expected): void
    {
        $message = $this->message->withHeaders([$name => $value]);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($expected, $message->getHeader($name));
    }

    public function testHeadersWithNoStringValues(): void
    {
        $date = new DateTimeImmutable();
        $sender = 'sender@example.com';
        $message = $this->message->withDate($date)->withSender($sender);

        $this->assertNotSame($message, $this->message);
        $this->assertSame([$sender], $message->getHeader('sEndEr'));
        $this->assertSame([$date->format(DateTimeInterface::RFC2822)], $message->getHeader('dAtE'));
    }

    public function testTextBody(): void
    {
        $body = 'Plain text';
        $message = $this->message->withTextBody($body);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($body, $message->getTextBody());
    }

    public function testHtmlBody(): void
    {
        $body = '<p>HTML content</p>';
        $message = $this->message->withHtmlBody($body);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($body, $message->getHtmlBody());
    }

    public function testError(): void
    {
        $error = new RuntimeException('Some error.');
        $message = $this->message->withError($error);

        $this->assertNotSame($message, $this->message);
        $this->assertSame($error, $message->getError());
    }

    public function testToString(): void
    {
        $string = $this->message
            ->withCharset($charset = 'utf-16')
            ->withSubject($subject = 'Test Subject')
            ->withFrom($from = 'from@example.com')
            ->withReplyTo($replyTo = 'reply-to@example.com')
            ->withTo($to = 'to@example.com')
            ->withCc($cc = 'cc@example.com')
            ->withPriority($priority = Email::PRIORITY_HIGH)
            ->withReturnPath($returnPath = 'bounce@example.com')
            ->withSender($sender = 'sender@example.com')
            ->withTextBody($text = 'Plain text')
            ->withHtmlBody($html = '<p>HTML content</p>')
            ->__toString()
        ;

        $this->assertStringContainsString("charset=$charset", $string, 'Incorrect charset!');
        $this->assertStringContainsString("Subject: $subject", $string, 'Incorrect "Subject" header!');
        $this->assertStringContainsString("From: $from", $string, 'Incorrect "From" header!');
        $this->assertStringContainsString("Reply-To: $replyTo", $string, 'Incorrect "Reply-To" header!');
        $this->assertStringContainsString("To: $to", $string, 'Incorrect "To" header!');
        $this->assertStringContainsString("Cc: $cc", $string, 'Incorrect "Cc" header!');
        $this->assertStringContainsString("X-Priority: $priority (High)", $string, 'Incorrect "Priority" header!');
        $this->assertStringContainsString("Return-Path: <$returnPath>", $string, 'Incorrect "Return-Path" header!');
        $this->assertStringContainsString("Sender: $sender", $string, 'Incorrect "Sender" header!');
        $this->assertStringContainsString($text, $string, 'Incorrect "Text" body!');
        $this->assertStringContainsString("Content-Type: text/plain", $string, 'Incorrect "Text" content type!');
        $this->assertStringContainsString($html, $string, 'Incorrect "Html" body!');
        $this->assertStringContainsString("Content-Type: text/html", $string, 'Incorrect "Html" content type!');
    }

    public function testHeadersAndToString(): void
    {
        $string = $this->message
            ->withAddedHeader('Some', 'foo')
            ->withAddedHeader('Multiple', 'value1')
            ->withAddedHeader('Multiple', 'value2')
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withTextBody('Text')
            ->__toString()
        ;

        $this->assertStringContainsString('Some: foo', $string, 'Unable to add header!');
        $this->assertStringContainsString('Multiple: value1', $string, 'First value of multiple header lost!');
        $this->assertStringContainsString('Multiple: value2', $string, 'Second value of multiple header lost!');

        $string = $this->message
            ->withHeader('Some', 'foo')
            ->withHeader('Some', 'override')
            ->withHeader('Multiple', ['value1', 'value2'])
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withTextBody('Text')
            ->__toString()
        ;

        $this->assertStringContainsString('Some: override', $string, 'Unable to set header!');
        $this->assertStringNotContainsString('Some: foo', $string, 'Unable to override header!');
        $this->assertStringContainsString('Multiple: value1', $string, 'First value of multiple header lost!');
        $this->assertStringContainsString('Multiple: value2', $string, 'Second value of multiple header lost!');
    }

    public function testSerialize(): void
    {
        $message = $this->message
            ->withTo('to@example.com')
            ->withFrom('from@example.com')
            ->withSubject('Alternative Body Test')
            ->withTextBody('Test plain text body')
        ;

        $this->assertNotSame($this->message, $message);

        $serializedMessage = serialize($message);
        $this->assertNotEmpty($serializedMessage, 'Unable to serialize message!');

        $unserializedMessaage = unserialize($serializedMessage);
        $this->assertEquals($message, $unserializedMessaage, 'Unable to unserialize message!');
    }

    public function testAlternativeBodyCharset(): void
    {
        $charset = 'windows-1251';
        $message = $this->message
            ->withCharset($charset)
            ->withFrom('from@example.com')
            ->withTo('to@example.com')
            ->withTextBody('some text')
            ->withHtmlBody('some html')
        ;

        $this->assertNotSame($this->message, $message);
        $this->assertSame(2, substr_count((string) $message, $charset), 'Wrong charset for alternative body.');

        $message = $message->withTextBody('some text override');
        $this->assertSame(2, substr_count((string) $message, $charset), 'Wrong charset for alternative body override.');
    }

    public function testAttachFile(): void
    {
        $file = File::fromPath(__FILE__, 'test.php', 'application/x-php');

        $message = $this->message
            ->withTo('to@example.com')
            ->withFrom('from@example.com')
            ->withSubject('Attach File Test')
            ->withTextBody('Attach File Test body')
            ->withAttached($file)
        ;

        $this->assertNotSame($this->message, $message);
        $this->assertAttachment($message, $file, false);
    }

    public function testAttachContent(): void
    {
        $file = File::fromContent('Test attachment content', 'test.txt', 'text/plain');

        $message = $this->message
            ->withTo('to@example.com')
            ->withFrom('from@example.com')
            ->withSubject('Attach Content Test')
            ->withTextBody('Attach Content Test body')
            ->withAttached($file)
        ;

        $this->assertNotSame($this->message, $message);
        $this->assertAttachment($message, $file, true);
    }

    public function testEmbedFile(): void
    {
        $path = $this->createImageFile('embed-file.png', 'Embed Image File');
        $file = File::fromPath($path, basename($path), 'image/png');

        $message = $this->message
            ->withTo('to@example.com')
            ->withFrom('from@example.com')
            ->withSubject('Embed File Test')
            ->withHtmlBody('Embed image: <img src="' . $file->cid() . '" alt="pic">')
            ->withEmbedded($file)
        ;

        $this->assertNotSame($this->message, $message);
        $this->assertAttachment($message, $file, false);
    }

    public function testEmbedContent(): void
    {
        $path = $this->createImageFile('embed-file.png', 'Embed Image File');
        $file = File::fromContent(file_get_contents($path), basename($path), 'image/png');

        $message = $this->message
            ->withTo('to@example.com')
            ->withFrom('from@example.com')
            ->withSubject('Embed Content Test')
            ->withHtmlBody('Embed image: <img src="' . $file->cid() . '" alt="pic">')
            ->withEmbedded($file)
        ;

        $this->assertNotSame($this->message, $message);
        $this->assertAttachment($message, $file, true);
    }

    public function testDefaultGetters(): void
    {
        $this->assertSame('utf-8', $this->message->getCharset());
        $this->assertSame('', $this->message->getFrom());
        $this->assertSame('', $this->message->getTo());
        $this->assertSame('', $this->message->getReplyTo());
        $this->assertSame('', $this->message->getCc());
        $this->assertSame('', $this->message->getBcc());
        $this->assertSame('', $this->message->getSubject());
        $this->assertSame('', $this->message->getTextBody());
        $this->assertSame('', $this->message->getHtmlBody());
        $this->assertSame([], $this->message->getHeader('header'));
        $this->assertNull($this->message->getError());
        $this->assertInstanceOf(Email::class, $this->message->getSymfonyEmail());
        $this->assertNull($this->message->getDate());
        $this->assertSame(Email::PRIORITY_NORMAL, $this->message->getPriority());
        $this->assertSame('', $this->message->getReturnPath());
        $this->assertSame('', $this->message->getSender());
    }

    public function testImmutability(): void
    {
        $file = File::fromContent('Test attachment content', 'test.txt', 'text/plain');

        $this->assertNotSame($this->message, $this->message->withCharset('utf-8'));
        $this->assertNotSame($this->message, $this->message->withFrom('from@example.com'));
        $this->assertNotSame($this->message, $this->message->withTo('to@example.com'));
        $this->assertNotSame($this->message, $this->message->withReplyTo('reply-to@example.com'));
        $this->assertNotSame($this->message, $this->message->withCc('cc@example.com'));
        $this->assertNotSame($this->message, $this->message->withBcc('bcc@example.com'));
        $this->assertNotSame($this->message, $this->message->withSubject('subject'));
        $this->assertNotSame($this->message, $this->message->withTextBody('text'));
        $this->assertNotSame($this->message, $this->message->withHtmlBody('html'));
        $this->assertNotSame($this->message, $this->message->withAttached($file));
        $this->assertNotSame($this->message, $this->message->withEmbedded($file));
        $this->assertNotSame($this->message, $this->message->withAddedHeader('name', 'value'));
        $this->assertNotSame($this->message, $this->message->withHeader('name', 'value'));
        $this->assertNotSame($this->message, $this->message->withHeaders([]));
        $this->assertNotSame($this->message, $this->message->withError(new RuntimeException()));
        $this->assertNotSame($this->message, $this->message->withDate(new DateTime()));
        $this->assertNotSame($this->message, $this->message->withPriority(Email::PRIORITY_NORMAL));
        $this->assertNotSame($this->message, $this->message->withReturnPath('bounce@example.com'));
        $this->assertNotSame($this->message, $this->message->withSender('sender@example.com'));
    }

    private function assertAttachment(Message $message, File $file, bool $checkContent): void
    {
        $attachment = $message->getSymfonyEmail()->getAttachments()[0];

        $this->assertInstanceOf(DataPart::class, $attachment);

        if ($checkContent) {
            $this->assertSame($file->content(), $attachment->getBody(), 'Invalid content!');
        }

        $this->assertSame(
            $file->name(),
            $this->getInaccessibleProperty($attachment, 'name'),
            'Invalid file name!',
        );

        $this->assertSame(
            $file->contentType(),
            "{$attachment->getMediaType()}/{$attachment->getMediaSubtype()}",
            'Invalid content type!',
        );
    }

    private function createImageFile(string $fileName = 'test.png', string $text = 'Test Image'): string
    {
        if (!function_exists('imagepng')) {
            $this->markTestSkipped('GD lib required.');
        }

        $fileFullName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $fileName;
        $image = \imagecreatetruecolor(120, 20);

        if ($image === false) {
            throw new RuntimeException('Unable create a new true color image.');
        }

        $textColor = \imagecolorallocate($image, 233, 14, 91);
        \imagestring($image, 1, 5, 5, $text, $textColor);
        \imagepng($image, $fileFullName);
        \imagedestroy($image);

        return $fileFullName;
    }
}
