<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension that allow render inner page part calling directly their
 * controller response instead of doing a simple include.
 */
final class BlockRenderExtension extends AbstractExtension
{
    public function __construct(private readonly FragmentHandler $handler)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('render_block', [$this, 'blockRender'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws RuntimeError
     */
    public function blockRender(?NodesSources $nodeSource = null, string $themeName = 'DefaultTheme', array $assignation = []): string
    {
        if (null !== $nodeSource) {
            if (!empty($themeName)) {
                $class = $this->getNodeSourceControllerName($nodeSource, $themeName);
                if (class_exists($class) && method_exists($class, 'blockAction')) {
                    $controllerReference = new ControllerReference($class.'::blockAction', [
                        'source' => $nodeSource,
                        'assignation' => $assignation,
                    ]);

                    /*
                     * ignore_errors option MUST BE false in order to catch ForceResponseException
                     * from Master request render method and redirect users.
                     */
                    return $this->handler->render($controllerReference, 'inline', [
                        'ignore_errors' => false,
                    ]);
                }
                throw new RuntimeError($class.'::blockAction() action does not exist.');
            } else {
                throw new RuntimeError('Invalid name formatting for your theme.');
            }
        }
        throw new RuntimeError('Invalid NodesSources.');
    }

    protected function getNodeSourceControllerName(NodesSources $nodeSource, string $themeName): string
    {
        return '\\Themes\\'.$themeName.'\\Controllers\\Blocks\\'.
                $nodeSource->getNodeTypeName().'Controller';
    }
}
