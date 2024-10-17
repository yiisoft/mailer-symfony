<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;
use Yiisoft\Mailer\Priority;
use Yiisoft\Mailer\Symfony\Mailer;
use Yiisoft\Mailer\Symfony\Tests\TestAsset\DummyTransport;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

use function dirname;

final class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $transport = $container->get(TransportInterface::class);
        $mailer = $container->get(MailerInterface::class);

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function testMessageSetting(): void
    {
        $params = $this->getParams();
        $date = new DateTimeImmutable();
        $file1 = File::fromContent('1');
        $file2 = File::fromContent('2');
        $file3 = File::fromContent('3');
        $file4 = File::fromContent('4');
        $params['yiisoft/mailer-symfony']['messageSettings'] = [
            'charset' => 'utf-8',
            'from' => 'from@example.com',
            'addFrom' => 'from-add@example.com',
            'to' => 'to@example.com',
            'addTo' => 'to-add@example.com',
            'replyTo' => 'reply-to@example.com',
            'addReplyTo' => 'reply-to-add@example.com',
            'cc' => 'cc@example.com',
            'addCc' => 'cc-add@example.com',
            'bcc' => 'bcc@example.com',
            'addBcc' => 'bcc-add@example.com',
            'subject' => 'Test',
            'date' => $date,
            'priority' => Priority::HIGH,
            'returnPath' => 'return@example.com',
            'sender' => 'sender@example.com',
            'textBody' => 'my-text',
            'htmlBody' => '<b>Hello</b>',
            'attachments' => [$file1],
            'addAttachments' => [$file2],
            'embeddings' => [$file3],
            'addEmbeddings' => [$file4],
            'headers' => ['X-Test' => 'on'],
            'overwriteHeaders' => ['X-Pass' => 'true'],
            'convertHtmlToText' => false,
        ];
        $transport = new DummyTransport();
        $container = $this->createContainer($params, $transport);

        $mailer = $container->get(MailerInterface::class);
        $mailer->send(new Message());

        $messages = $transport->getSentMessages();
        $this->assertCount(1, $messages);

        /** @var Email $message */
        $message = $messages[0];
        $this->assertSame('utf-8', $message->getHtmlCharset());
        $this->assertSame('utf-8', $message->getTextCharset());
        $this->assertCount(2, $message->getFrom());
        $this->assertSame('from@example.com', $message->getFrom()[0]->getAddress());
        $this->assertSame('from-add@example.com', $message->getFrom()[1]->getAddress());
        $this->assertCount(2, $message->getTo());
        $this->assertSame('to@example.com', $message->getTo()[0]->getAddress());
        $this->assertSame('to-add@example.com', $message->getTo()[1]->getAddress());
        $this->assertCount(2, $message->getReplyTo());
        $this->assertSame('reply-to@example.com', $message->getReplyTo()[0]->getAddress());
        $this->assertSame('reply-to-add@example.com', $message->getReplyTo()[1]->getAddress());
        $this->assertCount(2, $message->getCc());
        $this->assertSame('cc@example.com', $message->getCc()[0]->getAddress());
        $this->assertSame('cc-add@example.com', $message->getCc()[1]->getAddress());
        $this->assertCount(2, $message->getBcc());
        $this->assertSame('bcc@example.com', $message->getBcc()[0]->getAddress());
        $this->assertSame('bcc-add@example.com', $message->getBcc()[1]->getAddress());
        $this->assertSame('Test', $message->getSubject());
        $this->assertSame($date->getTimestamp(), $message->getDate()->getTimestamp());
        $this->assertSame(Priority::HIGH->value, $message->getPriority());
        $this->assertSame('return@example.com', $message->getReturnPath()?->getAddress());
        $this->assertSame('sender@example.com', $message->getSender()?->getAddress());
        $this->assertSame('my-text', $message->getTextBody());
        $this->assertSame('<b>Hello</b>', $message->getHtmlBody());
        $this->assertCount(4, $message->getAttachments());
        $this->assertSame($file1->content(), $message->getAttachments()[0]->getBody());
        $this->assertSame($file2->content(), $message->getAttachments()[1]->getBody());
        $this->assertSame($file3->content(), $message->getAttachments()[2]->getBody());
        $this->assertSame($file4->content(), $message->getAttachments()[3]->getBody());

        $headers = $message->getHeaders();
        $this->assertSame('on', $headers->getHeaderBody('X-Test'));
        $this->assertSame('true', $headers->getHeaderBody('X-Pass'));
    }

    public function testMessageSettingWithHtmlToTextBodyConverter(): void
    {
        $params = $this->getParams();
        $params['yiisoft/mailer-symfony']['messageSettings'][ 'convertHtmlToText'] = true;
        $transport = new DummyTransport();
        $container = $this->createContainer($params, $transport);

        $mailer = $container->get(MailerInterface::class);
        $mailer->send(
            new Message(
                subject: 'Notice',
                htmlBody: '<h1>Hello!</h1>',
            )
        );

        $messages = $transport->getSentMessages();
        $this->assertCount(1, $messages);

        /** @var Email $message */
        $message = $messages[0];
        $this->assertSame('Hello!', $message->getTextBody());
    }

    private function createContainer(?array $params = null, ?TransportInterface $transport = null): Container
    {
        $config = $this->getDiConfig($params)
            +
            [
                EventDispatcherInterface::class => new SimpleEventDispatcher(),
                Aliases::class => [
                    '__construct()' => [
                        [
                            'resources' => __DIR__ . '/environment/resources',
                            'runtime' => __DIR__ . '/environment/runtime',
                        ],
                    ],
                ],
            ];

        if ($transport !== null) {
            $config[TransportInterface::class] = $transport;
        }

        return new Container(
            ContainerConfig::create()->withDefinitions($config)
        );
    }

    private function getDiConfig(?array $params = null): array
    {
        if ($params === null) {
            $params = $this->getParams();
        }
        return require dirname(__DIR__) . '/config/di.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
