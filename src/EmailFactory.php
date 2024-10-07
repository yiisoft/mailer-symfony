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
        $email = new Email();

        $from = $this->convertStringsToAddresses($message->getFrom());
        if ($from !== null) {
            $email = $email->from(...$from);
        }

        $to = $this->convertStringsToAddresses($message->getTo());
        if ($to !== null) {
            $email = $email->to(...$to);
        }

        $replyTo = $this->convertStringsToAddresses($message->getReplyTo());
        if ($replyTo !== null) {
            $email = $email->replyTo(...$replyTo);
        }

        $cc = $this->convertStringsToAddresses($message->getCc());
        if ($cc !== null) {
            $email = $email->cc(...$cc);
        }

        $bcc = $this->convertStringsToAddresses($message->getBcc());
        if ($bcc !== null) {
            $email = $email->bcc(...$bcc);
        }

        $subject = $message->getSubject();
        if ($subject !== null) {
            $email = $email->subject($subject);
        }

        $priority = $message->getPriority();
        if ($priority !== null) {
            $email = $email->priority($priority->value);
        }

        $charset = $message->getCharset();

        $textBody = $message->getTextBody();
        if ($textBody !== null) {
            $email = $email->text($textBody, $charset ?? 'utf-8');
        }

        $htmlBody = $message->getHtmlBody();
        if ($htmlBody !== null) {
            $email = $email->html($htmlBody, $charset ?? 'utf-8');
        }

        $returnPath = $message->getReturnPath();
        if ($returnPath !== null) {
            $email->returnPath($returnPath);
        }

        $sender = $message->getSender();
        if ($sender !== null) {
            $email->sender($sender);
        }

        $date = $message->getDate();
        if ($date !== null) {
            $email->date($date);
        }

        $emailHeaders = $email->getHeaders();
        foreach ($message->getHeaders() ?? [] as $name => $values) {
            foreach ($values as $value) {
                match ($name) {
                    'Date' => $emailHeaders->addDateHeader($name, new DateTimeImmutable($value)),
                    'Message-ID' => $emailHeaders->addIdHeader($name, $value),
                    default => $emailHeaders->addTextHeader($name, $value),
                };
            }
        }

        foreach ($message->getAttachments() ?? [] as $file) {
            $email->addPart(
                $this->createDataPartFromFile($file)
            );
        }

        foreach ($message->getEmbeddings() ?? [] as $file) {
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
     * @param string|string[]|null $strings
     *
     * @return Address[]|null
     */
    private function convertStringsToAddresses(array|string|null $strings): array|null
    {
        if ($strings === null) {
            return null;
        }

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
