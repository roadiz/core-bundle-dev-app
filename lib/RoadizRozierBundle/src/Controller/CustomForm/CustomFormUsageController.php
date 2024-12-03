<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Controller\CustomForm;

use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class CustomFormUsageController extends AbstractController
{
    public function usageAction(CustomForm $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCESS_CUSTOMFORMS');
        $customForm = $id;

        return $this->render('@RoadizRozier/custom-forms/usage.html.twig', [
            'customForm' => $customForm,
            'usages' => $customForm->getNodes(),
        ]);
    }
}
