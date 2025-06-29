<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Themes\Rozier\AjaxControllers\AbstractAjaxController;

final class NodesSourcesStatusController extends AbstractAjaxController
{
    public function __construct(
        private readonly Registry $workflowRegistry,
        private readonly LogTrail $logTrail,
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, $serializer, $translator);
    }

    /**
     * Handle AJAX edition requests for Node
     * such as coming from node-tree widgets.
     *
     * @return Response JSON response
     */
    public function __invoke(Request $request, NodesSources $nodesSources): Response
    {
        $this->validateRequest($request);

        $workflow = $this->workflowRegistry->get($nodesSources);

        if (!is_string($request->get('statusValue'))) {
            throw new BadRequestHttpException('Status value is not specified.');
        }

        $workflow->apply($nodesSources, $request->get('statusValue'));
        $this->managerRegistry->getManager()->flush();
        $msg = $this->translator->trans('node.%name%.status_changed_to.%status%', [
            '%name%' => $nodesSources->getTitle(),
            '%status%' => $this->translator->trans(match (true) {
                $nodesSources->isPublished() => 'published',
                $nodesSources->isDeleted() => 'deleted',
                default => 'draft',
            }),
        ]);
        $this->logTrail->publishConfirmMessage($request, $msg, $nodesSources);

        return new JsonResponse(
            [
                'statusCode' => Response::HTTP_PARTIAL_CONTENT,
                'status' => 'success',
                'responseText' => $msg,
                'name' => 'status',
                'value' => $request->get('statusValue'),
            ],
            Response::HTTP_PARTIAL_CONTENT
        );
    }
}
