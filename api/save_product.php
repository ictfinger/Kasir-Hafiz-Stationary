<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$barcode = $_POST['barcode'] ?? '';
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$category = $_POST['category'] ?? '';

// Validation
if (empty($barcode) || empty($name) || empty($price) || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

if ($price < 0 || $stock < 0) {
    echo json_encode(['success' => false, 'message' => 'Harga dan stok harus >= 0']);
    exit;
}

$conn = getConnection();

// Check if barcode already exists
$stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Barcode sudah digunakan']);
    exit;
}
$stmt->close();

// Insert product
$stmt = $conn->prepare("INSERT INTO products (barcode, name, price, stock, category) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdis", $barcode, $name, $price, $stock, $category);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Produk berhasil disimpan', 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan produk: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
