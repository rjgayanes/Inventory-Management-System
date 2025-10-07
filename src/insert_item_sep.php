<?php
session_start();
include './db.php';
require_once __DIR__ . '/activity_logger.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only Supply Officer can insert
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    echo json_encode(["success" => false, "error" => "Access denied"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'])) {
    $conn->begin_transaction();

    try {
        // ---------- FILE UPLOAD ----------
        $item_image = null;
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            // Corrected upload directory path
            $uploadDir = 'C:/xampp/htdocs/Inventory_Management_System/public/uploads/item_images/';
            // Corrected web path for database storage
            $webPath = '/Inventory_Management_System/public/uploads/item_images/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = uniqid() . "_" . basename($_FILES['item_image']['name']);
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['item_image']['tmp_name'], $targetFile)) {
                $item_image = $webPath . $filename;
            }
        }

        
        // ---------- INPUTS ----------
        $item_name = $_POST['item_name'];
        $description = $_POST['description'];
        $unit_of_measure = $_POST['unit_of_measure'];
        $unit_cost = (float)str_replace(',', '', $_POST['unit_cost']);
        $entered_quantity = (float)str_replace(',', '', $_POST['unit_quantity']);
        $unit_total_cost = (float)str_replace(',', '', $_POST['unit_total_cost']);
        $property_number = $_POST['property_number'];
        $item_classification = $_POST['item_classification'];
        $fund_ID = (int)$_POST['fund_ID'];
        $type_ID = (int)$_POST['type_ID'];
        $estimated_useful_life = $_POST['estimated_useful_life'];
        $acquisition_date = !empty($_POST['acquisition_date']) ? $_POST['acquisition_date'] : date('Y-m-d');

        // ---------- INSERT INTO ITEMS ----------
        $stmt = $conn->prepare("INSERT INTO Items (
            item_name, description, unit_of_measure, unit_cost, unit_quantity, unit_total_cost,
            property_number, item_classification, fund_ID, type_ID, acquisition_date, estimated_useful_life, item_image
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssdddssiisss",
            $item_name,
            $description,
            $unit_of_measure,
            $unit_cost,
            $entered_quantity,
            $unit_total_cost,
            $property_number,
            $item_classification,
            $fund_ID,
            $type_ID,
            $acquisition_date,
            $estimated_useful_life,
            $item_image
        );
        $stmt->execute();
        $item_ID = $stmt->insert_id;
        $stmt->close();

        $conn->commit();
        echo json_encode([
            "success" => true, 
            "item_ID" => $item_ID, 
            "quantity" => $entered_quantity
        ]);

        // Activity log
        log_activity($conn, (int)$_SESSION['user_ID'], 'Created item #' . $item_ID . ' - ' . $item_name);

        // And modify the catch block:
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Insert item error: " . $e->getMessage());
            echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
        }
            exit;
        }

echo json_encode(["success" => false, "error" => "Invalid request"]);
exit;