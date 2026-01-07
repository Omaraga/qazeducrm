<?php

namespace app\services\reports;

use app\components\reports\ReportInterface;
use app\components\reports\ReportFilterDTO;
use Yii;

/**
 * Сервис экспорта отчетов
 *
 * Экспортирует данные отчета в различные форматы.
 * По умолчанию использует CSV (совместим с Excel).
 *
 * Для полноценной поддержки XLSX установите:
 * composer require phpoffice/phpspreadsheet
 */
class ReportExportService
{
    private ReportInterface $report;
    private ReportFilterDTO $filter;

    public function __construct(ReportInterface $report, ReportFilterDTO $filter)
    {
        $this->report = $report;
        $this->filter = $filter;
    }

    /**
     * Экспорт в формате CSV (открывается в Excel)
     */
    public function exportCsv(): string
    {
        $data = $this->report->getData($this->filter);
        $columns = $this->report->getColumns();
        $summary = $this->report->getSummary($this->filter);
        $summaryConfig = $this->report->getSummaryConfig();

        // BOM для корректного отображения UTF-8 в Excel
        $output = "\xEF\xBB\xBF";

        // Заголовок отчета
        $output .= $this->report->getTitle() . "\n";
        $output .= "Период: " . $this->filter->getPeriodLabel() . "\n";
        $output .= "Дата формирования: " . date('d.m.Y H:i') . "\n";
        $output .= "\n";

        // Сводка
        if (!empty($summary) && !empty($summaryConfig)) {
            $output .= "СВОДКА\n";
            foreach ($summaryConfig as $config) {
                $key = $config['key'];
                $value = $summary[$key] ?? 0;
                $formattedValue = $this->formatValue($value, $config['format'] ?? null);
                $output .= $this->escapeCsv($config['label']) . ";" . $this->escapeCsv($formattedValue) . "\n";
            }
            $output .= "\n";
        }

        // Заголовки таблицы
        $output .= "ДАННЫЕ\n";
        $headers = [];
        foreach ($columns as $column) {
            $headers[] = $this->escapeCsv($column['label']);
        }
        $output .= implode(';', $headers) . "\n";

        // Данные
        foreach ($data as $row) {
            $values = [];
            foreach ($columns as $column) {
                $field = $column['field'];
                $value = $row[$field] ?? '';
                $format = $column['format'] ?? null;
                $formattedValue = $this->formatValue($value, $format);
                $values[] = $this->escapeCsv($formattedValue);
            }
            $output .= implode(';', $values) . "\n";
        }

        // Итого
        if (!empty($summary) && isset($summary['total'])) {
            $output .= "\n";
            $output .= "ИТОГО;" . $this->formatValue($summary['total'], 'currency') . "\n";
        }

        return $output;
    }

    /**
     * Экспорт в XLSX (требует phpspreadsheet)
     */
    public function exportXlsx(): ?string
    {
        // Проверяем наличие PhpSpreadsheet
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            // Возвращаем CSV как fallback
            Yii::warning('PhpSpreadsheet not installed, falling back to CSV export');
            return $this->exportCsv();
        }

        return $this->generateXlsx();
    }

    /**
     * Генерация XLSX с использованием PhpSpreadsheet
     */
    private function generateXlsx(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($this->report->getTitle(), 0, 31));

        $data = $this->report->getData($this->filter);
        $columns = $this->report->getColumns();
        $summary = $this->report->getSummary($this->filter);
        $summaryConfig = $this->report->getSummaryConfig();

        $row = 1;

        // Заголовок
        $sheet->setCellValue('A' . $row, $this->report->getTitle());
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        $sheet->setCellValue('A' . $row, 'Период: ' . $this->filter->getPeriodLabel());
        $row++;

        $sheet->setCellValue('A' . $row, 'Дата формирования: ' . date('d.m.Y H:i'));
        $row += 2;

        // Сводка
        if (!empty($summary) && !empty($summaryConfig)) {
            $sheet->setCellValue('A' . $row, 'СВОДКА');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            foreach ($summaryConfig as $config) {
                $key = $config['key'];
                $value = $summary[$key] ?? 0;
                $formattedValue = $this->formatValue($value, $config['format'] ?? null);
                $sheet->setCellValue('A' . $row, $config['label']);
                $sheet->setCellValue('B' . $row, $formattedValue);
                $row++;
            }
            $row++;
        }

        // Заголовки таблицы
        $sheet->setCellValue('A' . $row, 'ДАННЫЕ');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $col = 'A';
        foreach ($columns as $column) {
            $sheet->setCellValue($col . $row, $column['label']);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        $row++;

        // Данные
        foreach ($data as $dataRow) {
            $col = 'A';
            foreach ($columns as $column) {
                $field = $column['field'];
                $value = $dataRow[$field] ?? '';
                $format = $column['format'] ?? null;
                $formattedValue = $this->formatValue($value, $format);
                $sheet->setCellValue($col . $row, $formattedValue);
                $col++;
            }
            $row++;
        }

        // Автоширина столбцов
        foreach (range('A', chr(ord('A') + count($columns) - 1)) as $columnLetter) {
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Генерируем файл
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Отправить файл пользователю
     */
    public function download(string $format = 'xlsx'): void
    {
        $filename = $this->generateFilename($format);

        if ($format === 'xlsx' && class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $this->exportXlsx();
        } else {
            // CSV (работает везде)
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . str_replace('.xlsx', '.csv', $filename) . '"');
            header('Cache-Control: max-age=0');
            echo $this->exportCsv();
        }

        exit;
    }

    /**
     * Генерация имени файла
     */
    private function generateFilename(string $format): string
    {
        $title = $this->transliterate($this->report->getTitle());
        $date = date('Y-m-d');
        return "report_{$title}_{$date}.{$format}";
    }

    /**
     * Форматирование значения
     */
    private function formatValue($value, ?string $format): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        switch ($format) {
            case 'currency':
                return number_format((float)$value, 0, '.', ' ') . ' ₸';

            case 'percent':
                return number_format((float)$value, 1) . '%';

            case 'date':
                return $value ? Yii::$app->formatter->asDate($value, 'php:d.m.Y') : '-';

            case 'datetime':
                return $value ? Yii::$app->formatter->asDatetime($value, 'php:d.m.Y H:i') : '-';

            case 'number':
                return number_format((float)$value, 0, '.', ' ');

            default:
                return (string)$value;
        }
    }

    /**
     * Экранирование для CSV
     */
    private function escapeCsv(string $value): string
    {
        // Заменяем двойные кавычки на две двойные кавычки
        $value = str_replace('"', '""', $value);

        // Если значение содержит спецсимволы, оборачиваем в кавычки
        if (preg_match('/[;"\n\r]/', $value)) {
            return '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Транслитерация для имени файла
     */
    private function transliterate(string $string): string
    {
        $converter = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
            ' ' => '_',
        ];

        $string = strtr($string, $converter);
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $string);
    }
}
