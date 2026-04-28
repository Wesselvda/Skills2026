<?php
$sourcePath = __DIR__ . '/original.jpg';
$cellSize = 50;

if (isset($_GET['cell_size']) && ctype_digit((string) $_GET['cell_size'])) {
    $requestedCellSize = (int) $_GET['cell_size'];

    if ($requestedCellSize > 0) {
        $cellSize = $requestedCellSize;
    }
}

$sourceImage = imagecreatefromjpeg($sourcePath);
$width = imagesx($sourceImage);
$height = imagesy($sourceImage);
$mosaicImage = imagecreatetruecolor($width, $height);

for ($y = 0; $y < $height; $y += $cellSize) {
    $blockHeight = min($cellSize, $height - $y);

    for ($x = 0; $x < $width; $x += $cellSize) {
        $blockWidth = min($cellSize, $width - $x);
        $redTotal = 0;
        $greenTotal = 0;
        $blueTotal = 0;
        $pixelCount = 0;

        for ($py = 0; $py < $blockHeight; $py++) {
            for ($px = 0; $px < $blockWidth; $px++) {
                $rgb = imagecolorat($sourceImage, $x + $px, $y + $py);
                $redTotal += intdiv($rgb, 256 * 256) % 256;
                $greenTotal += intdiv($rgb, 256) % 256;
                $blueTotal += $rgb % 256;
                $pixelCount++;
            }
        }

        $averageRed = (int) round($redTotal / $pixelCount);
        $averageGreen = (int) round($greenTotal / $pixelCount);
        $averageBlue = (int) round($blueTotal / $pixelCount);
        $color = imagecolorallocate($mosaicImage, $averageRed, $averageGreen, $averageBlue);
        imagefilledrectangle(
            $mosaicImage,
            $x,
            $y,
            $x + $blockWidth - 1,
            $y + $blockHeight - 1,
            $color
        );
    }
}

header('Content-Type: image/jpeg');
imagejpeg($mosaicImage);
exit;