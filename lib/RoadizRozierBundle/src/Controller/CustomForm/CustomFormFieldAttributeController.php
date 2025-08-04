<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class CustomFormFieldAttributeController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    public function listAction(Request $request, int $customFormAnswerId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');

        /** @var CustomFormAnswer $customFormAnswer */
        $customFormAnswer = $this->managerRegistry
            ->getRepository(CustomFormAnswer::class)
            ->find($customFormAnswerId);
        $answers = $this->getAnswersByGroups($customFormAnswer->getAnswerFields());

        return $this->render('@RoadizRozier/custom-form-field-attributes/list.html.twig', [
            'fields' => $answers,
            'answer' => $customFormAnswer,
            'customFormId' => $customFormAnswer->getCustomForm()->getId(),
        ]);
    }

    protected function getAnswersByGroups(iterable $answers): array
    {
        $fieldsArray = [];

        /** @var CustomFormFieldAttribute $answer */
        foreach ($answers as $answer) {
            $groupName = $answer->getCustomFormField()->getGroupName();
            if (\is_string($groupName) && '' !== $groupName) {
                if (!isset($fieldsArray[$groupName]) || !\is_array($fieldsArray[$groupName])) {
                    $fieldsArray[$groupName] = [];
                }
                $fieldsArray[$groupName][] = $answer;
            } else {
                $fieldsArray[] = $answer;
            }
        }

        return $fieldsArray;
    }
}
