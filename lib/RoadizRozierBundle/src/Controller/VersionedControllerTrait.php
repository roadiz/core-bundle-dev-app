<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Exception\UnexpectedValueException;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use RZ\Roadiz\CoreBundle\Repository\UserLogEntryRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

trait VersionedControllerTrait
{
    protected bool $isReadOnly = false;

    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * @return $this
     */
    public function setIsReadOnly(bool $isReadOnly): self
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    protected function handleVersions(Request $request, PersistableInterface $entity, array &$assignation): ?Response
    {
        /** @var UserLogEntryRepository $repo */
        $repo = $this->getDoctrine()->getRepository(UserLogEntry::class);
        $logs = $repo->getLogEntries($entity);
        $versionNumber = $request->get('version', null);

        if (
            \is_numeric($versionNumber)
            && intval($versionNumber) > 0
        ) {
            try {
                $versionNumber = intval($versionNumber);
                $repo->revert($entity, $versionNumber);
                $this->isReadOnly = true;
                $assignation['currentVersionNumber'] = $versionNumber;
                /** @var UserLogEntry $log */
                foreach ($logs as $log) {
                    if ($log->getVersion() === $versionNumber) {
                        $assignation['currentVersion'] = $log;
                    }
                }
                $revertForm = $this->createNamedFormBuilder('revertVersion')
                    ->add('version', HiddenType::class, ['data' => $versionNumber])
                    ->getForm();
                $revertForm->handleRequest($request);

                if ($revertForm->isSubmitted() && $revertForm->isValid()) {
                    $this->getDoctrine()->getManager()->persist($entity);
                    $this->onPostUpdate($entity, $request);

                    return $this->getPostUpdateRedirection($entity);
                }
                $assignation['revertForm'] = $revertForm->createView();
            } catch (UnexpectedValueException) {
                throw new ResourceNotFoundException();
            }
        }

        $assignation['versions'] = $logs;

        return null;
    }

    abstract protected function onPostUpdate(PersistableInterface $entity, Request $request): void;

    abstract protected function getPostUpdateRedirection(PersistableInterface $entity): ?Response;

    /**
     * @deprecated
     */
    abstract protected function getDoctrine(): ManagerRegistry;

    abstract protected function createNamedFormBuilder(string $name = 'form', mixed $data = null, array $options = []): FormBuilderInterface;
}
