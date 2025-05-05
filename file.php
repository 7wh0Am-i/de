<?php
$upload_dir = "uploads/";

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], $upload_dir . basename($file['name']));
    echo "<p>Uploaded!</p>";
}

// Handle file delete
if (isset($_GET['delete'])) {
    $file_to_delete = basename($_GET['delete']);
    unlink($upload_dir . $file_to_delete);
    echo "<p>Deleted $file_to_delete</p>";
}

// List files
$files = array_diff(scandir($upload_dir), ['.', '..']);
?>

<h2>Upload File</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required />
    <button type="submit">Upload</button>
</form>

<h2>Files</h2>
<ul>
<?php foreach ($files as $file): ?>
    <li>
        <a href="<?= $upload_dir . $file ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
        | <a href="?delete=<?= urlencode($file) ?>" onclick="return confirm('Delete <?= $file ?>?')">Delete</a>
    </li>
<?php endforeach; ?>
</ul>
