<?php

declare(strict_types=1);

namespace Themes\Rozier\AjaxControllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\Forms\ImageCropAlignmentType;
use Twig\Environment;

/**
 * Render a template form for document alignment to be used in HTML.
 */
final class AjaxDocumentAlignmentController extends AbstractController
{
    public function __construct(private readonly FormFactoryInterface $formFactory, private readonly Environment $twig)
    {
    }

    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_DOCUMENTS');

        $form = $this->formFactory->create(ImageCropAlignmentType::class, options: [
            'csrf_protection' => false,
        ]);

        return new Response(
            $this->twig->render('@RoadizRozier/documents/documentAlignment.html.twig', [
                'form' => $form->createView(),
            ]),
            Response::HTTP_PARTIAL_CONTENT
        );
    }
}
