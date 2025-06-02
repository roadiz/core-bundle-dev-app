<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\User;

use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 */
class OpenIdAccount implements UserInterface, EquatableInterface
{
    #[Groups(['user'])]
    protected ?string $issuer = null;

    #[Groups(['user'])]
    protected ?string $name = null;

    #[Groups(['user'])]
    protected ?string $nickname = null;

    #[Groups(['user'])]
    protected ?string $website = null;

    #[Groups(['user'])]
    protected ?string $locale = null;

    #[Groups(['user'])]
    protected ?string $phoneNumber = null;

    #[Groups(['user'])]
    protected ?array $address = null;

    #[Groups(['user'])]
    protected ?string $familyName = null;

    #[Groups(['user'])]
    protected ?string $middleName = null;

    #[Groups(['user'])]
    protected ?string $givenName = null;

    #[Groups(['user'])]
    protected ?string $picture = null;

    #[Groups(['user'])]
    protected ?string $profile = null;

    public function __construct(
        #[Groups(['user'])]
        protected string $email,
        /**
         * @var array<string>
         */
        #[Groups(['user'])]
        protected array $roles,
        #[Ignore]
        protected Token $jwtToken,
    ) {
        if (!($this->jwtToken instanceof Token\Plain)) {
            throw new \InvalidArgumentException('Token must be an instance of '.Token\Plain::class);
        }
        /*
         * https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
         */
        $claims = $this->jwtToken->claims();
        $this->name = $this->getStringClaim($claims, 'name');
        $this->issuer = $this->getStringClaim($claims, 'iss');
        $this->givenName = $this->getStringClaim($claims, 'given_name');
        $this->familyName = $this->getStringClaim($claims, 'family_name');
        $this->middleName = $this->getStringClaim($claims, 'middle_name');
        $this->nickname = $this->getStringClaim($claims, 'nickname') ??
            $this->getStringClaim($claims, 'preferred_username') ??
            null;
        $this->profile = $this->getStringClaim($claims, 'profile');
        $this->picture = $this->getStringClaim($claims, 'picture');
        $this->locale = $this->getStringClaim($claims, 'locale');
        $this->phoneNumber = $this->getStringClaim($claims, 'phone_number');
        $this->address = $this->getArrayClaim($claims, 'address');
    }

    private function getStringClaim(Token\DataSet $claims, string $claimName): ?string
    {
        if (!empty($claimName) && $claims->has($claimName) && is_string($claims->get($claimName))) {
            return $claims->get($claimName);
        }

        return null;
    }

    private function getArrayClaim(Token\DataSet $claims, string $claimName): ?array
    {
        if (!empty($claimName) && $claims->has($claimName) && is_array($claims->get($claimName))) {
            return $claims->get($claimName);
        }

        return null;
    }

    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): string
    {
        return '';
    }

    public function getUsername(): string
    {
        return $this->email ?? '';
    }

    #[\Override]
    public function eraseCredentials(): void
    {
        return;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getAddress(): ?array
    {
        return $this->address;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function getJwtToken(): Token
    {
        return $this->jwtToken;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->getEmail() ?? '';
    }

    #[\Override]
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof OpenIdAccount) {
            return false;
        }

        if ($this->getEmail() !== $user->getEmail()) {
            return false;
        }

        if (array_diff($this->getRoles(), $user->getRoles())) {
            return false;
        }

        if ($this->getJwtToken() !== $user->getJwtToken()) {
            return false;
        }

        return true;
    }
}
