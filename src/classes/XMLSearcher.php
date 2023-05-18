<?php

namespace classes;

class XMLSearcher
{
    private $file;
    private $results;

    public function __construct($file) {
        $this->file = $file;
        $this->results = array();
    }
    public function searchItemName($searchString)
    {
        $xmlData = file_get_contents($this->file);
        $xmlData = file_get_contents($this->file);
        $dom = new DOMDocument();
        libxml_disable_entity_loader(true); // Disable external entity loading for security
        $dom->loadXML($xmlData);

        $elementName = "item_name";
        $xpath = new DOMXPath($dom);
        $query = "//" . $elementName . "[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '" . strtolower($searchString) . "')]";
        $elements = $xpath->query($query);

        $results = array();
        foreach ($elements as $element) {
            $results[] = $element->nodeValue;
        }

        $this->cleanedData = htmlspecialchars($xmlData);
        return $this->results;
    }
}