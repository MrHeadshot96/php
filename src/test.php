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
            $searcher = new JSONSearcher($file);
            $results = $searcher->searchItemName($searchString);
            break;
        case "gzip":
            $searcher = new GZIPSearcher($file);
            $results = $searcher->searchItemName($searchString);
            break;
        case "zip":
            $searcher = new ZIPSearcher($file);
            $results = $searcher->searchItemName($searchString);
            break;
        case "xml":
            $searcher = new XMLSearcher($file);
            $results = $searcher->searchItemName($searchString);
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
