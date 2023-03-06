<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Crypto;

use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Asymmetric\EncryptionSecretKey;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use RZ\Crypto\Encoder\AsymmetricUniqueKeyEncoder;
use RZ\Crypto\Encoder\SymmetricUniqueKeyEncoder;
use RZ\Crypto\Encoder\UniqueKeyEncoderInterface;
use RZ\Crypto\KeyChain\KeyChainInterface;

class UniqueKeyEncoderFactory
{
    protected KeyChainInterface $keyChain;
    protected string $defaultKeyName;

    /**
     * @param KeyChainInterface $keyChain
     * @param string $defaultKeyName
     */
    public function __construct(KeyChainInterface $keyChain, string $defaultKeyName)
    {
        $this->keyChain = $keyChain;
        $this->defaultKeyName = $defaultKeyName;
    }

    public function getEncoder(?string $keyName = null): UniqueKeyEncoderInterface
    {
        try {
            $keyName = $keyName ?? $this->defaultKeyName;
            $key = $this->keyChain->get($keyName);

            if ($key instanceof EncryptionSecretKey) {
                $publicKey = $key->derivePublicKey();
                return new AsymmetricUniqueKeyEncoder(
                    $publicKey,
                    $key
                );
            } elseif ($key instanceof EncryptionKey) {
                return new SymmetricUniqueKeyEncoder($key);
            }
        } catch (\Exception $exception) {
            throw new InvalidKey(
                sprintf('Key %s is not a valid encryption key', $keyName),
                0,
                $exception
            );
        }

        throw new InvalidKey(sprintf('Key %s is not a valid encryption key', $keyName));
    }
}
