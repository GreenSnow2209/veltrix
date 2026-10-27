<?php

namespace Veltrix\Grid\Exporters;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Veltrix\Grid\Grid;

class CsvExporter
{
    protected Grid $grid;

    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    public function export(): StreamedResponse
    {
        $filename = 'export-' . date('Y-m-d-His') . '.csv';

        $columns = $this->grid->getColumns();
        $headers = array_map(fn($col) => $col->getLabel(), array_values($columns));

        $query = clone $this->grid->model()->getQuery();

        $response = new StreamedResponse(function () use ($query, $columns, $headers) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Write CSV headers
            fputcsv($handle, $headers);

            // Stream chunk by chunk for minimal memory usage
            $query->chunk(500, function ($records) use ($handle, $columns) {
                foreach ($records as $row) {
                    $line = [];
                    foreach ($columns as $column) {
                        $val = $column->renderValue($row);
                        // Strip HTML tags for clean CSV output
                        $line[] = is_string($val) ? strip_tags($val) : $val;
                    }
                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
