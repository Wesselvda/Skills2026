<?php
    $uploadedFiles = $_FILES['folderFiles'] ?? null;
    $folderName = 'folder';

    if ($uploadedFiles && isset($uploadedFiles['name']) && is_array($uploadedFiles['name'])) {
        foreach ($uploadedFiles['full_path'] ?? $uploadedFiles['name'] as $path) {
            $path = ltrim(str_replace('\\', '/', $path), '/');
            $parts = explode('/', $path);

            if (count($parts) > 1 && $parts[0] !== '') {
                $folderName = $parts[0];
                break;
            }
        }

        $zip = new ZipArchive();
        $zipFileName = $folderName . '.zip';
        $zipFilePath = __DIR__ . DIRECTORY_SEPARATOR . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $fileCount = count($uploadedFiles['name']);

            for ($index = 0; $index < $fileCount; $index++) {
                $zip->addFile(
                    $uploadedFiles['tmp_name'][$index],
                    ltrim(str_replace('\\', '/', $uploadedFiles['full_path'][$index] ?? $uploadedFiles['name'][$index]), '/')
                );
            }

            $zip->close();

            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Type: application/zip');
            readfile($zipFilePath);
            unlink($zipFilePath);
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C1</title>
</head>
<body>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="file" id="folderInput" name="folderFiles[]" webkitdirectory directory multiple>
        <button type="submit">Compress</button>
    </form>
</body>
</html>