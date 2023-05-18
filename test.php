<?php
$host = "Local";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "items";
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file = $_FILES["fileInput"]["tmp_name"];
    $searchString = $_POST["stringInput"];
    $fileName = $_FILES["fileInput"]["name"];

    // Check if the file uploaded successfully
    if ($file === "") {
        echo "No file was uploaded.";
        exit;
    }
    // Check file type of file
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    switch ($fileExtension) {
        case "json":
            $jsonData = file_get_contents($file);
            //parse
            $parsedData = json_decode($jsonData, true);
            if ($parsedData === null) {
                echo "Invalid JSON file";
                exit;
            }
            $results = array();
            //search for string
            function searchItemName($data, $searchString, &$results) {
                foreach ($data as $key => $value) {
                    if ($key === "item_name" && stripos($value, $searchString) !== false) {
                        $results[] = $value;
                    } elseif (is_array($value) || is_object($value)) {
                        searchItemName($value, $searchString, $results);
                    }
                }
            }
            searchItemName($parsedData, $searchString, $results);
            // Clean up code
            $cleanedData = array_map('htmlspecialchars', $parsedData);
            // ...
            break;
        case "gzip":
            $csvData = file_get_contents($file);
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

            $cleanedData = array_map('htmlspecialchars', $lines);

            break;
        case "zip":
            $tempDir = sys_get_temp_dir();
            $zip = new ZipArchive;
            if ($zip->open($file) === true) {
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
            $cleanedData = array_map('htmlspecialchars', $lines);
            break;
        case "xml":
            $xmlData = file_get_contents($file);
            $xmlData = file_get_contents($file);
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

            $cleanedData = htmlspecialchars($xmlData);

            break;
        default:
            echo "File not valid";
            exit;
    }
    if (!empty($results)) {
        $id = 0;
        echo "Search results for '$searchString':<br>";
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $insertStmt = $pdo->prepare("INSERT INTO items (id) VALUES (name)");
        foreach ($results as $result)
            $insertStmt->execute([$id]);
            $insertStmt->execute([$result]);
        $id = $id + 1;

        foreach ($results as $result) {
            echo htmlspecialchars($result) . "<br>";
        }
        $selectStmt = $pdo->query("SELECT name FROM items");
        $insertedNames = $selectStmt->fetchAll(PDO::FETCH_COLUMN);

        // Display the inserted names
        echo "Inserted names:<br>";
        foreach ($insertedNames as $name) {
            echo $name . "<br>";
        }
    }
    else {
        echo "No results found for '$searchString':<br>";
    }

}
?>
<!DOCTYPE html>
<html lang="">
<head>
    <title>File Upload Form</title>
</head>
<body>
<form action="test.php" method="post" enctype="multipart/form-data">
    <label for="stringInput">Select a string:</label>
    <input type="search" id="stringInput" name="stringInput">
    <label for="fileInput">Select a file:</label>
    <input type="file" id="fileInput" name="fileInput" accept=".json, .csv, .zip, .gzip, .xml">
    <br>
    <input type="submit" value="Upload">
</form>
</body>
</html>
