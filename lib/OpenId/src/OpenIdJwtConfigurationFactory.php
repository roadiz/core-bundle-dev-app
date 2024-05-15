<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use RZ\Roadiz\JWT\JwtConfigurationFactory;
use RZ\Roadiz\JWT\Validation\Constraint\HostedDomain;
use RZ\Roadiz\JWT\Validation\Constraint\UserInfoEndpoint;

final class OpenIdJwtConfigurationFactory implements JwtConfigurationFactory
{
    public function __construct(
        private readonly ?Discovery $discovery,
        private readonly ?string $openIdHostedDomain,
        private readonly ?string $oauthClientId,
        private readonly bool $verifyUserInfo
    ) {
    }

    /**
     * @return Constraint[]
     */
    protected function getValidationConstraints(): array
    {
        $validators = [
            new LooseValidAt(SystemClock::fromSystemTimezone()),
        ];

        if (!empty($this->oauthClientId)) {
            $validators[] = new PermittedFor(trim($this->oauthClientId));
        }

        if (!empty($this->openIdHostedDomain)) {
            $validators[] = new HostedDomain(trim($this->openIdHostedDomain));
        }

        if (null !== $this->discovery && $this->discovery->isValid()) {
            $issuer = $this->discovery->get('issuer');
            $userinfoEndpoint = $this->discovery->get('userinfo_endpoint');
            if (is_string($issuer) && !empty($issuer)) {
                $validators[] = new IssuedBy($issuer);
            }
            if ($this->verifyUserInfo && is_string($userinfoEndpoint) && !empty($userinfoEndpoint)) {
                $validators[] = new UserInfoEndpoint(trim($userinfoEndpoint));
            }
        }

        return $validators;
    }

    public function create(): Configuration
    {
        $configuration = Configuration::forUnsecuredSigner();
        /*
         * Verify JWT signature if asymmetric crypto is used and if PHP gmp extension is loaded.
         */
        if (
            null !== $this->discovery &&
            $this->discovery->canVerifySignature() &&
            null !== $pems = $this->discovery->getPems()
        ) {
            /** @var array $signingAlgValuesSupported */
            $signingAlgValuesSupported = $this->discovery->get('id_token_signing_alg_values_supported', []);
            if (
                in_array(
                    'RS256',
                    $signingAlgValuesSupported
                ) &&
                !empty($pems[0])
            ) {
                $configuration = Configuration::forAsymmetricSigner(
                    new Sha256(),
                    InMemory::plainText($pems[0]),
                    InMemory::plainText($pems[0])
                );
            }
        }

        $configuration->setValidationConstraints(...$this->getValidationConstraints());
        return $configuration;
    }
}
