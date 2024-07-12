<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm;

use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CustomFormAnswerSerializer
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @throws \Exception
     */
    public function toSimpleArray(CustomFormAnswer $answer): array
    {
        $answers = [
            'ip' => $answer->getIp(),
            'submitted.date' => $answer->getSubmittedAt()
        ];
        /** @var CustomFormFieldAttribute $answerAttr */
        foreach ($answer->getAnswerFields() as $answerAttr) {
            $field = $answerAttr->getCustomFormField();
            if ($field->isDocuments()) {
                $answers[$field->getLabel()] = implode(PHP_EOL, $answerAttr->getDocuments()->map(function (Document $document) {
                    return $this->urlGenerator->generate('documentsDownloadPage', [
                        'documentId' => $document->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                })->toArray());
            } else {
                $answers[$field->getLabel()] = $answerAttr->getValue();
            }
        }
        return $answers;
    }
}
