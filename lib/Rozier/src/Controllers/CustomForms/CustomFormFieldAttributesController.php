<?php

declare(strict_types=1);

namespace Themes\Rozier\Controllers\CustomForms;

use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;
use Twig\Error\RuntimeError;

class CustomFormFieldAttributesController extends RozierApp
{
    /**
     * List every node-types.
     *
     * @param Request $request
     * @param int $customFormAnswerId
     *
     * @return Response
     * @throws RuntimeError
     */
    public function listAction(Request $request, int $customFormAnswerId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        /*
         * Manage get request to filter list
         */

        /** @var CustomFormAnswer $customFormAnswer */
        $customFormAnswer = $this->em()->find(CustomFormAnswer::class, $customFormAnswerId);
        $answers = $this->getAnswersByGroups($customFormAnswer->getAnswerFields());

        $this->assignation['fields'] = $answers;
        $this->assignation['answer'] = $customFormAnswer;
        $this->assignation['customFormId'] = $customFormAnswer->getCustomForm()->getId();

        return $this->render('@RoadizRozier/custom-form-field-attributes/list.html.twig', $this->assignation);
    }

    /**
     * @param iterable $answers
     * @return array
     */
    protected function getAnswersByGroups(iterable $answers): array
    {
        $fieldsArray = [];

        /** @var CustomFormFieldAttribute $answer */
        foreach ($answers as $answer) {
            $groupName = $answer->getCustomFormField()->getGroupName();
            if (\is_string($groupName) && $groupName !== '') {
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
