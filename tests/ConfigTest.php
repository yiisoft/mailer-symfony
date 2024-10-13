<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Symfony\Mailer;
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

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($params)
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
