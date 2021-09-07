<?php

declare(strict_types=1);

namespace Yiisoft\Mailer\Symfony;

use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Yiisoft\Mailer\Mailer as BaseMailer;
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageFactoryInterface;
use Yiisoft\Mailer\MessageInterface;

use function get_class;
use function sprintf;

/**
 * Mailer implements a mailer based on Symfony Mailer.
 *
 * @see https://github.com/symfony/mailer
 */
final class Mailer extends BaseMailer
{
    private SymfonyMailer $symfonyMailer;
    private ?SMimeEncrypter $encryptor = null;

    /**
     * @var DkimSigner|SMimeSigner|null
     */
    private $signer = null;
    private array $dkimSignerOptions = [];

    /**
     * @param MessageFactoryInterface $messageFactory
     * @param MessageBodyRenderer $messageBodyRenderer
     * @param EventDispatcherInterface $eventDispatcher
     * @param TransportInterface $transport
     */
    public function __construct(
        MessageFactoryInterface $messageFactory,
        MessageBodyRenderer $messageBodyRenderer,
        EventDispatcherInterface $eventDispatcher,
        TransportInterface $transport
    ) {
        parent::__construct($messageFactory, $messageBodyRenderer, $eventDispatcher);
        $this->symfonyMailer = new SymfonyMailer($transport);
    }

    /**
     * Returns a new instance with the specified encryptor.
     *
     * @param SMimeEncrypter $encryptor The encryptor instance.
     *
     * @see https://symfony.com/doc/current/mailer.html#encrypting-messages
     *
     * @return self
     */
    public function withEncryptor(SMimeEncrypter $encryptor): self
    {
        $new = clone $this;
        $new->encryptor = $encryptor;
        return $new;
    }

    /**
     * Returns a new instance with the specified signer.
     *
     * @param DkimSigner|object|SMimeSigner $signer The signer instance.
     * @param array $options The options for DKIM signer {@see DkimSigner}.
     *
     * @throws RuntimeException If the signer is not an instance of {@see DkimSigner} or {@see SMimeSigner}.
     *
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     *
     * @return self
     */
    public function withSigner(object $signer, array $options = []): self
    {
        $new = clone $this;

        if ($signer instanceof DkimSigner) {
            $new->signer = $signer;
            $new->dkimSignerOptions = $options;
            return $new;
        }

        if ($signer instanceof SMimeSigner) {
            $new->signer = $signer;
            return $new;
        }

        throw new RuntimeException(sprintf(
            'The signer must be an instance of "%s" or "%s". The "%s" instance is received.',
            DkimSigner::class,
            SMimeSigner::class,
            get_class($signer),
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @throws TransportExceptionInterface If sending failed.
     */
    protected function sendMessage(MessageInterface $message): void
    {
        if (!($message instanceof Message)) {
            throw new RuntimeException(sprintf(
                'The message must be an instance of "%s". The "%s" instance is received.',
                Message::class,
                get_class($message),
            ));
        }

        $message = $message->getSymfonyEmail();

        if ($this->encryptor !== null) {
            $message = $this->encryptor->encrypt($message);
        }

        if ($this->signer !== null) {
            $message = $this->signer instanceof DkimSigner
                ? $this->signer->sign($message, $this->dkimSignerOptions)
                : $this->signer->sign($message)
            ;
        }

        $this->symfonyMailer->send($message);
    }
}
