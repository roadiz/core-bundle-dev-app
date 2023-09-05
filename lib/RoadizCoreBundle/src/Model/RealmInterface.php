<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface RealmInterface extends PersistableInterface
{
    public const TYPE_PLAIN_PASSWORD = 'plain_password';
    public const TYPE_ROLE = 'bearer_role';
    public const TYPE_USER = 'bearer_user';

    public const BEHAVIOUR_NONE = 'none';
    public const BEHAVIOUR_DENY = 'deny';
    public const BEHAVIOUR_HIDE_BLOCKS = 'hide_blocks';

    /**
     * Inheritance type to prevent cascading ancestors realms.
     */
    public const INHERITANCE_NONE = 'none';
    /**
     * Inheritance type retreived automatically from ancestors.
     */
    public const INHERITANCE_AUTO = 'auto';
    /**
     * Inheritance type only for Realm roots.
     */
    public const INHERITANCE_ROOT = 'root';

    public function getType(): string;

    /**
     * @return string
     * @see https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml
     */
    public function getAuthenticationScheme(): string;

    /**
     * @return string
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Headers/WWW-Authenticate
     */
    public function getChallenge(): string;
    public function getBehaviour(): string;
    public function getName(): string;
    public function getPlainPassword(): ?string;
    public function getRole(): ?string;
    public function getUsers(): Collection;
    public function getSerializationGroup(): ?string;
}
