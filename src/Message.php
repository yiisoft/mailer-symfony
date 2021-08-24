<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;
use Throwable;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MessageInterface;

use function is_string;

/**
 * Message implements a message class based on Symfony Mailer.
 *
 * @see https://symfony.com/doc/current/mailer.html#creating-sending-messages
 * @see Mailer
 */
final class Message implements MessageInterface
{
    private Email $email;
    private ?Throwable $error = null;
    private string $charset = 'utf-8';

    public function __construct()
    {
        $this->email = new Email();
    }

    public function __clone()
    {
        $this->email = clone $this->email;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function withCharset(string $charset): self
    {
        $new = clone $this;
        $new->charset = $charset;
        return $new;
    }

    public function getFrom()
    {
        return $this->convertAddressesToStrings($this->email->getFrom());
    }

    public function withFrom($from): self
    {
        $new = clone $this;
        $new->email->from(...$this->convertStringsToAddresses($from));
        return $new;
    }

    public function getTo()
    {
        return $this->convertAddressesToStrings($this->email->getTo());
    }

    public function withTo($to): self
    {
        $new = clone $this;
        $new->email->to(...$this->convertStringsToAddresses($to));
        return $new;
    }

    public function getReplyTo()
    {
        return $this->convertAddressesToStrings($this->email->getReplyTo());
    }

    public function withReplyTo($replyTo): self
    {
        $new = clone $this;
        $new->email->replyTo(...$this->convertStringsToAddresses($replyTo));
        return $new;
    }

    public function getCc()
    {
        return $this->convertAddressesToStrings($this->email->getCc());
    }

    public function withCc($cc): self
    {
        $new = clone $this;
        $new->email->cc(...$this->convertStringsToAddresses($cc));
        return $new;
    }

    public function getBcc()
    {
        return $this->convertAddressesToStrings($this->email->getBcc());
    }

    public function withBcc($bcc): self
    {
        $new = clone $this;
        $new->email->bcc(...$this->convertStringsToAddresses($bcc));
        return $new;
    }

    public function getSubject(): string
    {
        return (string) $this->email->getSubject();
    }

    public function withSubject(string $subject): self
    {
        $new = clone $this;
        $new->email->subject($subject);
        return $new;
    }

    public function getTextBody(): string
    {
        return (string) $this->email->getTextBody();
    }

    public function withTextBody(string $text): self
    {
        $new = clone $this;
        $new->email->text($text, $this->charset);
        return $new;
    }

    public function getHtmlBody(): string
    {
        return (string) $this->email->getHtmlBody();
    }

    public function withHtmlBody(string $html): self
    {
        $new = clone $this;
        $new->email->html($html, $this->charset);
        return $new;
    }

    public function withAttached(File $file): self
    {
        $new = clone $this;

        $file->path() === null
            ? $new->email->attach((string) $file->content(), $file->name(), $file->contentType())
            : $new->email->attachFromPath($file->path(), $file->name(), $file->contentType())
        ;

        return $new;
    }

    public function withEmbedded(File $file): self
    {
        $new = clone $this;

        $file->path() === null
            ? $new->email->embed((string) $file->content(), $file->name(), $file->contentType())
            : $new->email->embedFromPath($file->path(), $file->name(), $file->contentType())
        ;

        return $new;
    }

    public function getHeader(string $name): array
    {
        $headers = $this->email->getHeaders();

        if (!$headers->has($name)) {
            return [];
        }

        $values = [];

        /** @var HeaderInterface $header */
        foreach ($headers->all($name) as $header) {
            $values[] = $header->getBodyAsString();
        }

        return $values;
    }

    public function withAddedHeader(string $name, string $value): self
    {
        $new = clone $this;
        $new->email->getHeaders()->addTextHeader($name, $value);
        return $new;
    }

    public function withHeader(string $name, $value): self
    {
        $new = clone $this;
        $headers = $new->email->getHeaders();

        if ($headers->has($name)) {
            $headers->remove($name);
        }

        foreach ((array) $value as $v) {
            $headers->addTextHeader($name, $v);
        }

        return $new;
    }

    public function withHeaders(array $headers): self
    {
        $new = clone $this;

        foreach ($headers as $name => $value) {
            $new = $new->withHeader($name, $value);
        }

        return $new;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function withError(Throwable $e): self
    {
        $new = clone $this;
        $new->error = $e;
        return $new;
    }

    public function __toString(): string
    {
        return $this->email->toString();
    }

    /**
     * Returns a Symfony email instance.
     *
     * @return Email Symfony email instance.
     */
    public function getSymfonyEmail(): Email
    {
        return $this->email;
    }

    /**
     * Returns the date when the message was sent, or null if it was not set.
     *
     * @return DateTimeImmutable|null The date when the message was sent.
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->email->getDate();
    }

    /**
     * Returns a new instance with the specified date when the message was sent.
     *
     * @param DateTimeInterface $date The date when the message was sent.
     *
     * @return self
     */
    public function withDate(DateTimeInterface $date): self
    {
        $new = clone $this;
        $new->email->date($date);
        return $new;
    }

    /**
     * Returns the priority of this message.
     *
     * @return int The priority value as integer in range: `1..5`,
     * where 1 is the highest priority and 5 is the lowest.
     */
    public function getPriority(): int
    {
        return $this->email->getPriority();
    }

    /**
     * Returns a new instance with the specified priority of this message.
     *
     * @param int $priority The priority value, should be an integer in range: `1..5`,
     * where 1 is the highest priority and 5 is the lowest.
     *
     * @return self
     */
    public function withPriority(int $priority): self
    {
        $new = clone $this;
        $new->email->priority($priority);
        return $new;
    }

    /**
     * Returns the return-path (the bounce address) of this message.
     *
     * @return string The bounce email address.
     */
    public function getReturnPath(): string
    {
        $returnPath = $this->email->getReturnPath();
        return $returnPath === null ? '' : $returnPath->getAddress();
    }

    /**
     * Returns a new instance with the specified return-path (the bounce address) of this message.
     *
     * @param string $address The bounce email address.
     *
     * @return self
     */
    public function withReturnPath(string $address): self
    {
        $new = clone $this;
        $new->email->returnPath($address);
        return $new;
    }

    /**
     * Returns the message actual sender email address.
     *
     * @return string The actual sender email address.
     */
    public function getSender(): string
    {
        $sender = $this->email->getSender();
        return $sender === null ? '' : $sender->getAddress();
    }

    /**
     * Returns a new instance with the specified actual sender email address.
     *
     * @param string $address The actual sender email address.
     *
     * @return self
     */
    public function withSender(string $address): self
    {
        $new = clone $this;
        $new->email->sender($address);
        return $new;
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param Address[] $addresses
     *
     * @return string|array<string, string>
     */
    private function convertAddressesToStrings(array $addresses)
    {
        $strings = [];

        foreach ($addresses as $address) {
            $strings[$address->getAddress()] = $address->getName();
        }

        return empty($strings) ? '' : $strings;
    }

    /**
     * Converts string representations of address to their instances.
     *
     * @param string|array<int|string, string> $strings
     *
     * @return Address[]
     */
    private function convertStringsToAddresses($strings): array
    {
        if (is_string($strings)) {
            return [new Address($strings)];
        }

        $addresses = [];

        foreach ($strings as $address => $name) {
            if (!is_string($address)) {
                // email address without name
                $addresses[] = new Address($name);
                continue;
            }

            $addresses[] = new Address($address, $name);
        }

        return $addresses;
    }
}
