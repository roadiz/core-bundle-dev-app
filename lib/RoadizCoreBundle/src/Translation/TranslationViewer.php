<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Translation;

use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\TranslationRepository;
use RZ\Roadiz\CoreBundle\Routing\RouteHandler;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class TranslationViewer
{
    private ?TranslationInterface $translation = null;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Settings $settingsBag,
        private readonly RouterInterface $router,
        private readonly PreviewResolverInterface $previewResolver,
    ) {
    }

    public function getRepository(): TranslationRepository
    {
        return $this->managerRegistry->getRepository(Translation::class);
    }

    /**
     * Return available page translation information.
     *
     * Be careful, for static routes Roadiz will generate a localized
     * route identifier suffixed with "Locale" text. In case of "force_locale"
     * setting to true, Roadiz will always use suffixed route.
     *
     * ## example return value
     *
     *     array (size=3)
     *       'en' =>
     *         array (size=4)
     *             'name' => string 'newsPage'
     *             'url' => string 'http://localhost/news/test'
     *             'locale' => string 'en'
     *             'active' => boolean false
     *             'translation' => string 'English'
     *       'fr' =>
     *         array (size=4)
     *             'name' => string 'newsPageLocale'
     *             'url' => string 'http://localhost/fr/news/test'
     *             'locale' => string 'fr'
     *             'active' => boolean true
     *             'translation' => string 'French'
     *       'es' =>
     *         array (size=4)
     *             'name' => string 'newsPageLocale'
     *             'url' => string 'http://localhost/es/news/test'
     *             'locale' => string 'es'
     *             'active' => boolean false
     *             'translation' => string 'Spanish'
     *
     * @param bool $absolute Generate absolute url or relative paths
     *
     * @throws ORMException
     */
    public function getTranslationMenuAssignation(Request $request, bool $absolute = false): array
    {
        $attr = $request->attributes->all();
        $query = $request->query->all();
        $name = '';
        $forceLocale = (bool) $this->settingsBag->get('force_locale');
        $useStaticRouting = !empty($attr['_route'])
            && is_string($attr['_route'])
            && RouteObjectInterface::OBJECT_BASED_ROUTE_NAME !== $attr['_route'];

        /*
         * Fix absolute boolean to Int constant.
         */
        $absolute = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        if (key_exists('node', $attr) && $attr['node'] instanceof Node) {
            $node = $attr['node'];
            $this->managerRegistry->getManagerForClass(Node::class)->refresh($node);
        } else {
            $node = null;
        }
        /*
         * If using a static route (routes.yml)…
         */
        if ($useStaticRouting) {
            $translations = $this->getRepository()->findAllAvailable();
            /*
             * Search for a route without Locale suffix
             */
            $baseRoute = RouteHandler::getBaseRoute($attr['_route']);
            if (null !== $this->router->getRouteCollection()->get($baseRoute)) {
                $attr['_route'] = $baseRoute;
            }
        } elseif (null !== $node) {
            /*
             * If using dynamic routing…
             */
            if ($this->previewResolver->isPreview()) {
                $translations = $this->getRepository()->findAvailableTranslationsForNode($node);
            } else {
                $translations = $this->getRepository()->findStrictlyAvailableTranslationsForNode($node);
            }
            $name = 'node';
        } else {
            return [];
        }

        $return = [];

        foreach ($translations as $translation) {
            $url = null;
            /*
             * Remove existing _locale in query string
             */
            if (key_exists('_locale', $query)) {
                unset($query['_locale']);
            }
            /*
             * Remove existing page parameter in query string
             * if listing is different between 2 languages, maybe
             * page 2 or 3 does not exist in language B but exists in
             * language A
             */
            if (key_exists('page', $query)) {
                unset($query['page']);
            }

            if ($useStaticRouting) {
                $name = $attr['_route'];
                /*
                 * Use suffixed route if locales are forced or
                 * if it’s not default translation.
                 */
                if (true === $forceLocale || !$translation->isDefaultTranslation()) {
                    /*
                     * Search for a Locale suffixed route
                     */
                    if (null !== $this->router->getRouteCollection()->get($attr['_route'].'Locale')) {
                        $name = $attr['_route'].'Locale';
                    }

                    $attr['_route_params']['_locale'] = $translation->getPreferredLocale();
                } else {
                    if (key_exists('_locale', $attr['_route_params'])) {
                        unset($attr['_route_params']['_locale']);
                    }
                }

                /*
                 * Remove existing page parameter in route parameters
                 * if listing is different between 2 languages, maybe
                 * page 2 or 3 does not exist in language B but exists in
                 * language A
                 */
                if (key_exists('page', $attr['_route_params'])) {
                    unset($attr['_route_params']['page']);
                }

                if (is_string($name)) {
                    $url = $this->router->generate(
                        $name,
                        array_merge($attr['_route_params'], $query),
                        $absolute
                    );
                } else {
                    $url = $this->router->generate(
                        RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                        array_merge($attr['_route_params'], $query, [
                            RouteObjectInterface::ROUTE_OBJECT => $name,
                        ]),
                        $absolute
                    );
                }
            } elseif ($node) {
                $nodesSources = $node->getNodeSourcesByTranslation($translation)->first() ?: null;
                if ($nodesSources instanceof NodesSources) {
                    $url = $this->router->generate(
                        RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                        array_merge($query, [
                            RouteObjectInterface::ROUTE_OBJECT => $nodesSources,
                        ]),
                        $absolute
                    );
                }
            }

            if (null !== $url) {
                $return[$translation->getPreferredLocale()] = [
                    'name' => $name,
                    'url' => $url,
                    'locale' => $translation->getPreferredLocale(),
                    'active' => $this->translation->getPreferredLocale() === $translation->getPreferredLocale(),
                    'translation' => $translation->getName(),
                ];
            }
        }

        return $return;
    }

    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @return TranslationViewer
     */
    public function setTranslation(?TranslationInterface $translation)
    {
        $this->translation = $translation;

        return $this;
    }
}
