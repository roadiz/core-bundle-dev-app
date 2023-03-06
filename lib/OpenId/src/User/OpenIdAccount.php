<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\User;

use Lcobucci\JWT\Token;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @see https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 */
class OpenIdAccount implements UserInterface, EquatableInterface
{
    /**
     * @var array<string>
     * @SymfonySerializer\Groups({"user"})
     */
    protected array $roles;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $issuer = null;
    /**
     * @var string
     * @SymfonySerializer\Groups({"user"})
     */
    protected string $email;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $name = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $nickname = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $website = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $locale = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $phoneNumber = null;
    /**
     * @var array|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?array $address = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $familyName = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $middleName = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $givenName = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $picture = null;
    /**
     * @var string|null
     * @SymfonySerializer\Groups({"user"})
     */
    protected ?string $profile = null;
    /**
     * @var Token
     */
    protected Token $jwtToken;

    /**
     * @param string $email
     * @param array  $roles
     * @param Token  $jwtToken
     */
    public function __construct(
        string $email,
        array $roles,
        Token $jwtToken
    ) {
        $this->roles = $roles;
        $this->email = $email;
        $this->jwtToken = $jwtToken;
        if (!($jwtToken instanceof Token\Plain)) {
            throw new \InvalidArgumentException('Token must be an instance of ' . Token\Plain::class);
        }
        /*
         * https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
         */
        $claims = $jwtToken->claims();
        $this->name = $this->getStringClaim($claims, 'name');
        $this->issuer = $this->getStringClaim($claims, 'iss');
        $this->givenName = $this->getStringClaim($claims, 'given_name');
        $this->familyName = $this->getStringClaim($claims, 'family_name');
        $this->middleName = $this->getStringClaim($claims, 'middle_name');
        $this->nickname = $this->getStringClaim($claims, 'nickname');
        $this->profile = $this->getStringClaim($claims, 'profile');
        $this->picture = $this->getStringClaim($claims, 'picture');
        $this->locale = $this->getStringClaim($claims, 'locale');
        $this->phoneNumber = $this->getStringClaim($claims, 'phone_number');
        $this->address = $this->getArrayClaim($claims, 'address');
    }

    private function getStringClaim(Token\DataSet $claims, string $claimName): ?string
    {
        if ($claims->has($claimName) && is_string($claims->get($claimName))) {
            return $claims->get($claimName);
        }
        return null;
    }
    private function getArrayClaim(Token\DataSet $claims, string $claimName): ?array
    {
        if ($claims->has($claimName) && is_array($claims->get($claimName))) {
            return $claims->get($claimName);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     * @return void
     */
    public function eraseCredentials()
    {
        return;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    /**
     * @return string
     */
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    /**
     * @return string
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    /**
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @return array|null
     */
    public function getAddress(): ?array
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @return string|null
     */
    public function getProfile(): ?string
    {
        return $this->profile;
    }

    /**
     * @return Token
     */
    public function getJwtToken(): Token
    {
        return $this->jwtToken;
    }

    /**
     * @return string|null
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail() ?? '';
    }

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
