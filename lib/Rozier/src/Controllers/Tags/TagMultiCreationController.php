<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\Tags;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent;
use RZ\Roadiz\CoreBundle\Tag\TagFactory;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Themes\Rozier\Forms\MultiTagType;
use Themes\Rozier\RozierApp;

class TagMultiCreationController extends RozierApp
{
    public function __construct(private readonly TagFactory $tagFactory)
    {
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function addChildAction(Request $request, int $parentTagId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TAGS');

        $translation = $this->em()->getRepository(Translation::class)->findDefault();
        $parentTag = $this->em()->find(Tag::class, $parentTagId);

        if (null === $parentTag) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(MultiTagType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $names = explode(',', $data['names']);
                $names = array_map('trim', $names);
                $names = array_filter($names);
                $names = array_unique($names);

                /*
                 * Get latest position to add tags after.
                 */
                $latestPosition = $this->em()
                    ->getRepository(Tag::class)
                    ->findLatestPositionInParent($parentTag);

                $tagsArray = [];
                foreach ($names as $name) {
                    $tagsArray[] = $this->tagFactory->create($name, $translation, $parentTag, $latestPosition);
                    $this->em()->flush();
                }

                /*
                 * Dispatch event and msg
                 */
                foreach ($tagsArray as $tag) {
                    /*
                     * Dispatch event
                     */
                    $this->dispatchEvent(new TagCreatedEvent($tag));
                    $msg = $this->getTranslator()->trans('child.tag.%name%.created', ['%name%' => $tag->getTagName()]);
                    $this->publishConfirmMessage($request, $msg, $tag);
                }

                return $this->redirectToRoute('tagsTreePage', ['tagId' => $parentTagId]);
            } catch (\InvalidArgumentException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['translation'] = $translation;
        $this->assignation['form'] = $form->createView();
        $this->assignation['tag'] = $parentTag;

        return $this->render('@RoadizRozier/tags/add-multiple.html.twig', $this->assignation);
    }
}
