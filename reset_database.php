<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Database - Kasir Hafiz Stationary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="bi bi-arrow-clockwise"></i> Reset Database</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // Database configuration
                        $host = 'localhost';
                        $user = 'root';
                        $pass = '';
                        $dbname = 'kasir_hafiz_stationary';

                        // Connect to MySQL server
                        $conn = @new mysqli($host, $user, $pass);

                        if ($conn->connect_error) {
                            echo '<div class="alert alert-danger">Koneksi gagal: ' . $conn->connect_error . '</div>';
                            exit;
                        }

                        // Create database if not exists
                        $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Database siap</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
                            exit;
                        }

                        // Connect to database
                        $conn->close();
                        $conn = new mysqli($host, $user, $pass, $dbname);
                        $conn->set_charset("utf8mb4");
                        
                        if ($conn->connect_error) {
                            echo '<div class="alert alert-danger">Error: ' . $conn->connect_error . '</div>';
                            exit;
                        }

                        // Drop products table if exists
                        $sql = "DROP TABLE IF EXISTS products";
                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Tabel lama berhasil dihapus</div>';
                        }

                        // Create products table with MyISAM engine
                        $sql = "CREATE TABLE products (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            barcode VARCHAR(255) UNIQUE NOT NULL,
                            name VARCHAR(255) NOT NULL,
                            price DECIMAL(10,2) NOT NULL,
                            stock INT NOT NULL DEFAULT 0,
                            category VARCHAR(100) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            INDEX idx_barcode (barcode),
                            INDEX idx_category (category)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Tabel products berhasil dibuat dengan engine MyISAM</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error membuat tabel: ' . $conn->error . '</div>';
                            exit;
                        }

                        // Create transactions table
                        $sql = "CREATE TABLE transactions (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            transaction_code VARCHAR(50) UNIQUE NOT NULL,
                            total_amount DECIMAL(10,2) NOT NULL,
                            total_items INT NOT NULL,
                            payment_amount DECIMAL(10,2) NOT NULL,
                            change_amount DECIMAL(10,2) NOT NULL,
                            payment_method VARCHAR(50) NOT NULL DEFAULT 'Tunai',
                            cashier_name VARCHAR(100) NOT NULL DEFAULT 'Admin',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_transaction_code (transaction_code),
                            INDEX idx_created_at (created_at)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Tabel transactions berhasil dibuat</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error membuat tabel transactions: ' . $conn->error . '</div>';
                            exit;
                        }

                        // Create transaction_items table
                        $sql = "CREATE TABLE transaction_items (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            transaction_id INT NOT NULL,
                            product_id INT NOT NULL,
                            barcode VARCHAR(255) NOT NULL,
                            product_name VARCHAR(255) NOT NULL,
                            price DECIMAL(10,2) NOT NULL,
                            quantity INT NOT NULL,
                            subtotal DECIMAL(10,2) NOT NULL,
                            INDEX idx_transaction_id (transaction_id),
                            INDEX idx_product_id (product_id)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Tabel transaction_items berhasil dibuat</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error membuat tabel transaction_items: ' . $conn->error . '</div>';
                            exit;
                        }

                        // Insert sample data
                        $sampleProducts = [
                            ['8992388101127', 'Pulpen Standard Hitam', 3000, 100, 'Alat Tulis'],
                            ['8992388101134', 'Buku Tulis 48 Halaman', 5000, 50, 'Buku'],
                            ['8992388101141', 'Penghapus Karet Putih', 2000, 75, 'Alat Tulis'],
                            ['8992388101158', 'Penggaris 30cm', 4000, 60, 'Alat Tulis'],
                            ['8992388101165', 'Pensil 2B', 2500, 120, 'Alat Tulis']
                        ];

                        $stmt = $conn->prepare("INSERT INTO products (barcode, name, price, stock, category) VALUES (?, ?, ?, ?, ?)");
                        
                        foreach ($sampleProducts as $product) {
                            $stmt->bind_param("ssdis", $product[0], $product[1], $product[2], $product[3], $product[4]);
                            $stmt->execute();
                        }
                        
                        echo '<div class="alert alert-success"><i class="bi bi-check-circle"></i> 5 produk contoh berhasil ditambahkan</div>';
                        $stmt->close();
                        $conn->close();
                        ?>
                        
                        <div class="alert alert-info mt-4">
                            <h5><i class="bi bi-info-circle"></i> Reset Selesai!</h5>
                            <p class="mb-0">Database telah di-reset dan siap digunakan. Semua data lama telah dihapus.</p>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-house-door"></i> Ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
