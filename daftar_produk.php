<?php
require_once 'config.php';

$pageTitle = 'Daftar Produk';
$useDataTables = true;
$useBarcode = true;

$conn = getConnection();
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-list-ul"></i> Daftar Produk</h1>
            <p>Kelola semua produk Anda</p>
        </div>
        <div>
            <a href="tambah_produk.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Produk
            </a>
        </div>
    </div>
</div>

<!-- Alert Section -->
<div id="alertSection"></div>

<!-- Products Table -->
<div class="custom-card">
    <div class="card-header">
        <i class="bi bi-table"></i> Semua Produk
    </div>
    <div class="card-body">
        <?php if ($products->num_rows > 0): ?>
        <div class="table-responsive">
            <table id="productsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Ditambahkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <code><?php echo htmlspecialchars($product['barcode']); ?></code>
                            <br>
                            <button class="btn btn-sm btn-outline-primary mt-1" onclick="showBarcode('<?php echo htmlspecialchars($product['barcode']); ?>', '<?php echo htmlspecialchars($product['name']); ?>')">
                                <i class="bi bi-upc"></i> Lihat
                            </button>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span></td>
                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge <?php echo $product['stock'] > 10 ? 'bg-success' : ($product['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
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

<!-- Barcode Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upc"></i> Barcode Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h6 id="modalProductName" class="mb-3"></h6>
                <div class="barcode-container">
                    <svg id="modalBarcode"></svg>
                </div>
                <p class="mt-3 mb-0"><code id="modalBarcodeNumber"></code></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printBarcode()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#productsTable').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        order: [[0, 'desc']]
    });
});

// Show Barcode in Modal
function showBarcode(barcode, name) {
    document.getElementById('modalProductName').textContent = name;
    document.getElementById('modalBarcodeNumber').textContent = barcode;
    
    // Generate barcode
    JsBarcode("#modalBarcode", barcode, {
        format: "EAN13",
        width: 2,
        height: 80,
        displayValue: true
    });
    
    const modal = new bootstrap.Modal(document.getElementById('barcodeModal'));
    modal.show();
}

// Print Barcode
function printBarcode() {
    const barcodeSvg = document.getElementById('modalBarcode').outerHTML;
    const productName = document.getElementById('modalProductName').textContent;
    
    const printWindow = window.open('', '', 'width=600,height=400');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Barcode</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                h3 { margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <h3>${productName}</h3>
            ${barcodeSvg}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                }
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Delete Product
async function deleteProduct(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Produk berhasil dihapus!');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Gagal menghapus produk');
        }
    } catch (error) {
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
    }
}

function showAlert(type, message) {
    const alertSection = document.getElementById('alertSection');
    alertSection.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    setTimeout(() => {
        alertSection.innerHTML = '';
    }, 5000);
}
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
