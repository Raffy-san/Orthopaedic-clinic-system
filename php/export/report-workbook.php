<?php

function reportWorkbookColumnName(int $columnNumber): string
{
    $name = '';
    while ($columnNumber > 0) {
        $remainder = ($columnNumber - 1) % 26;
        $name = chr(65 + $remainder) . $name;
        $columnNumber = intdiv($columnNumber - 1, 26);
    }

    return $name;
}

function reportWorkbookXmlValue(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
}

function createReportWorkbook(
    string $reportTitle,
    string $clinicName,
    string $fromDate,
    string $toDate,
    array $columns,
    array $rows,
    array $summary,
    string $reportType
): string {
    if (!class_exists(ZipArchive::class)) {
        throw new RuntimeException('The PHP ZIP extension is required to generate XLSX files.');
    }

    $columnCount = count($columns);
    if ($columnCount === 0) {
        throw new RuntimeException('Cannot export a report without columns.');
    }

    $lastColumn = reportWorkbookColumnName($columnCount);
    $firstTableRow = $summary ? 9 : 7;
    $lastTableRow = $firstTableRow + count($rows);
    $tableRange = 'A' . $firstTableRow . ':' . $lastColumn . max($firstTableRow, $lastTableRow);

    $dateColumns = match ($reportType) {
        'appointments' => [0],
        'consultations' => [],
        'patients' => [2, 6],
        default => [],
    };
    $dateTimeColumns = match ($reportType) {
        'financial', 'consultations' => [0],
        'patients' => [6],
        default => [],
    };
    $timeColumns = $reportType === 'appointments' ? [1] : [];
    $currencyColumns = match ($reportType) {
        'financial' => [4],
        'consultations' => [5],
        default => [],
    };

    $appendCell = static function (int $rowNumber, int $columnNumber, mixed $value, int $styleId = 0, string $type = 'string'): string {
        $reference = reportWorkbookColumnName($columnNumber) . $rowNumber;
        if ($type === 'number' && is_numeric($value)) {
            return '<c r="' . $reference . '" s="' . $styleId . '"><v>' . (string) (0 + $value) . '</v></c>';
        }

        if ($type === 'date') {
            $date = DateTime::createFromFormat('!Y-m-d', substr((string) $value, 0, 10));
            if ($date) {
                $excelDate = (int) $date->diff(new DateTime('1899-12-30'))->format('%a');
                return '<c r="' . $reference . '" s="' . $styleId . '"><v>' . $excelDate . '</v></c>';
            }
        } elseif ($type === 'datetime') {
            try {
                $date = new DateTime((string) $value);
                $epoch = new DateTime('1899-12-30 00:00:00');
                $excelDate = ((float) $date->format('U') - (float) $epoch->format('U')) / 86400;
                return '<c r="' . $reference . '" s="' . $styleId . '"><v>' . number_format($excelDate, 10, '.', '') . '</v></c>';
            } catch (Exception) {
                // Keep malformed date values visible as text in the exported workbook.
            }
        } elseif ($type === 'time') {
            try {
                $time = new DateTime((string) $value);
                $seconds = ((int) $time->format('G') * 3600) + ((int) $time->format('i') * 60) + (int) $time->format('s');
                return '<c r="' . $reference . '" s="' . $styleId . '"><v>' . number_format($seconds / 86400, 10, '.', '') . '</v></c>';
            } catch (Exception) {
                // Keep malformed time values visible as text in the exported workbook.
            }
        }

        return '<c r="' . $reference . '" s="' . $styleId . '" t="inlineStr"><is><t xml:space="preserve">'
            . reportWorkbookXmlValue($value)
            . '</t></is></c>';
    };

    $sheetRows = [];
    $sheetRows[] = '<row r="1" ht="32" customHeight="1">' . $appendCell(1, 1, $clinicName, 1) . '</row>';
    $sheetRows[] = '<row r="2" ht="26" customHeight="1">' . $appendCell(2, 1, $reportTitle, 2) . '</row>';
    $sheetRows[] = '<row r="3">' . $appendCell(3, 1, 'Reporting period', 4)
        . $appendCell(3, 2, $fromDate . ' to ' . $toDate, 5) . '</row>';
    $sheetRows[] = '<row r="4">' . $appendCell(4, 1, 'Generated', 4)
        . $appendCell(4, 2, date('F j, Y g:i A'), 5) . '</row>';
    $sheetRows[] = '<row r="5"></row>';

    if ($summary) {
        $summaryLabels = [];
        $summaryValues = [];
        foreach ($summary as $summaryItem) {
            $summaryLabels[] = $summaryItem[0];
            $summaryValues[] = $summaryItem[1];
        }
        $labelCells = '';
        $valueCells = '';
        foreach ($summaryLabels as $index => $label) {
            $labelCells .= $appendCell(6, $index + 1, $label, 6);
            $valueStyle = $reportType === 'financial' && $index > 0 ? 7 : 8;
            $valueCells .= $appendCell(7, $index + 1, $summaryValues[$index], $valueStyle, 'number');
        }
        $sheetRows[] = '<row r="6" ht="20" customHeight="1">' . $labelCells . '</row>';
        $sheetRows[] = '<row r="7" ht="24" customHeight="1">' . $valueCells . '</row>';
        $sheetRows[] = '<row r="8"></row>';
    } else {
        $sheetRows[] = '<row r="6"></row>';
    }

    $headerCells = '';
    foreach ($columns as $index => $column) {
        $headerCells .= $appendCell($firstTableRow, $index + 1, $column, 3);
    }
    $sheetRows[] = '<row r="' . $firstTableRow . '" ht="24" customHeight="1">' . $headerCells . '</row>';

    foreach ($rows as $rowIndex => $row) {
        $rowNumber = $firstTableRow + $rowIndex + 1;
        $cells = '';
        foreach (array_values($row) as $columnIndex => $value) {
            $isAlternate = $rowIndex % 2 === 1;
            if (in_array($columnIndex, $currencyColumns, true)) {
                $styleId = $isAlternate ? 13 : 12;
                $type = 'number';
            } elseif (in_array($columnIndex, $dateTimeColumns, true)) {
                $styleId = $isAlternate ? 15 : 14;
                $type = 'datetime';
            } elseif (in_array($columnIndex, $dateColumns, true)) {
                $styleId = $isAlternate ? 11 : 10;
                $type = 'date';
            } elseif (in_array($columnIndex, $timeColumns, true)) {
                $styleId = $isAlternate ? 17 : 16;
                $type = 'time';
            } else {
                $styleId = $isAlternate ? 9 : 0;
                $type = 'string';
            }
            $cells .= $appendCell($rowNumber, $columnIndex + 1, $value, $styleId, $type);
        }
        $sheetRows[] = '<row r="' . $rowNumber . '">' . $cells . '</row>';
    }

    $columnWidths = match ($reportType) {
        'financial' => [20, 28, 22, 16, 18],
        'appointments' => [16, 14, 28, 28, 36, 16],
        'consultations' => [22, 28, 28, 38, 38, 18],
        'patients' => [18, 28, 16, 14, 18, 20, 22],
        default => array_fill(0, $columnCount, 20),
    };
    $columnsXml = '';
    foreach ($columns as $index => $_column) {
        $columnNumber = $index + 1;
        $width = $columnWidths[$index] ?? 20;
        $columnsXml .= '<col min="' . $columnNumber . '" max="' . $columnNumber . '" width="' . $width . '" customWidth="1"/>';
    }

    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . $firstTableRow . '" topLeftCell="A' . ($firstTableRow + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="20"/><cols>' . $columnsXml . '</cols>'
        . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
        . '<autoFilter ref="' . $tableRange . '"/>'
        . '<mergeCells count="2"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/></mergeCells>'
        . '</worksheet>';

    $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="4"><numFmt numFmtId="164" formatCode="&quot;₱&quot;#,##0.00"/><numFmt numFmtId="165" formatCode="mmm d, yyyy"/><numFmt numFmtId="166" formatCode="mmm d, yyyy h:mm AM/PM"/><numFmt numFmtId="167" formatCode="h:mm AM/PM"/></numFmts>'
        . '<fonts count="5"><font><sz val="11"/><name val="Aptos"/></font>'
        . '<font><b/><color rgb="FFFFFFFF"/><sz val="18"/><name val="Aptos Display"/></font>'
        . '<font><b/><color rgb="FF14532D"/><sz val="13"/><name val="Aptos"/></font>'
        . '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Aptos"/></font>'
        . '<font><color rgb="FF64748B"/><sz val="10"/><name val="Aptos"/></font></fonts>'
        . '<fills count="6"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF166534"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF15803D"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/><bgColor indexed="64"/></patternFill></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
        . '<border><left style="hair"><color rgb="FFE2E8F0"/></left><right style="hair"><color rgb="FFE2E8F0"/></right><top/><bottom style="hair"><color rgb="FFE2E8F0"/></bottom><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="18">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="3" fillId="3" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="3" fillId="3" borderId="0" xfId="0"/>'
        . '<xf numFmtId="164" fontId="3" fillId="3" borderId="0" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="0" fontId="3" fillId="3" borderId="0" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0"/>'
        . '<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="165" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="164" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="166" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="166" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="167" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '<xf numFmtId="167" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1"/>'
        . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

    $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>';
    $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';
    $packageRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';
    $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';

    $temporaryPath = tempnam(sys_get_temp_dir(), 'clinic-report-');
    if ($temporaryPath === false) {
        throw new RuntimeException('Unable to create a temporary workbook file.');
    }

    $archive = new ZipArchive();
    if ($archive->open($temporaryPath, ZipArchive::OVERWRITE) !== true) {
        unlink($temporaryPath);
        throw new RuntimeException('Unable to open a temporary workbook archive.');
    }

    $entries = [
        '[Content_Types].xml' => $contentTypesXml,
        '_rels/.rels' => $packageRelsXml,
        'xl/workbook.xml' => $workbookXml,
        'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
        'xl/worksheets/sheet1.xml' => $sheetXml,
        'xl/styles.xml' => $stylesXml,
    ];
    foreach ($entries as $path => $contents) {
        if (!$archive->addFromString($path, $contents)) {
            $archive->close();
            unlink($temporaryPath);
            throw new RuntimeException('Unable to write a workbook component.');
        }
    }
    if (!$archive->close()) {
        unlink($temporaryPath);
        throw new RuntimeException('Unable to finalize the workbook archive.');
    }

    return $temporaryPath;
}
