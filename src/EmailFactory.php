<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony;

use DateTimeImmutable;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Yiisoft\Mailer\MessageInterface;

/**
 * @internal
 */
final class EmailFactory
{
    public function create(MessageInterface $message): Email
    {
        $email = (new Email())
            ->from(...$this->convertStringsToAddresses($message->getFrom()))
            ->to(...$this->convertStringsToAddresses($message->getTo()))
            ->replyTo(...$this->convertStringsToAddresses($message->getReplyTo()))
            ->cc(...$this->convertStringsToAddresses($message->getCc()))
            ->bcc(...$this->convertStringsToAddresses($message->getBcc()))
            ->subject($message->getSubject())
            ->priority($message->getPriority())
            ->text($message->getTextBody(), $message->getCharset())
            ->html($message->getHtmlBody(), $message->getCharset());

        $returnPath = $message->getReturnPath();
        if ($returnPath !== '') {
            $email->returnPath($returnPath);
        }

        $sender = $message->getSender();
        if ($sender !== '') {
            $email->sender($sender);
        }

        $date = $message->getDate();
        if ($date !== null) {
            $email->date($date);
        }

        $emailHeaders = $email->getHeaders();
        foreach ($message->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                match ($name) {
                    'Date' => $emailHeaders->addDateHeader($name, new DateTimeImmutable($value)),
                    'Message-ID' => $emailHeaders->addIdHeader($name, $value),
                    default => $emailHeaders->addTextHeader($name, $value),
                };
            }
        }

        foreach ($message->getAttachments() as $file) {
            $file->path() === null
                ? $email->attach((string) $file->content(), $file->name(), $file->contentType())
                : $email->attachFromPath($file->path(), $file->name(), $file->contentType());
        }

        foreach ($message->getEmbeddedFiles() as $file) {
            $file->path() === null
                ? $email->embed((string) $file->content(), $file->name(), $file->contentType())
                : $email->embedFromPath($file->path(), $file->name(), $file->contentType());
        }

        return $email;
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param Address[] $addresses
     *
     * @return array<string, string>|string
     */
    private function convertAddressesToStrings(array $addresses): array|string
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
     * @param array<int|string, string>|string $strings
     *
     * @return Address[]
     */
    private function convertStringsToAddresses(array|string $strings): array
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
