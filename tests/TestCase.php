<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageBodyTemplate;
use Yiisoft\Mailer\Symfony\Mailer;
use Yiisoft\Mailer\Symfony\Tests\TestAsset\DummyTransport;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\View;

use function basename;
use function str_replace;
use function sys_get_temp_dir;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ContainerInterface $container = null;

    protected function setUp(): void
    {
        FileHelper::ensureDirectory($this->getTestFilePath());
        $this->container();
    }

    protected function tearDown(): void
    {
        $this->container = null;
        FileHelper::removeDirectory($this->getTestFilePath());
    }

    protected function get(string $id)
    {
        return $this
            ->container()
            ->get($id);
    }

    protected function getTestFilePath(): string
    {
        return sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . basename(str_replace('\\', '_', static::class))
        ;
    }

    /**
     * Gets an inaccessible object property.
     *
     * @return mixed
     */
    protected function getInaccessibleProperty(object $object, string $propertyName)
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        $property->setAccessible(false);

        return $result;
    }

    private function container(): ContainerInterface
    {
        if ($this->container === null) {
            $tempDir = $this->getTestFilePath();
            $eventDispatcher = new SimpleEventDispatcher();
            $view = new View($tempDir, $eventDispatcher);
            $messageBodyTemplate = new MessageBodyTemplate($tempDir);
            $messageBodyRenderer = new MessageBodyRenderer($view, $messageBodyTemplate);
            $transport = new DummyTransport();
            $mailer = new Mailer($messageBodyRenderer, $transport, eventDispatcher: $eventDispatcher);

            $this->container = new SimpleContainer([
                EventDispatcherInterface::class => $eventDispatcher,
                MailerInterface::class => $mailer,
                MessageBodyRenderer::class => $messageBodyRenderer,
                MessageBodyTemplate::class => $messageBodyTemplate,
                TransportInterface::class => $transport,
            ]);
        }

        return $this->container;
    }
}
