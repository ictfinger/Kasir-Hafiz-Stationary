<?php
require_once 'config.php';

$pageTitle = 'Edit Produk';
$useBarcode = true;

if (!isset($_GET['id'])) {
    header('Location: daftar_produk.php');
    exit;
}

$id = $_GET['id'];
$conn = getConnection();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: daftar_produk.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-pencil"></i> Edit Produk</h1>
    <p>Edit informasi produk</p>
</div>

<!-- Alert Section -->
<div id="alertSection"></div>

<!-- Edit Product Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-card-list"></i> Form Edit Produk
            </div>
            <div class="card-body">
                <form id="editProductForm">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo htmlspecialchars($product['barcode']); ?>" readonly>
                        <small class="text-muted">Barcode tidak dapat diubah</small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required min="0" step="100">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Alat Tulis" <?php echo $product['category'] == 'Alat Tulis' ? 'selected' : ''; ?>>Alat Tulis</option>
                            <option value="Buku" <?php echo $product['category'] == 'Buku' ? 'selected' : ''; ?>>Buku</option>
                            <option value="Kertas" <?php echo $product['category'] == 'Kertas' ? 'selected' : ''; ?>>Kertas</option>
                            <option value="Elektronik" <?php echo $product['category'] == 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="Aksesoris" <?php echo $product['category'] == 'Aksesoris' ? 'selected' : ''; ?>>Aksesoris</option>
                            <option value="Lainnya" <?php echo $product['category'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Update Produk
                        </button>
                        <a href="daftar_produk.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-upc"></i> Barcode
            </div>
            <div class="card-body">
                <div class="barcode-container">
                    <svg id="barcodeDisplay"></svg>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Display current barcode
JsBarcode("#barcodeDisplay", "<?php echo $product['barcode']; ?>", {
    format: "EAN13",
    width: 2,
    height: 60,
    displayValue: true
});

// Submit Form
document.getElementById('editProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalHtml = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('api/update_product.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Produk berhasil diupdate!');
            setTimeout(() => {
                window.location.href = 'daftar_produk.php';
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Gagal mengupdate produk');
        }
    } catch (error) {
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
});

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
