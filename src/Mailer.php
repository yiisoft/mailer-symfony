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
use Yiisoft\Mailer\BaseMailer;
use Yiisoft\Mailer\MessageInterface;
use Yiisoft\Mailer\MessageSettings;

use function sprintf;

/**
 * Mailer implements a mailer based on Symfony Mailer.
 *
 * @see https://github.com/symfony/mailer
 *
 * @api
 */
final class Mailer extends BaseMailer
{
    private SymfonyMailer $symfonyMailer;
    private ?SMimeEncrypter $encryptor = null;

    private null|DkimSigner|SMimeSigner $signer = null;
    private array $dkimSignerOptions = [];

    private EmailFactory $emailFactory;

    public function __construct(
        TransportInterface $transport,
        ?MessageSettings $messageSettings = null,
        ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        parent::__construct($messageSettings, $eventDispatcher);
        $this->symfonyMailer = new SymfonyMailer($transport);
        $this->emailFactory = new EmailFactory();
    }

    /**
     * Returns a new instance with the specified encryptor.
     *
     * @param SMimeEncrypter $encryptor The encryptor instance.
     *
     * @see https://symfony.com/doc/current/mailer.html#encrypting-messages
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
            $signer::class,
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @throws TransportExceptionInterface If sending failed.
     */
    protected function sendMessage(MessageInterface $message): void
    {
        $email = $this->emailFactory->create($message);

        if ($this->encryptor !== null) {
            $email = $this->encryptor->encrypt($email);
        }

        if ($this->signer !== null) {
            $email = $this->signer instanceof DkimSigner
                ? $this->signer->sign($email, $this->dkimSignerOptions)
                : $this->signer->sign($email)
            ;
        }

        $this->symfonyMailer->send($email);
    }
}
