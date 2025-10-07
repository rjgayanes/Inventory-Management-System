<?php
require __DIR__ . '/db.php';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/activity_logger.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Validate input
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
	http_response_code(400);
	echo 'Missing or invalid unit_id';
	exit;
}

$unitId = (int) $_GET['unit_id'];

// Fetch unit and item details
$sql = "SELECT 
			 iu.unit_ID,
			 iu.barcode,
			 iu.barcode_image,
			 i.item_name,
			 i.property_number
		FROM Item_Units iu
		JOIN Items i ON iu.item_ID = i.item_ID
		WHERE iu.unit_ID = ?
		LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $unitId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
	http_response_code(404);
	echo 'Unit not found';
	exit;
}

$barcodeString = (string) $row['barcode'];
$itemName = (string) $row['item_name'];
$propertyNumber = (string) $row['property_number'];
$existingImageWeb = (string) ($row['barcode_image'] ?? '');

$uploadDir = realpath(__DIR__ . '/../public/uploads/barcodes');
if ($uploadDir === false) {
	$uploadDir = __DIR__ . '/../public/uploads/barcodes';
}
if (!is_dir($uploadDir)) {
	@mkdir($uploadDir, 0755, true);
}

$filename = 'barcode_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $barcodeString) . '.png';
$absPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
$webPath = './uploads/barcodes/' . $filename;

// If exists, try to serve existing; otherwise regenerate
if (!file_exists($absPath)) {
	$generator = new BarcodeGeneratorPNG();
	try {
		$barcodeData = $generator->getBarcode($barcodeString, $generator::TYPE_CODE_128, 3, 80);
	} catch (\Throwable $e) {
		http_response_code(500);
		echo 'Failed to generate barcode: ' . $e->getMessage();
		exit;
	}

	$barcodeImage = imagecreatefromstring($barcodeData);
	if ($barcodeImage === false) {
		http_response_code(500);
		echo 'Failed to create image from barcode data';
		exit;
	}

	$barcodeW = imagesx($barcodeImage);
	$barcodeH = imagesy($barcodeImage);

	$font = 5; // built-in GD font
	$padding = 12;
	$lineGap = 6;
	$topText1 = (string)$itemName;
	$topText2 = 'Property #: ' . (string)$propertyNumber;
	$bottomText = (string)$barcodeString;

	$text1W = imagefontwidth($font) * strlen($topText1);
	$text2W = imagefontwidth($font) * strlen($topText2);
	$text3W = imagefontwidth($font) * strlen($bottomText);
	$maxTextW = max($text1W, $text2W, $text3W);

	$canvasW = max($barcodeW + 2 * $padding, $maxTextW + 2 * $padding);
	$topTextHeight = imagefontheight($font) * 2 + $lineGap; // two lines + gap
	$bottomTextHeight = imagefontheight($font);
	$canvasH = $padding + $topTextHeight + $barcodeH + $lineGap + $bottomTextHeight + $padding;

	$canvas = imagecreatetruecolor($canvasW, $canvasH);
	$white = imagecolorallocate($canvas, 255, 255, 255);
	$black = imagecolorallocate($canvas, 0, 0, 0);
	imagefill($canvas, 0, 0, $white);

	// Draw top texts centered
	$text1X = (int)(($canvasW - $text1W) / 2);
	$text1Y = $padding;
	imagestring($canvas, $font, $text1X, $text1Y, $topText1, $black);

	$text2X = (int)(($canvasW - $text2W) / 2);
	$text2Y = $text1Y + imagefontheight($font) + $lineGap;
	imagestring($canvas, $font, $text2X, $text2Y, $topText2, $black);

	// Copy barcode image centered
	$barcodeX = (int)(($canvasW - $barcodeW) / 2);
	$barcodeY = $text2Y + imagefontheight($font) + $padding;
	imagecopy($canvas, $barcodeImage, $barcodeX, $barcodeY, 0, 0, $barcodeW, $barcodeH);

	// Draw bottom text (barcode string)
	$text3X = (int)(($canvasW - $text3W) / 2);
	$text3Y = $barcodeY + $barcodeH + $lineGap;
	imagestring($canvas, $font, $text3X, $text3Y, $bottomText, $black);

	// Save composed PNG
	imagepng($canvas, $absPath);
	imagedestroy($barcodeImage);
	imagedestroy($canvas);

	// Persist path if not set
	if (empty($existingImageWeb)) {
		$upd = $conn->prepare('UPDATE Item_Units SET barcode_image = ? WHERE unit_ID = ?');
		$upd->bind_param('si', $webPath, $unitId);
		$upd->execute();
		$upd->close();
	}
}

// Stream the file as a download
if (!file_exists($absPath)) {
	http_response_code(404);
	echo 'Barcode image not found';
	exit;
}

// Clear any previous output
if (ob_get_level()) {
	ob_end_clean();
}

// Set proper headers for PNG download
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="barcode_' . $barcodeString . '.png"');
header('Content-Length: ' . filesize($absPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output the file
readfile($absPath);

// Log activity after successful stream
if (isset($_SESSION) && isset($_SESSION['user_ID'])) {
	log_activity($conn, (int)$_SESSION['user_ID'], 'Downloaded barcode image for unit_ID ' . $unitId);
}
exit;

?>


