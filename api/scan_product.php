<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$barcode = $_POST['barcode'] ?? '';

if (empty($barcode)) {
    echo json_encode(['success' => false, 'message' => 'Barcode tidak boleh kosong']);
    exit;
}

$conn = getConnection();

// Search product by barcode
$stmt = $conn->prepare("SELECT id, barcode, name, price, stock FROM products WHERE barcode = ?");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    if ($product['stock'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stok produk habis',
            'product' => $product
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'product' => $product
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Produk dengan barcode ' . htmlspecialchars($barcode) . ' tidak ditemukan'
    ]);
}

$stmt->close();
$conn->close();
?>
