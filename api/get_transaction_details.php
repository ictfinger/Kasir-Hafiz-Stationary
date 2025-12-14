<?php
require_once '../config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
    exit;
}

$conn = getConnection();

// Get transaction details
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

$transaction = $result->fetch_assoc();
$stmt->close();

// Get transaction items
$stmt = $conn->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'transaction' => $transaction,
    'items' => $items
]);
?>
