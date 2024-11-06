<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Setting;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LogExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('log_entity_edit_path', [$this, 'getEditPath'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
        ];
    }

    public function getEditPath(?object $log): ?string
    {
        if (!($log instanceof Log) || null === $log->getEntityId()) {
            return null;
        }

        switch ($log->getEntityClass()) {
            case Node::class:
            case NodesSources::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_NODES')
                    && isset($log->getAdditionalData()['node_id'])
                    && isset($log->getAdditionalData()['translation_id'])
                ) {
                    return $this->urlGenerator->generate('nodesEditSourcePage', [
                        'nodeId' => $log->getAdditionalData()['node_id'],
                        'translationId' => $log->getAdditionalData()['translation_id'],
                    ]);
                }
                break;
            case Tag::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_TAGS')
                ) {
                    return $this->urlGenerator->generate('tagsEditPage', [
                        'tagId' => $log->getEntityId(),
                    ]);
                }
                break;
            case Document::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_DOCUMENTS')
                ) {
                    return $this->urlGenerator->generate('documentsEditPage', [
                        'documentId' => $log->getEntityId(),
                    ]);
                }
                break;
            case User::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_USERS')
                ) {
                    return $this->urlGenerator->generate('usersEditPage', [
                        'id' => $log->getEntityId(),
                    ]);
                }
                break;
            case CustomForm::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_CUSTOMFORMS')
                ) {
                    return $this->urlGenerator->generate('customFormsEditPage', [
                        'id' => $log->getEntityId(),
                    ]);
                }
                break;
            case Translation::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_TRANSLATIONS')
                ) {
                    return $this->urlGenerator->generate('translationsEditPage', [
                        'translationId' => $log->getEntityId(),
                    ]);
                }
                break;
            case Setting::class:
                if (
                    $this->security->isGranted('ROLE_ACCESS_SETTINGS')
                ) {
                    return $this->urlGenerator->generate('settingsEditPage', [
                        'settingId' => $log->getEntityId(),
                    ]);
                }
                break;
        }

        return null;
    }
}
