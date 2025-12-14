<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Database - Kasir Hafiz Stationary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Database Installation</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // Database configuration
                        $host = 'localhost';
                        $user = 'root';
                        $pass = '';
                        $dbname = 'kasir_hafiz_stationary';

                        // Disable strict error reporting for connection
                        mysqli_report(MYSQLI_REPORT_OFF);

                        // Create connection without database
                        $conn = @new mysqli($host, $user, $pass);

                        // Check connection
                        if ($conn->connect_error) {
                            $errorMsg = $conn->connect_error;
                            $errorCode = $conn->connect_errno;
                            
                            echo '<div class="alert alert-danger">';
                            echo '<h5><i class="bi bi-exclamation-triangle"></i> Koneksi Database Gagal!</h5>';
                            
                            if ($errorCode == 2002 || $errorCode == 2003) {
                                echo '<p><strong>MySQL belum berjalan!</strong></p>';
                                echo '<p>Silakan lakukan langkah berikut:</p>';
                                echo '<ol>';
                                echo '<li>Buka <strong>XAMPP Control Panel</strong></li>';
                                echo '<li>Klik tombol <strong>Start</strong> pada bagian <strong>MySQL</strong></li>';
                                echo '<li>Tunggu hingga MySQL berwarna hijau</li>';
                                echo '<li>Refresh halaman ini (tekan F5)</li>';
                                echo '</ol>';
                            } else {
                                echo '<p>Error: ' . htmlspecialchars($errorMsg) . '</p>';
                                echo '<p>Error Code: ' . $errorCode . '</p>';
                            }
                            
                            echo '</div>';
                            echo '<div class="text-center mt-3">';
                            echo '<button class="btn btn-primary" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh Halaman</button>';
                            echo '</div>';
                            exit;
                        }

                        // Re-enable error reporting
                        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                        // Create database
                        $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                        if ($conn->query($sql) === TRUE) {
                            echo '<div class="alert alert-success">✓ Database created successfully or already exists</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error creating database: ' . $conn->error . '</div>';
                            exit;
                        }

                        // Close and reconnect to the specific database
                        $conn->close();
                        $conn = new mysqli($host, $user, $pass, $dbname);
                        
                        // Check connection
                        if ($conn->connect_error) {
                            echo '<div class="alert alert-danger">Error connecting to database: ' . $conn->connect_error . '</div>';
                            exit;
                        }
                        
                        $conn->set_charset("utf8mb4");

                        // Create products table
                        $sql = "CREATE TABLE IF NOT EXISTS products (
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
                            echo '<div class="alert alert-success">✓ Products table created successfully</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error creating table: ' . $conn->error . '</div>';
                            echo '<div class="mt-3"><button class="btn btn-primary" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh Halaman</button></div>';
                            $conn->close();
                            exit;
                        }

                        // Insert sample data
                        try {
                            $check = $conn->query("SELECT COUNT(*) as count FROM products");
                            $row = $check->fetch_assoc();
                            
                            if ($row['count'] == 0) {
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
                                
                                echo '<div class="alert alert-success">✓ Sample data inserted successfully</div>';
                                $stmt->close();
                            } else {
                                echo '<div class="alert alert-info">ℹ Sample data already exists</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="alert alert-warning">Warning: Could not insert sample data: ' . $e->getMessage() . '</div>';
                        }

                        $conn->close();
                        ?>
                        
                        <div class="mt-4">
                            <h5>Installation Complete!</h5>
                            <p>Your database has been set up successfully. You can now use the application.</p>
                            <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
