<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use RZ\Roadiz\Core\AbstractEntities\AbstractHuman;
use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use RZ\Roadiz\CoreBundle\Security\User\AdvancedUserInterface;
use RZ\Roadiz\Random\SaltGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use RZ\Roadiz\CoreBundle\Form\Constraint\ValidFacebookName;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\Table(name: "users"),
    ORM\Index(columns: ["username"], name: "idx_users_username"),
    ORM\Index(columns: ["email"], name: "idx_users_email"),
    ORM\Index(columns: ["enabled"], name: "idx_users_enabled"),
    ORM\Index(columns: ["credentials_expires_at"], name: "idx_users_credentials_expires_at"),
    ORM\Index(columns: ["password_requested_at"], name: "idx_users_password_requested_at"),
    ORM\Index(columns: ["expires_at"], name: "idx_users_expires_at"),
    ORM\Index(columns: ["last_login"], name: "idx_users_last_login"),
    ORM\Index(columns: ["locked"], name: "idx_users_locked"),
    ORM\Index(columns: ["locale"], name: "idx_users_locale"),
    ORM\HasLifecycleCallbacks,
    UniqueEntity("email"),
    UniqueEntity("username")
]
class User extends AbstractHuman implements UserInterface, AdvancedUserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Email confirmation link TTL (in seconds) to change
     * password.
     *
     * @var int
     */
    public const CONFIRMATION_TTL = 900;

    /**
     * @Serializer\Groups({"user_personal", "human"})
     * @var string|null
     */
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[SymfonySerializer\Groups(['user_personal', 'human'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Assert\Email]
    /** @phpstan-ignore-next-line */
    protected ?string $email = null;

    /**
     * @var bool
     * @Serializer\Exclude()
     */
    #[SymfonySerializer\Ignore]
    protected bool $sendCreationConfirmationEmail = false;

    #[ORM\Column(name: 'facebook_name', type: 'string', unique: false, nullable: true)]
    #[SymfonySerializer\Groups(['user_social'])]
    #[Serializer\Groups(['user_social'])]
    #[ValidFacebookName]
    protected ?string $facebookName = null;

    /**
     * @var string|null
     * @Serializer\Groups({"user"})
     */
    #[ORM\Column(name: 'picture_url', type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['user'])]
    #[Assert\Length(max: 250)]
    protected ?string $pictureUrl = null;

    /**
     * @var boolean
     * @Serializer\Groups({"user_security"})
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    #[SymfonySerializer\Groups(['user_security'])]
    protected bool $enabled = true;

    /**
     * @Serializer\Groups({"user_security"})
     * @var string|null
     */
    #[ORM\Column(name: 'confirmation_token', type: 'string', unique: true, nullable: true)]
    #[SymfonySerializer\Groups(['user_security'])]
    protected ?string $confirmationToken = null;

    /**
     * @Serializer\Groups({"user_security"})
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['user_security'])]
    protected ?\DateTime $passwordRequestedAt = null;

    /**
     * @Serializer\Groups({"user_personal", "log_user"})
     * @var string
     */
    #[ORM\Column(type: 'string', unique: true)]
    #[SymfonySerializer\Groups(['user_personal', 'log_user'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private string $username = '';

    /**
     * The salt to use for hashing.
     */
    #[ORM\Column(name: 'salt', type: 'string')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private string $salt = '';

    /**
     * Encrypted password.
     */
    #[ORM\Column(type: 'string', nullable: false)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private string $password = '';

    /**
     * Plain password. Used for model validation.
     * **Must not be persisted.**
     *
     * @var string|null
     * @Serializer\Groups({"user:write"})
     * @PasswordStrength(minLength=8, minStrength=3)
     */
    #[SymfonySerializer\Groups(['user:write'])]
    #[Assert\NotBlank(groups: ['no_empty_password'])]
    private ?string $plainPassword = null;

    /**
     * @var \DateTime|null
     * @Serializer\Groups({"user_security"})
     */
    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['user_security'])]
    private ?\DateTime $lastLogin = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'users_roles')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $roleEntities;

    /**
     * Names of current User roles
     * to be compatible with symfony security scheme
     *
     * @var array<string>|null
     */
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?array $roles = null;

    /**
     * @var Collection<int, Group>
     * @Serializer\Groups({"user_group"})
     */
    #[ORM\JoinTable(name: 'users_groups')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    #[SymfonySerializer\Groups(['user_group'])]
    private Collection $groups;

    /**
     * @var boolean
     * @Serializer\Groups({"user_security"})
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[SymfonySerializer\Groups(['user_security'])]
    private bool $locked = false;

    /**
     * @Serializer\Groups({"user_security"})
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'credentials_expires_at', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['user_security'])]
    private ?\DateTime $credentialsExpiresAt = null;

    /**
     * @Serializer\Groups({"user_security"})
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    #[SymfonySerializer\Groups(['user_security'])]
    private ?\DateTime $expiresAt = null;

    /**
     * @Serializer\Groups({"user_chroot"})
     * @var Node|null
     */
    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(name: 'chroot_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[SymfonySerializer\Groups(['user_chroot'])]
    private ?Node $chroot = null;

    /**
     * @var null|string
     * @Serializer\Groups({"user"})
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 7, nullable: true)]
    #[SymfonySerializer\Groups(['user'])]
    private ?string $locale = null;

    public function __construct()
    {
        $this->roleEntities = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->sendCreationConfirmationEmail(false);
        $this->initAbstractDateTimed();

        $saltGenerator = new SaltGenerator();
        $this->setSalt($saltGenerator->generateSalt());
    }

    /**
     * Set if we need Roadiz to send a default email
     * when User will be persisted.
     *
     * @param bool $sendCreationConfirmationEmail
     * @return User
     */
    public function sendCreationConfirmationEmail(bool $sendCreationConfirmationEmail): User
    {
        $this->sendCreationConfirmationEmail = $sendCreationConfirmationEmail;
        return $this;
    }

    /**
     * Tells if we need Roadiz to send a default email
     * when User will be persisted. Default: false.
     *
     * @return bool
     */
    public function willSendCreationConfirmationEmail(): bool
    {
        return $this->sendCreationConfirmationEmail;
    }

    /**
     * Get available username data, first name and last name
     * or username as a last try.
     *
     * @return string
     * @Serializer\Exclude()
     */
    #[SymfonySerializer\Ignore]
    public function getIdentifier(): string
    {
        if ($this->getFirstName() != "" && $this->getLastName() != "") {
            return $this->getFirstName() . " " . $this->getLastName();
        } elseif ($this->getFirstName() != "") {
            return $this->getFirstName();
        } else {
            return $this->getUsername();
        }
    }

    /**
     * @return string
     * @Serializer\Groups({"user_identifier", "user_personal"})
     * @Serializer\VirtualProperty()
     */
    #[SymfonySerializer\SerializedName('identifier')]
    #[SymfonySerializer\Groups(['user_identifier', 'user_personal'])]
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return string $username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get facebook profile name to grab public infos such as picture
     *
     * @return string|null
     */
    public function getFacebookName(): ?string
    {
        return $this->facebookName;
    }

    /**
     * @param string|null $facebookName
     * @return $this
     */
    public function setFacebookName(?string $facebookName): User
    {
        $this->facebookName = $facebookName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    /**
     * @param string|null $pictureUrl
     * @return $this
     */
    public function setPictureUrl(?string $pictureUrl): User
    {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     * @return $this
     */
    public function setSalt(string $salt): User
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return string $password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string|null $plainPassword
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;
        if (null !== $plainPassword && $plainPassword != '') {
            /*
             * We MUST change password to trigger preUpdate lifeCycle event.
             */
            $this->password = '--password-changed--' . uniqid();
        }
        return $this;
    }

    /**
     * @return \DateTime|null $lastLogin
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime|null $lastLogin
     * @return User
     */
    public function setLastLogin(?\DateTime $lastLogin): User
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * Get random string sent to the user email address in order to verify it.
     *
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * Set random string sent to the user email address in order to verify it.
     *
     * @param string|null $confirmationToken
     * @return $this
     */
    public function setConfirmationToken(?string $confirmationToken): User
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Check if password reset request has expired.
     *
     * @param int $ttl Password request time to live.
     *
     * @return boolean
     */
    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param \DateTime|null $date
     * @return $this
     */
    public function setPasswordRequestedAt(\DateTime $date = null): User
    {
        $this->passwordRequestedAt = $date;
        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use addRoleEntity
     */
    public function addRole(Role $role): User
    {
        return $this->addRoleEntity($role);
    }

    /**
     * Add a role object to current user.
     *
     * @param Role $role
     *
     * @return $this
     */
    public function addRoleEntity(Role $role): User
    {
        if (!$this->getRolesEntities()->contains($role)) {
            $this->getRolesEntities()->add($role);
        }

        return $this;
    }

    /**
     * Get roles entities
     *
     * @return Collection|null
     */
    public function getRolesEntities(): ?Collection
    {
        return $this->roleEntities;
    }

    /**
     * @param ArrayCollection $roles
     * @return User
     */
    public function setRolesEntities(ArrayCollection $roles): User
    {
        $this->roleEntities = $roles;
        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use removeRoleEntity
     */
    public function removeRole(Role $role): User
    {
        return $this->removeRoleEntity($role);
    }

    /**
     * Remove role from current user.
     *
     * @param Role $role
     *
     * @return $this
     */
    public function removeRoleEntity(Role $role): User
    {
        if ($this->getRolesEntities()->contains($role)) {
            $this->getRolesEntities()->removeElement($role);
        }
        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * @return User
     */
    public function eraseCredentials(): User
    {
        return $this->setPlainPassword('');
    }

    /**
     * Insert user into group.
     *
     * @param Group $group
     *
     * @return $this
     */
    public function addGroup(Group $group): User
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getGroups(): ?Collection
    {
        return $this->groups;
    }

    /**
     * Remove user from group
     *
     * @param Group $group
     *
     * @return $this
     */
    public function removeGroup(Group $group): User
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Get current user groups name.
     *
     * @return array Array of strings
     * @Serializer\Groups({"user"})
     * @Serializer\VirtualProperty()
     */
    #[SymfonySerializer\Groups(['user'])]
    public function getGroupNames(): array
    {
        $names = [];
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Combines expiresAt date-time limit AND expired boolean value.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool    true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     * @Serializer\Groups({"user_security"})
     * @Serializer\VirtualProperty()
     */
    #[SymfonySerializer\Groups(['user_security'])]
    public function isAccountNonExpired(): bool
    {
        return $this->expiresAt === null || $this->expiresAt->getTimestamp() > time();
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     * @Serializer\Groups({"user_security"})
     * @Serializer\VirtualProperty()
     */
    #[SymfonySerializer\Groups(['user_security'])]
    public function isAccountNonLocked(): bool
    {
        return !$this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param User $user
     *
     * @return boolean
     */
    public function equals(User $user): bool
    {
        return (
            $this->username == $user->getUsername() ||
            $this->email == $user->getEmail()
        );
    }

    /**
     * @return \DateTime|null
     */
    public function getCredentialsExpiresAt(): ?\DateTime
    {
        return $this->credentialsExpiresAt;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return User
     */
    public function setCredentialsExpiresAt(?\DateTime $date = null): User
    {
        $this->credentialsExpiresAt = $date;

        return $this;
    }

    /**
     * @return Node|null
     * @internal Do use directly, use NodeChrootResolver class to support external users (SSO, oauth2, …)
     */
    public function getChroot(): ?Node
    {
        return $this->chroot;
    }

    /**
     * @param Node|null $chroot
     * @return User
     */
    public function setChroot(Node $chroot = null): User
    {
        $this->chroot = $chroot;

        return $this;
    }

    /**
     * Get prototype abstract Gravatar url.
     *
     * @Serializer\Exclude()
     * @param string $type Default: "identicon"
     * @param string $size Default: "200"
     * @return string
     */
    #[SymfonySerializer\Ignore]
    public function getGravatarUrl(string $type = "identicon", string $size = "200"): string
    {
        if (null !== $this->getEmail()) {
            return "https://www.gravatar.com/avatar/" . md5(\mb_strtolower(trim($this->getEmail()))) . "?d=" . $type . "&s=" . $size;
        }
        return '';
    }

    /**
     * @return string $text
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Combines credentialsExpiresAt date-time limit AND credentialsExpired boolean value.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool    true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    #[SymfonySerializer\Ignore]
    public function isCredentialsNonExpired(): bool
    {
        return $this->credentialsExpiresAt === null || $this->credentialsExpiresAt->getTimestamp() > time();
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime|null $expiresAt
     * @return User
     */
    public function setExpiresAt(?\DateTime $expiresAt): User
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * Get roles names as a simple array, combining groups roles.
     *
     * @return array<string>
     */
    #[SymfonySerializer\SerializedName('roles')]
    #[SymfonySerializer\Groups(['user_role'])]
    public function getRoles(): array
    {
        if (null === $this->roles) {
            $this->roles = [];
            if (null !== $this->getRolesEntities()) {
                foreach ($this->getRolesEntities() as $role) {
                    if (null !== $role) {
                        $this->roles[] = $role->getName();
                    }
                }
            }
            if (null !== $this->getGroups()) {
                foreach ($this->getGroups() as $group) {
                    if ($group instanceof Group) {
                        // User roles > Groups roles
                        $this->roles = array_merge($group->getRoles(), $this->roles);
                    }
                }
            }

            // we need to make sure to have at least one role
            $this->roles[] = Role::ROLE_DEFAULT;
            $this->roles = array_unique($this->roles);
        }

        return $this->roles;
    }

    /**
     * @return null|string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param null|string $locale
     * @return User
     */
    public function setLocale(?string $locale): User
    {
        $this->locale = $locale;
        return $this;
    }

    public function __serialize(): array
    {
        return [
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            // needed for token roles
            $this->roleEntities,
            $this->groups,
            // needed for advancedUserinterface
            $this->expiresAt,
            $this->locked,
            $this->credentialsExpiresAt,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->roleEntities,
            $this->groups,
            $this->expiresAt,
            $this->locked,
            $this->credentialsExpiresAt,
        ] = $data;
    }

    /**
     * @Serializer\Groups({"user_security"})
     */
    #[SymfonySerializer\Groups(['user_security'])]
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_SUPERADMIN);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasGroup(string $name): bool
    {
        return in_array((string) $name, $this->getGroupNames());
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array(\mb_strtoupper((string) $role), $this->getRoles(), true);
    }

    /**
     * Every field tested in this methods must be serialized in token.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    #[SymfonySerializer\Ignore]
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->getId() !== $user->getId()) {
            return false;
        }

        if ($this->getEmail() !== $user->getEmail()) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        if ($this->isAccountNonExpired() !== $user->isAccountNonExpired()) {
            return false;
        }

        if ($this->isAccountNonLocked() !== $user->isAccountNonLocked()) {
            return false;
        }

        if ($this->isCredentialsNonExpired() !== $user->isCredentialsNonExpired()) {
            return false;
        }

        if ($this->isEnabled() !== $user->isEnabled()) {
            return false;
        }

        if (array_diff($this->getRoles(), $user->getRoles())) {
            return false;
        }

        return true;
    }
}
