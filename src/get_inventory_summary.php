<?php
session_start();
include './db.php';

// Check if user is logged in
if (!isset($_SESSION['user_ID']) || $_SESSION['role'] !== 'Supply Officer') {
    header("Location: login.php?error=Access+denied");
    exit();
}

$classification = $_GET['classification'] ?? 'all';

// Build query based on classification filter
$sql = "SELECT f.fund_name, et.type_name, i.item_classification,
               SUM(i.unit_total_cost) AS total_amount
        FROM Items i
        JOIN Fund_sources f ON i.fund_ID = f.fund_ID
        JOIN Equipment_types et ON i.type_ID = et.type_ID
        WHERE (? = 'all' OR i.item_classification = ?)
        GROUP BY f.fund_name, et.type_name, i.item_classification
        ORDER BY f.fund_name, et.type_name, i.item_classification";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $classification, $classification);
$stmt->execute();
$result = $stmt->get_result();

$fundData = [];
$grandTotal = 0;

while ($row = $result->fetch_assoc()) {
    $fund = $row['fund_name'];
    $type = $row['type_name'];
    $amount = $row['total_amount'] ?? 0;

    if (!isset($fundData[$fund])) {
        $fundData[$fund] = ['types' => [], 'total' => 0];
    }
    
    $fundData[$fund]['types'][$type] = $amount;
    $fundData[$fund]['total'] += $amount;
    $grandTotal += $amount;
}
?>

<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th class="text-center">Account Title</th>
            <th class="amount text-center">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($fundData)): ?>
            <tr>
                <td colspan="2" class="text-center">No data found for the selected classification</td>
            </tr>
        <?php else: ?>
            <?php foreach ($fundData as $fund => $data): ?>
                <tr>
                    <td colspan="2"><strong><?php echo htmlspecialchars($fund); ?></strong></td>
                </tr>
                <?php foreach ($data['types'] as $type => $amt): ?>
                    <tr>
                        <td style="padding-left:20px;"><?php echo htmlspecialchars($type); ?></td>
                        <td class="amount"><?php echo number_format($amt, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td>Total for <?php echo htmlspecialchars($fund); ?></td>
                    <td class="amount"><?php echo number_format($data['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td><strong>Grand Total</strong></td>
                <td class="amount"><strong><?php echo number_format($grandTotal, 2); ?></strong></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>