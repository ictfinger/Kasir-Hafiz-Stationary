<?php
require_once 'config.php';

$pageTitle = 'Dashboard';

// Check if database and table exist
try {
    $conn = getConnection();
    
    // Check if products table exists
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows == 0) {
        // Table doesn't exist, redirect to install
        header('Location: install.php');
        exit;
    }
    
    // Get statistics
    $totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
    $totalStock = $conn->query("SELECT SUM(stock) as total FROM products")->fetch_assoc()['total'];
    $totalValue = $conn->query("SELECT SUM(price * stock) as total FROM products")->fetch_assoc()['total'];
    $totalCategories = $conn->query("SELECT COUNT(DISTINCT category) as count FROM products")->fetch_assoc()['count'];

    // Get recent products
    $recentProducts = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    
} catch (Exception $e) {
    // If any error, redirect to install
    header('Location: install.php');
    exit;
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <p>Selamat datang di sistem kasir Hafiz Stationary</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stats-title">Total Produk</div>
            <div class="stats-value"><?php echo number_format($totalProducts); ?></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bi bi-boxes"></i>
            </div>
            <div class="stats-title">Total Stok</div>
            <div class="stats-value"><?php echo number_format($totalStock); ?></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon warning">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stats-title">Nilai Stok</div>
            <div class="stats-value">Rp <?php echo number_format($totalValue, 0, ',', '.'); ?></div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="bi bi-tags"></i>
            </div>
            <div class="stats-title">Kategori</div>
            <div class="stats-value"><?php echo number_format($totalCategories); ?></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="tambah_produk.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Produk Baru
                    </a>
                    <a href="daftar_produk.php" class="btn btn-success">
                        <i class="bi bi-list-ul"></i> Lihat Semua Produk
                    </a>
                    <button class="btn btn-warning" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row">
    <div class="col-12">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Produk Terbaru
            </div>
            <div class="card-body">
                <?php if ($recentProducts->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Ditambahkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $recentProducts->fetch_assoc()): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($product['barcode']); ?></code></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php echo $product['stock'] > 10 ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada produk. <a href="tambah_produk.php">Tambah produk pertama</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
