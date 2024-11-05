<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Xlsx;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated XLSX serialization is deprecated and will be removed in next major version.
 * Define basic serialize operations for XLSX data type.
 */
abstract class AbstractXlsxSerializer implements SerializerInterface
{
    public function __construct(protected readonly TranslatorInterface $translator)
    {
    }

    /**
     * Serializes data.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function serialize(mixed $obj): string
    {
        $data = $this->toArray($obj);
        $exporter = new XlsxExporter($this->translator);

        return $exporter->exportXlsx($data);
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
