<?php

declare(strict_types=1);

namespace Themes\Rozier\Traits;

use Gedmo\Exception\UnexpectedValueException;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\UserLogEntry;
use RZ\Roadiz\CoreBundle\Repository\UserLogEntryRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
     * @return self
     */
    public function setIsReadOnly(bool $isReadOnly)
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    protected function handleVersions(Request $request, PersistableInterface $entity): ?Response
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
                $this->assignation['currentVersionNumber'] = $versionNumber;
                /** @var UserLogEntry $log */
                foreach ($logs as $log) {
                    if ($log->getVersion() === $versionNumber) {
                        $this->assignation['currentVersion'] = $log;
                    }
                }
                $revertForm = $this->createNamedFormBuilder('revertVersion')
                    ->add('version', HiddenType::class, ['data' => $versionNumber])
                    ->getForm();
                $revertForm->handleRequest($request);

                $this->assignation['revertForm'] = $revertForm->createView();

                if ($revertForm->isSubmitted() && $revertForm->isValid()) {
                    $this->em()->persist($entity);
                    $this->onPostUpdate($entity, $request);

                    return $this->getPostUpdateRedirection($entity);
                }
            } catch (UnexpectedValueException $e) {
                throw new ResourceNotFoundException();
            }
        }

        $this->assignation['versions'] = $logs;

        return null;
    }

    abstract protected function onPostUpdate(PersistableInterface $entity, Request $request): void;

    abstract protected function getPostUpdateRedirection(PersistableInterface $entity): ?Response;
}
