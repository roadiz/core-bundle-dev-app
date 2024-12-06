<?php

declare(strict_types=1);

namespace Themes\Rozier;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Themes\Rozier\Explorer\FoldersProvider;
use Themes\Rozier\Explorer\SettingsProvider;
use Themes\Rozier\Explorer\UsersProvider;
use Twig\Error\RuntimeError;

/**
 * Rozier main theme application.
 */
class RozierApp extends AppController
{
    protected static string $themeName = 'Rozier Backstage theme';
    protected static string $themeAuthor = 'Ambroise Maupate, Julien Blanchet';
    protected static string $themeCopyright = 'REZO ZERO';
    protected static string $themeDir = 'Rozier';

    public const DEFAULT_ITEM_PER_PAGE = 50;

    public static array $backendLanguages = [
        'Arabic' => 'ar',
        'English' => 'en',
        'Español' => 'es',
        'Français' => 'fr',
        'Indonesian' => 'id',
        'Italiano' => 'it',
        'Türkçe' => 'tr',
        'Русский язык' => 'ru',
        'српска ћирилица' => 'sr',
        '中文' => 'zh',
    ];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'securityAuthenticationUtils' => AuthenticationUtils::class,
            'urlGenerator' => UrlGeneratorInterface::class,
            'logger' => LoggerInterface::class,
            'kernel' => KernelInterface::class,
            'settingsBag' => Settings::class,
            'nodeTypesBag' => NodeTypes::class,
            'rolesBag' => Roles::class,
            'csrfTokenManager' => CsrfTokenManagerInterface::class,
            OAuth2LinkGenerator::class => OAuth2LinkGenerator::class,
            RozierServiceRegistry::class => RozierServiceRegistry::class,
            UsersProvider::class => UsersProvider::class,
            SettingsProvider::class => SettingsProvider::class,
            FoldersProvider::class => FoldersProvider::class,
        ]);
    }

    public function createEntityListManager(string $entity, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return parent::createEntityListManager($entity, $criteria, $ordering)
            ->setDisplayingNotPublishedNodes(true);
    }

    /**
     * Returns a fully qualified view path for Twig rendering.
     */
    protected function getNamespacedView(string $view, string $namespace = ''): string
    {
        if ('' !== $namespace && '/' !== $namespace) {
            $view = '@'.$namespace.'/'.$view;
        } elseif ('/' !== $namespace) {
            // when no namespace is used
            // use current theme directory
            $view = '@RoadizRozier/'.$view;
        }

        return $view;
    }

    /**
     * @return Response $response
     *
     * @throws RuntimeError
     */
    public function indexAction(Request $request): Response
    {
        return $this->render('@RoadizRozier/index.html.twig', $this->assignation);
    }
}
