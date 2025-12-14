<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$category = $_POST['category'] ?? '';

// Validation
if (empty($id) || empty($name) || empty($price) || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

if ($price < 0 || $stock < 0) {
    echo json_encode(['success' => false, 'message' => 'Harga dan stok harus >= 0']);
    exit;
}

$conn = getConnection();

// Update product
$stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, category = ? WHERE id = ?");
$stmt->bind_param("sdisi", $name, $price, $stock, $category, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Produk berhasil diupdate']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Tidak ada perubahan data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate produk: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
