<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony;

use DateTimeImmutable;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File as SymfonyFile;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MessageInterface;

use function is_string;

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
            $email->addPart(
                $this->createDataPartFromFile($file)
            );
        }

        foreach ($message->getEmbeddings() as $file) {
            $email->addPart(
                $this->createDataPartFromFile($file)->asInline()
            );
        }

        return $email;
    }

    /**
     * @see Email::attach()
     * @see Email::attachFromPath()
     * @see Email::embed()
     * @see Email::embedFromPath()
     */
    private function createDataPartFromFile(File $file): DataPart
    {
        $body = $file->path() === null
            ? $file->content() ?? ''
            : new SymfonyFile($file->path(), $file->name());
        return (new DataPart($body, $file->name(), $file->contentType()))
            ->setContentId($file->id());
    }

    /**
     * Converts string representations of address to their instances.
     *
     * @param string[]|string $strings
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
