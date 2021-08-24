<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony\Tests\TestAsset;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

final class DummyTransport implements TransportInterface
{
    /**
     * @var RawMessage[]
     */
    private array $sentMessages = [];

    /**
     * @return RawMessage[]
     */
    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        if ($message instanceof Email && empty($message->getSubject())) {
            throw new TransportException('Subject is required.');
        }

        $this->sentMessages[] = $message;
        return null;
    }

    public function __toString(): string
    {
        return self::class;
    }
}
