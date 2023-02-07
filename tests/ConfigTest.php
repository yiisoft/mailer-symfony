<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Mailer\FileMailer;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageFactory;
use Yiisoft\Mailer\MessageFactoryInterface;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\View;

final class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $messageBodyRenderer = $container->get(MessageBodyRenderer::class);
        $messageFactory = $container->get(MessageFactoryInterface::class);
        $transport = $container->get(TransportInterface::class);
        $fileMailer = $container->get(FileMailer::class);
        $mailer = $container->get(MailerInterface::class);

        $this->assertInstanceOf(MessageBodyRenderer::class, $messageBodyRenderer);
        $this->assertInstanceOf(MessageFactory::class, $messageFactory);
        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertInstanceOf(FileMailer::class, $fileMailer);
        $this->assertInstanceOf(FileMailer::class, $mailer);
    }

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($params)
                +
                [
                    View::class => ['__construct()' => [__DIR__]],
                    EventDispatcherInterface::class => new SimpleEventDispatcher(),
                    Aliases::class => [
                        '__construct()' => [
                            [
                                'resources' => __DIR__ . '/environment/resources',
                                'runtime' => __DIR__ . '/environment/runtime',
                            ]
                        ],
                    ],
                ]
            )
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
