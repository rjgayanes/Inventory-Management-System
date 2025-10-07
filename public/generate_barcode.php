<?php
require '../src/db.php';
require_once __DIR__ . '/../src/activity_logger.php';
require '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

header("Content-Type: application/json");

// 1. Decode incoming JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['item_ID']) || !isset($data['quantity'])) {
    echo json_encode([
        "success" => false,
        "error"   => "Invalid request data"
    ]);
    exit;
}

$item_ID  = intval($data['item_ID']);
$quantity = intval($data['quantity']);

$generator = new BarcodeGeneratorPNG();

// Ensure required PHP extensions are available
if (!extension_loaded('gd')) {
    echo json_encode([
        "success" => false,
        "error" => "PHP GD extension is not enabled. Enable ext-gd in php.ini."
    ]);
    exit;
}

// Make sure upload directory exists
$uploadDir = __DIR__ . "/uploads/barcodes/";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        echo json_encode([
            "success" => false,
            "error" => "Failed to create upload directory"
        ]);
        exit;
    }
}

// --- Step 1: Fetch item details for more meaningful barcode ---
$stmt = $conn->prepare("
    SELECT i.item_name, i.property_number, i.unit_of_measure, et.type_name 
    FROM Items i 
    LEFT JOIN Equipment_types et ON i.type_ID = et.type_ID 
    WHERE i.item_ID = ?
");
$stmt->bind_param("i", $item_ID);
$stmt->execute();
$stmt->bind_result($item_name, $property_number, $unit_of_measure, $type_name);
$stmt->fetch();
$stmt->close();

// Normalize
$uom = strtolower(trim($unit_of_measure));

// --- Step 2: Define non-multiplying UOMs ---
$nonMultiplyingUOMs = [
    "sq. m", "sq. ft", "sq. yd",
    "m³", "ft³", "yd³", "kg", "g", "mt", "lb", "l", "ml", "gal",
    "m", "cm", "mm", "in", "ft", "yd"
];

// If UOM is non-multiplying → force quantity = 1
if (in_array($uom, $nonMultiplyingUOMs)) {
    $quantity = 1;
}

$barcodes = [];
$generatedFiles = [];

for ($i = 1; $i <= $quantity; $i++) {
    // Create a more meaningful barcode string with item info
    $itemCode = substr(strtoupper(preg_replace('/[^A-Z0-9]/', '', $item_name)), 0, 4);
    $typeCode = substr(strtoupper(preg_replace('/[^A-Z0-9]/', '', $type_name)), 0, 3);
    
    // Generate unique but meaningful barcode string
    $barcodeString = sprintf(
        "ITEM-%s-%s-%s-%04d", 
        $itemCode, 
        $typeCode, 
        date('ym'), 
        $item_ID + $i
    );

    // Generate barcode image
    try {
        $barcodeData = $generator->getBarcode($barcodeString, $generator::TYPE_CODE_128, 3, 80);
    } catch (\Throwable $e) {
        echo json_encode([
            "success" => false,
            "error" => "Failed to generate barcode: " . $e->getMessage()
        ]);
        exit;
    }

	// Compose styled image with item name, property number, barcode graphic, and barcode text
	$filename = "barcode_" . $barcodeString . ".png";
	$filepath = $uploadDir . $filename;

	$barcodeImage = imagecreatefromstring($barcodeData);
	if ($barcodeImage === false) {
		echo json_encode([
			"success" => false,
			"error" => "Failed to create image from barcode data"
		]);
		exit;
	}

	$barcodeW = imagesx($barcodeImage);
	$barcodeH = imagesy($barcodeImage);

	$font = 5; // built-in GD font
	$padding = 12;
	$lineGap = 6;
	$topText1 = (string)$item_name;
	$topText2 = "Property #: " . (string)$property_number;
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
	imagepng($canvas, $filepath);
	imagedestroy($barcodeImage);
	imagedestroy($canvas);

	$generatedFiles[] = $filepath;

    // Store relative path for frontend (web accessible)
    $filepathWeb = "./uploads/barcodes/" . $filename;

    // Insert into Item_Units
    $stmt = $conn->prepare("INSERT INTO Item_Units (item_ID, barcode, barcode_image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $item_ID, $barcodeString, $filepathWeb);
    $stmt->execute();
    $stmt->close();

    // Collect for response - include item info for display
    $barcodes[] = [
        "string" => $barcodeString,
        "image"  => $filepathWeb,
        "item_name" => $item_name,
        "property_number" => $property_number
    ];
}

// Optionally create a ZIP of all generated barcode images for easy download
$zipWebPath = null;
if (!empty($generatedFiles) && class_exists('ZipArchive')) {
	$zip = new ZipArchive();
	$zipFilename = sprintf('barcodes_%d_%s.zip', $item_ID, date('Ymd_His'));
	$zipPath = $uploadDir . $zipFilename;
	if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
		foreach ($generatedFiles as $absPath) {
			$localName = basename($absPath);
			$zip->addFile($absPath, $localName);
		}
		$zip->close();
		$zipWebPath = './uploads/barcodes/' . $zipFilename;
	}
}


// Log activity: barcode(s) generated
if (isset($_SESSION) && isset($_SESSION['user_ID'])) {
	$unitsCount = count($barcodes);
	$logMsg = $unitsCount === 1
		? 'Generated 1 barcode for item_ID ' . $item_ID
		: 'Generated ' . $unitsCount . ' barcodes for item_ID ' . $item_ID;
	log_activity($conn, (int)$_SESSION['user_ID'], $logMsg);
}

// Final JSON response
echo json_encode([
	"success"  => true,
	"message"  => "Barcodes generated and saved successfully.",
	"barcodes" => $barcodes,
	"download_zip" => $zipWebPath
]);