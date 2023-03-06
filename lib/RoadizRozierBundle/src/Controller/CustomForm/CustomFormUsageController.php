<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Component\HttpFoundation\Response;
use Themes\Rozier\RozierApp;

final class CustomFormUsageController extends RozierApp
{
    public function usageAction(CustomForm $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        $customForm = $id;
        $this->assignation['customForm'] = $customForm;
        $this->assignation['usages'] = $customForm->getNodes();

        return $this->render('@RoadizRozier/custom-forms/usage.html.twig', $this->assignation);
    }
}
