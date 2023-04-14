<?php

declare(strict_types=1);

namespace Themes\Rozier;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\RozierBundle\Controller\BackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\Event\UserActionsMenuEvent;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Rozier main theme application
 */
class RozierApp extends BackendController
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
        $this->assignation['themeServices'] = $this->get(RozierServiceRegistry::class);

        /*
         * Switch this to true to use uncompressed JS and CSS files
         */
        $this->assignation['head']['backDevMode'] = false;
        //Settings
        $this->assignation['head']['siteTitle'] = $this->getSettingsBag()->get('site_name') . ' backstage';
        $this->assignation['head']['mapsLocation'] = $this->getSettingsBag()->get('maps_default_location') ? $this->getSettingsBag()->get('maps_default_location') : null;
        $this->assignation['head']['mainColor'] = $this->getSettingsBag()->get('main_color');
        $this->assignation['head']['googleClientId'] = $this->getSettingsBag()->get('google_client_id', "");
        $this->assignation['head']['themeName'] = static::$themeName;
        $this->assignation['head']['ajaxToken'] = $this->get('csrfTokenManager')->getToken(static::AJAX_TOKEN_INTENTION);
        $this->assignation['rozier_user_actions'] = $this->get('dispatcher')->dispatch(new UserActionsMenuEvent())->getActions();

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
        $this->assignation['mainColor'] = $this->getSettingsBag()->get('main_color');
        $this->assignation['nodeTypes'] = $this->get('nodeTypesBag')->all();

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

        return $this->makeResponseCachable($request, $response, 30, true);
    }
}
