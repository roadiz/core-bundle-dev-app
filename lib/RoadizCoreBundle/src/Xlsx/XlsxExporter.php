<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Xlsx;

use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated XLSX serialization is deprecated and will be removed in next major version.
 */
class XlsxExporter
{
    public function __construct(protected readonly TranslatorInterface $translator)
    {
    }

    /**
     * Export an array of data to XLSX format.
     *
     * @param \IteratorAggregate|array $data
     * @param array                    $keys
     *
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportXlsx($data, $keys = [])
    {
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator("Roadiz CMS")
            ->setLastModifiedBy("Roadiz CMS")
            ->setCategory("");

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeRow = 1;
        $hasGlobalHeader = false;

        $headerStyles = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FF0000'],
                'size' => 11,
                'name' => 'Verdana',
            ],
            'width' => 50,
        ];

        /*
         * Add headers row
         */
        if (count($keys) > 0) {
            foreach ($keys as $key => $value) {
                $columnAlpha = Coordinate::stringFromColumnIndex($key + 1);
                $activeSheet->getStyle($columnAlpha . ($activeRow))->applyFromArray($headerStyles);
                $activeSheet->setCellValueByColumnAndRow($key + 1, $activeRow, $this->translator->trans($value));
            }
            $activeRow++;
            $hasGlobalHeader = true;
        }

        $headerkeys = $keys;

        foreach ($data as $answer) {
            /*
             * If headers have changed
             * we print them
             */
            if (
                false === $hasGlobalHeader &&
                $headerkeys != array_keys($answer)
            ) {
                $headerkeys = array_keys($answer);
                foreach ($headerkeys as $key => $value) {
                    $columnAlpha = Coordinate::stringFromColumnIndex($key + 1);
                    $activeSheet->getStyle($columnAlpha . $activeRow)->applyFromArray($headerStyles);
                    if (\is_string($value)) {
                        $value = $this->translator->trans($value);
                    }
                    $activeSheet->setCellValueByColumnAndRow($key + 1, $activeRow, $value);
                }
                $activeRow++;
            }

            /*
             * Print values
             */
            $answer = array_values($answer);
            foreach ($answer as $k => $value) {
                $columnAlpha = Coordinate::stringFromColumnIndex($k + 1);

                if (
                    $value instanceof Collection ||
                    is_array($value)
                ) {
                    continue;
                }

                if ($value instanceof \DateTimeInterface) {
                    $value = Date::PHPToExcel($value);
                    $activeSheet->getStyle($columnAlpha . ($activeRow))
                        ->getNumberFormat()
                        ->setFormatCode('dd.mm.yyyy hh:MM:ss');
                }
                /*
                 * Set value into cell
                 */
                $activeSheet->getStyle($columnAlpha . $activeRow)->getAlignment()->setWrapText(true);
                $activeSheet->setCellValueByColumnAndRow($k + 1, $activeRow, $this->translator->trans((string) $value));
            }

            $activeRow++;
        }

        /*
         * autosize
         */
        foreach (range('A', $spreadsheet->getActiveSheet()->getHighestDataColumn()) as $col) {
            $spreadsheet->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setWidth(50);
        }


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $output = ob_get_clean();

        if (!\is_string($output)) {
            throw new \RuntimeException('Output is not a string.');
        }
        return $output;
    }
}
