<?php

namespace classes;

class ZIPSearcher
{
    private $file;
    private $results;

    public function __construct($file) {
        $this->file = $file;
        $this->results = array();
    }
    public function searchItemName($searchString)
    {
        $tempDir = sys_get_temp_dir();
        $zip = new ZipArchive;
        if ($zip->open($this->file) === true) {
            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            echo "Failed to open the ZIP file.";
            exit;
        }
        $extractedFiles = glob($tempDir . "/*.csv");
        $csvData = file_get_contents($extractedFiles[0]);
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