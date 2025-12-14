<?php
require_once '../config.php';

header('Content-Type: application/json');

// Generate unique EAN-13 barcode
function generateEAN13() {
    $conn = getConnection();
    $maxAttempts = 100;
    
    for ($i = 0; $i < $maxAttempts; $i++) {
        // Generate 12 random digits
        $code = '';
        for ($j = 0; $j < 12; $j++) {
            $code .= rand(0, 9);
        }
        
        // Calculate check digit
        $sum = 0;
        for ($j = 0; $j < 12; $j++) {
            $digit = (int)$code[$j];
            $sum += ($j % 2 === 0) ? $digit : $digit * 3;
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        $barcode = $code . $checkDigit;
        
        // Check if barcode already exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return $barcode;
        }
        
        $stmt->close();
    }
    
    $conn->close();
    return null;
}

$barcode = generateEAN13();

if ($barcode) {
    echo json_encode([
        'success' => true,
        'barcode' => $barcode
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal generate barcode unik'
    ]);
}
?>
