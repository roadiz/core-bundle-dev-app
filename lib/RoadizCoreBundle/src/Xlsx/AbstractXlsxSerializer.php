<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Xlsx;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Define basic serialize operations for XLSX data type.
 */
abstract class AbstractXlsxSerializer implements SerializerInterface
{
    protected TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Serializes data.
     *
     * @param mixed $obj
     *
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function serialize($obj): string
    {
        $data = $this->toArray($obj);
        $exporter = new XlsxExporter($this->translator);

        return $exporter->exportXlsx($data);
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
