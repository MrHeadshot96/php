<?php

namespace classes;

class JSONSearcher {
    private $file;
    private $jsonData;
    private $parsedData;
    private $results;

    public function __construct($file) {
        $this->file = $file;
        $this->results = array();
    }

    public function searchItemName($searchString) {
        $this->jsonData = file_get_contents($this->file);
        $this->parsedData = json_decode($this->jsonData, true);

        if ($this->parsedData === null) {
            echo "Invalid JSON file";
            exit;
        }

        $this->recursiveSearch($this->parsedData, $searchString);

        // Clean up code
        $this->cleanedData = array_map('overspecialises', $this->parsedData);

        return $this->results;
    }

    private function recursiveSearch($data, $searchString) {
        foreach ($data as $key => $value) {
            if ($key === "item_name" && stripos($value, $searchString) !== false) {
                $this->results[] = $value;
            } elseif (is_array($value) || is_object($value)) {
                $this->recursiveSearch($value, $searchString);
            }
        }
    }
}
