<?php

declare(strict_types=1);

namespace App\Tests\Rozier;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Templates the 2.7 NSArticle example includes keep a deprecated stub at their old path until 2.9 (ADR 0002).
 */
final class TemplateStubsTest extends KernelTestCase
{
    use ExpectDeprecationTrait;

    public function testAdminBaseExtendsTheAdminLayout(): void
    {
        $twig = $this->twig();
        $template = $twig->load('@RoadizRozier/admin/base.html.twig')->unwrap($twig);

        $parent = $template->getParent([]);

        $this->assertNotFalse($parent);
        $this->assertSame('@RoadizRozier/ui/layouts/admin.html.twig', $parent->getTemplateName());
    }

    /**
     * @group legacy
     */
    public function testColumnOrderingStubRendersTheRzTableMacro(): void
    {
        $this->expectDeprecation('Since roadiz/rozier-bundle 2.8: The "@RoadizRozier/includes/column_ordering.html.twig" template is deprecated%s');

        $twig = $this->twig();
        $request = Request::create('/rz-admin/articles', 'GET', ['field' => 'title', 'ordering' => 'ASC']);
        /** @var RequestStack $requestStack */
        $requestStack = $this->getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        $html = $twig->render('@RoadizRozier/includes/column_ordering.html.twig', [
            'field' => 'title',
            'label' => 'Title',
        ]);

        $this->assertStringContainsString('ordering=DESC', html_entity_decode($html));
        $this->assertStringContainsString('class="rz-link active', $html);
        $this->assertStringContainsString('Title', $html);
    }

    /**
     * @group legacy
     */
    public function testBulkActionsStubIncludesTheBulkActionsPattern(): void
    {
        $this->expectDeprecation('Since roadiz/rozier-bundle 2.8: The "@RoadizRozier/admin/bulk_actions.html.twig" template is deprecated%s');

        $html = $this->twig()->render('@RoadizRozier/admin/bulk_actions.html.twig', ['hasBulkActions' => false]);

        $this->assertSame('', trim($html));
    }

    private function twig(): Environment
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        return $twig;
    }
}
