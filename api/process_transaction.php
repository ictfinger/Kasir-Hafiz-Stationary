<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data from request body
$input = json_decode(file_get_contents('php://input'), true);

$cart = $input['cart'] ?? [];
$paymentAmount = $input['payment_amount'] ?? 0;
$paymentMethod = $input['payment_method'] ?? 'Tunai';
$cashierName = $input['cashier_name'] ?? 'Admin';

// Validation
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja kosong']);
    exit;
}

$conn = getConnection();

// Start transaction
$conn->begin_transaction();

try {
    $totalAmount = 0;
    $totalItems = 0;
    
    // Validate all items and check stock
    foreach ($cart as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Get current stock
        $stmt = $conn->prepare("SELECT stock, price, name FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Produk ID $productId tidak ditemukan");
        }
        
        $product = $result->fetch_assoc();
        
        if ($product['stock'] < $quantity) {
            throw new Exception("Stok {$product['name']} tidak cukup. Tersedia: {$product['stock']}, Diminta: $quantity");
        }
        
        $totalAmount += $product['price'] * $quantity;
        $totalItems += $quantity;
        
        $stmt->close();
    }
    
    // Validate payment
    if ($paymentAmount < $totalAmount) {
        throw new Exception("Pembayaran kurang. Total: Rp " . number_format($totalAmount, 0, ',', '.'));
    }
    
    $changeAmount = $paymentAmount - $totalAmount;
    
    // Generate transaction code
    $date = date('Ymd');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $transactionCode = "TRX-$date-$random";
    
    // Check if code exists, regenerate if needed
    $stmt = $conn->prepare("SELECT id FROM transactions WHERE transaction_code = ?");
    $stmt->bind_param("s", $transactionCode);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $transactionCode = "TRX-$date-$random";
    }
    $stmt->close();
    
    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (transaction_code, total_amount, total_items, payment_amount, change_amount, payment_method, cashier_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdiddss", $transactionCode, $totalAmount, $totalItems, $paymentAmount, $changeAmount, $paymentMethod, $cashierName);
    $stmt->execute();
    $transactionId = $stmt->insert_id;
    $stmt->close();
    
    // Insert transaction items and update stock
    $stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, barcode, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtUpdateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    
    foreach ($cart as $item) {
        $productId = $item['product_id'];
        $barcode = $item['barcode'];
        $productName = $item['name'];
        $price = $item['price'];
        $quantity = $item['quantity'];
        $subtotal = $price * $quantity;
        
        // Insert item
        $stmt->bind_param("iissdid", $transactionId, $productId, $barcode, $productName, $price, $quantity, $subtotal);
        $stmt->execute();
        
        // Update stock
        $stmtUpdateStock->bind_param("ii", $quantity, $productId);
        $stmtUpdateStock->execute();
    }
    
    $stmt->close();
    $stmtUpdateStock->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi berhasil diproses',
        'transaction' => [
            'id' => $transactionId,
            'code' => $transactionCode,
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
            'payment_amount' => $paymentAmount,
            'change_amount' => $changeAmount,
            'payment_method' => $paymentMethod,
            'cashier_name' => $cashierName,
            'items' => $cart
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
