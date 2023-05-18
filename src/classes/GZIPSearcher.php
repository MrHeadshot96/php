<?php

namespace classes;

class GZIPSearcher
{
    private $file;
    private $results;
    public function __construct($file) {
        $this->file = $file;
        $this->results = array();
    }
    public function searchItemName($searchString){
        $csvData = file_get_contents($this->file);
        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvData);
        $extractedData = gzfile($tempFile);
        unlink($tempFile);

        // Read the extracted CSV data into $csvData variable
        $csvData = implode("", $extractedData);

        $lines = explode(PHP_EOL, $csvData);
        $header = str_getcsv(array_shift($lines));

        $searchColumn = "item_name";
        $columnIndex = array_search($searchColumn, $header);

        if ($columnIndex === false) {
            echo "The '$searchColumn'was not found.";
            exit;
        }

        $results = array();

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (isset($row[$columnIndex]) && stripos($row[$columnIndex], $searchString) !== false) {
                $results[] = $row[$columnIndex];
            }
        }

        $this->cleanedData = array_map('htmlspecialchars', $lines);
        return $this->results;
    }
}