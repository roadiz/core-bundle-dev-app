<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\EntityHandler\TranslationHandler;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationCreatedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent;
use RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent;
use RZ\Roadiz\CoreBundle\ListManager\EntityListManagerFactoryInterface;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use RZ\Roadiz\RozierBundle\Form\TranslationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
final class TranslationController extends AbstractController
{
    public function __construct(
        private readonly HandlerFactoryInterface $handlerFactory,
        private readonly EntityListManagerFactoryInterface $entityManagerFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogTrail $logTrail,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TRANSLATIONS');

        $assignation = [];
        $assignation['translations'] = [];

        $listManager = $this->entityManagerFactory->createAdminEntityListManager(
            Translation::class
        );
        $listManager->handle();

        $assignation['filters'] = $listManager->getAssignation();
        $translations = $listManager->getEntities();

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            // Make default forms
            $form = $this->formFactory
                ->createNamedBuilder('default_trans_'.$translation->getId(), FormType::class, $translation)
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var TranslationHandler $handler */
                $handler = $this->handlerFactory->getHandler($translation);
                $handler->makeDefault();
                $msg = $this->translator->trans('translation.%name%.made_default', ['%name%' => $translation->getName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $translation);
                $this->eventDispatcher->dispatch(new TranslationUpdatedEvent($translation));

                /*
                 * Force redirect to avoid resending form when refreshing page
                 */
                return $this->redirectToRoute(
                    'translationsHomePage'
                );
            }

            $assignation['translations'][] = [
                'translation' => $translation,
                'defaultForm' => $form->createView(),
            ];
        }

        return $this->render('@RoadizRozier/translations/list.html.twig', $assignation);
    }

    public function editAction(Request $request, int $translationId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TRANSLATIONS');

        /** @var Translation|null $translation */
        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->find($translationId);

        if (null === $translation) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(TranslationType::class, $translation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManagerForClass(Translation::class)?->flush();
            $msg = $this->translator->trans('translation.%name%.updated', ['%name%' => $translation->getName()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $translation);

            $this->eventDispatcher->dispatch(new TranslationUpdatedEvent($translation));

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute(
                'translationsEditPage',
                ['translationId' => $translation->getId()]
            );
        }

        return $this->render('@RoadizRozier/translations/edit.html.twig', [
            'translation' => $translation,
            'form' => $form->createView(),
        ]);
    }

    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TRANSLATIONS');

        $translation = new Translation();

        $form = $this->createForm(TranslationType::class, $translation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerRegistry->getManagerForClass(Translation::class)?->persist($translation);
            $this->managerRegistry->getManagerForClass(Translation::class)?->flush();

            $msg = $this->translator->trans('translation.%name%.created', ['%name%' => $translation->getName()]);
            $this->logTrail->publishConfirmMessage($request, $msg, $translation);

            $this->eventDispatcher->dispatch(new TranslationCreatedEvent($translation));

            /*
             * Force redirect to avoid resending form when refreshing page
             */
            return $this->redirectToRoute('translationsHomePage');
        }

        return $this->render('@RoadizRozier/translations/add.html.twig', [
            'translation' => $translation,
            'form' => $form->createView(),
        ]);
    }

    public function deleteAction(Request $request, int $translationId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_TRANSLATIONS');

        /** @var Translation|null $translation */
        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->find($translationId);

        if (null === $translation) {
            throw new ResourceNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (false === $translation->isDefaultTranslation()) {
                $this->managerRegistry->getManagerForClass(Translation::class)?->remove($translation);
                $this->managerRegistry->getManagerForClass(Translation::class)?->flush();
                $msg = $this->translator->trans('translation.%name%.deleted', ['%name%' => $translation->getName()]);
                $this->logTrail->publishConfirmMessage($request, $msg, $translation);
                $this->eventDispatcher->dispatch(new TranslationDeletedEvent($translation));

                return $this->redirectToRoute('translationsHomePage');
            }
            $form->addError(new FormError($this->translator->trans(
                'translation.%name%.cannot_delete_default_translation',
                ['%name%' => $translation->getName()]
            )));
        }

        return $this->render('@RoadizRozier/translations/delete.html.twig', [
            'translation' => $translation,
            'form' => $form->createView(),
        ]);
    }
}
