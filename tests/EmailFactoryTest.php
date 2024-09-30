<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use DateTimeImmutable;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\Message;
use Yiisoft\Mailer\Symfony\EmailFactory;

final class EmailFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testBase(): void
    {
        $message = new Message(
            from: 'yii@example.com',
            to: ['mark@example.com', 'vasya@example.com'],
            replyTo: 'den@example.com',
            cc: ['boss@example.com' => 'Big Boss'],
            bcc: 'kate@example.com',
            subject: 'Important letter',
            date: new DateTimeImmutable('2024-09-27 11:03:17'),
            priority: 4,
            returnPath: 'bounce@example.com',
            sender: 'hosting@example.com',
            textBody: 'Hello =)',
            htmlBody: '<b>Hello =)</b>',
            attachments: [
                File::fromContent('test', 'yii.txt'),
            ],
            embeddings: [
                File::fromPath(path: __DIR__ . '/TestAsset/yii.png'),
            ],
            headers: [
                'X-Test' => ['new', 'tourist'],
                'X-Spam' => ['0'],
            ],
        );

        $email = (new EmailFactory())->create($message);

        $this->assertCount(1, $email->getFrom());
        $this->assertSame('yii@example.com', $email->getFrom()[0]->toString());
        $this->assertCount(2, $email->getTo());
        $this->assertSame('mark@example.com', $email->getTo()[0]->toString());
        $this->assertSame('vasya@example.com', $email->getTo()[1]->toString());
        $this->assertCount(1, $email->getReplyTo());
        $this->assertSame('den@example.com', $email->getReplyTo()[0]->toString());
        $this->assertCount(1, $email->getCc());
        $this->assertSame('"Big Boss" <boss@example.com>', $email->getCc()[0]->toString());
        $this->assertCount(1, $email->getBcc());
        $this->assertSame('kate@example.com', $email->getBcc()[0]->toString());
        $this->assertSame('Important letter', $email->getSubject());
        $this->assertSame('2024-09-27 11:03:17', $email->getDate()?->format('Y-m-d H:i:s'));
        $this->assertSame(4, $email->getPriority());
        $this->assertSame('bounce@example.com', $email->getReturnPath()?->toString());
        $this->assertSame('hosting@example.com', $email->getSender()?->toString());
        $this->assertSame('Hello =)', $email->getTextBody());
        $this->assertSame('<b>Hello =)</b>', $email->getHtmlBody());

        $headers = $email->getHeaders()->toArray();
        $this->assertContains('X-Test: new', $headers);
        $this->assertContains('X-Test: tourist', $headers);
        $this->assertContains('X-Spam: 0', $headers);

        $attachments = $email->getAttachments();
        $this->assertCount(2, $attachments);
        $this->assertSame('yii.txt', $attachments[0]->getFilename());
        $this->assertSame('yii.png', $attachments[1]->getFilename());
    }
}
