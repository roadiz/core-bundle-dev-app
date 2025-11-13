<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\Tag;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\CoreBundle\Tag\TagFactory;
use RZ\Roadiz\RozierBundle\Form\MultiTagType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class TagMultiCreationController extends AbstractController
{
    public function __construct(
        private readonly TagFactory $tagFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
        private readonly LogTrail $logTrail,
    ) {
    }

    #[Route(
        path: '/rz-admin/tags/add-multiple-child/{parentTagId}',
        name: 'tagsAddMultipleChildPage',
        requirements: ['parentTagId' => '\d+']
    )]
    public function addChildAction(
        Request $request,
        #[MapEntity(
            expr: 'repository.find(parentTagId)',
            evictCache: true,
        )]
        Tag $parentTag,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $translation = $this->managerRegistry->getRepository(Translation::class)->findDefault();

        $form = $this->createForm(MultiTagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $names = explode(',', (string) $data['names']);
                $names = array_map('trim', $names);
                $names = array_filter($names);
                $names = array_unique($names);

                /*
                 * Get latest position to add tags after.
                 */
                $latestPosition = $this->managerRegistry
                    ->getRepository(Tag::class)
                    ->findLatestPositionInParent($parentTag);

                $tagsArray = [];
                foreach ($names as $name) {
                    $tagsArray[] = $this->tagFactory->create($name, $translation, $parentTag, $latestPosition);
                    $this->managerRegistry->getManager()->flush();
                }

                foreach ($tagsArray as $tag) {
                    $this->eventDispatcher->dispatch(new TagCreatedEvent($tag));
                    $msg = $this->translator->trans('child.tag.%name%.created', ['%name%' => $tag->getTagName()]);
                    $this->logTrail->publishConfirmMessage($request, $msg, $tag);
                }

                return $this->redirectToRoute('tagsTreePage', ['tagId' => $parentTag->getId()]);
            } catch (\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('@RoadizRozier/tags/add-multiple.html.twig', [
            'tag' => $parentTag,
            'form' => $form->createView(),
            'translation' => $translation,
        ]);
    }
}
