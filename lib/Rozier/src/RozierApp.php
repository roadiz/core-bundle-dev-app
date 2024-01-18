<?php

declare(strict_types=1);

namespace Themes\Rozier;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CompatBundle\Controller\AppController;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Roles;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface;
use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use RZ\Roadiz\OpenId\OAuth2LinkGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Themes\Rozier\Event\UserActionsMenuEvent;
use Themes\Rozier\Explorer\FoldersProvider;
use Themes\Rozier\Explorer\SettingsProvider;
use Themes\Rozier\Explorer\UsersProvider;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Rozier main theme application
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
            EmailManager::class => EmailManager::class,
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

    /**
     * @inheritDoc
     */
    public function createEntityListManager(string $entity, array $criteria = [], array $ordering = []): EntityListManagerInterface
    {
        return parent::createEntityListManager($entity, $criteria, $ordering)
            ->setDisplayingNotPublishedNodes(true);
    }

    /**
     * Returns a fully qualified view path for Twig rendering.
     *
     * @param string $view
     * @param string $namespace
     * @return string
     */
    protected function getNamespacedView(string $view, string $namespace = ''): string
    {
        if ($namespace !== "" && $namespace !== "/") {
            $view = '@' . $namespace . '/' . $view;
        } elseif ($namespace !== "/") {
            // when no namespace is used
            // use current theme directory
            $view = '@RoadizRozier/' . $view;
        }

        return $view;
    }

    /**
     * @return $this
     */
    public function prepareBaseAssignation()
    {
        parent::prepareBaseAssignation();
        /*
         * Use kernel DI container to delay API requests
         */
        $this->assignation['themeServices'] = $this->container->get(RozierServiceRegistry::class);

        /** @var CsrfTokenManagerInterface $tokenManager */
        $tokenManager = $this->container->get('csrfTokenManager');
        /*
         * Switch this to true to use uncompressed JS and CSS files
         */
        $this->assignation['head']['backDevMode'] = false;
        $this->assignation['head']['siteTitle'] = $this->getSettingsBag()->get('site_name') . ' backstage';
        $this->assignation['head']['mapsLocation'] = $this->getSettingsBag()->get('maps_default_location') ? $this->getSettingsBag()->get('maps_default_location') : null;
        $this->assignation['head']['mainColor'] = $this->getSettingsBag()->get('main_color');
        $this->assignation['head']['googleClientId'] = $this->getSettingsBag()->get('google_client_id', "");
        $this->assignation['head']['themeName'] = static::$themeName;
        $this->assignation['head']['ajaxToken'] = $tokenManager->getToken(static::AJAX_TOKEN_INTENTION);
        /** @var UserActionsMenuEvent $userActionsMenuEvent */
        $userActionsMenuEvent = $this->dispatchEvent(new UserActionsMenuEvent());
        $this->assignation['rozier_user_actions'] = $userActionsMenuEvent->getActions();

        $this->assignation['nodeStatuses'] = [
            Node::getStatusLabel(Node::DRAFT) => Node::DRAFT,
            Node::getStatusLabel(Node::PENDING) => Node::PENDING,
            Node::getStatusLabel(Node::PUBLISHED) => Node::PUBLISHED,
            Node::getStatusLabel(Node::ARCHIVED) => Node::ARCHIVED,
            Node::getStatusLabel(Node::DELETED) => Node::DELETED,
        ];

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return Response $response
     * @throws RuntimeError
     */
    public function indexAction(Request $request)
    {
        return $this->render('@RoadizRozier/index.html.twig', $this->assignation);
    }

    /**
     * @param Request $request
     *
     * @return Response $response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function cssAction(Request $request): Response
    {
        /** @var NodeTypes $nodeTypesBag */
        $nodeTypesBag = $this->container->get('nodeTypesBag');
        $this->assignation['mainColor'] = $this->getSettingsBag()->get('main_color');
        $this->assignation['nodeTypes'] = $nodeTypesBag->all();

        $folderQb = $this->em()->getRepository(Folder::class)->createQueryBuilder('f');
        $this->assignation['folders'] = $folderQb->andWhere($folderQb->expr()->neq('f.color', ':defaultColor'))
            ->setParameter('defaultColor', '#000000')
            ->getQuery()
            ->getResult();

        $tagQb = $this->em()->getRepository(Tag::class)->createQueryBuilder('t');
        $this->assignation['tags'] = $tagQb->andWhere($tagQb->expr()->neq('t.color', ':defaultColor'))
            ->setParameter('defaultColor', '#000000')
            ->getQuery()
            ->getResult();

        $response = new Response(
            $this->getTwig()->render('@RoadizRozier/css/mainColor.css.twig', $this->assignation),
            Response::HTTP_OK,
            ['content-type' => 'text/css']
        );

        return $this->makeResponseCachable($request, $response, 60, true);
    }
}
