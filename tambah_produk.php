<?php
require_once 'config.php';

$pageTitle = 'Tambah Produk';
$useBarcode = true;

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-plus-circle"></i> Tambah Produk Baru</h1>
    <p>Tambahkan produk baru dengan barcode otomatis</p>
</div>

<!-- Alert Section -->
<div id="alertSection"></div>

<!-- Add Product Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-card-list"></i> Form Produk
            </div>
            <div class="card-body">
                <form id="productForm">
                    <div class="mb-3">
                        <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="barcode" name="barcode" required readonly>
                            <button class="btn btn-primary" type="button" id="generateBarcodeBtn">
                                <i class="bi bi-upc-scan"></i> Generate Barcode
                            </button>
                        </div>
                        <small class="text-muted">Klik tombol untuk generate barcode otomatis</small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Contoh: Pulpen Standard Hitam">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="price" name="price" required min="0" step="100" placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" required min="0" placeholder="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Buku">Buku</option>
                            <option value="Kertas">Kertas</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Simpan Produk
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                        <a href="daftar_produk.php" class="btn btn-warning">
                            <i class="bi bi-list-ul"></i> Lihat Daftar Produk
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="custom-card">
            <div class="card-header">
                <i class="bi bi-upc"></i> Preview Barcode
            </div>
            <div class="card-body">
                <div class="barcode-container">
                    <div id="barcodePreview">
                        <p class="text-muted">
                            <i class="bi bi-upc-scan" style="font-size: 3rem;"></i><br>
                            Generate barcode untuk melihat preview
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Barcode akan otomatis digenerate dalam format EAN-13
                    </small>
                </div>
            </div>
        </div>

        <div class="custom-card mt-3">
            <div class="card-header">
                <i class="bi bi-lightbulb"></i> Tips
            </div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.9rem;">
                    <li>Generate barcode sebelum mengisi form</li>
                    <li>Pastikan nama produk jelas dan spesifik</li>
                    <li>Input harga tanpa titik atau koma</li>
                    <li>Pilih kategori yang sesuai</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Generate Barcode
document.getElementById('generateBarcodeBtn').addEventListener('click', async function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    
    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';
    
    try {
        const response = await fetch('api/generate_barcode.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('barcode').value = data.barcode;
            
            // Generate barcode image
            const barcodePreview = document.getElementById('barcodePreview');
            barcodePreview.innerHTML = '<svg id="barcodeSvg"></svg>';
            JsBarcode("#barcodeSvg", data.barcode, {
                format: "EAN13",
                width: 2,
                height: 60,
                displayValue: true
            });
            
            showAlert('success', 'Barcode berhasil digenerate!');
        } else {
            showAlert('danger', data.message || 'Gagal generate barcode');
        }
    } catch (error) {
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

// Submit Form
document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalHtml = submitBtn.innerHTML;
    
    // Validate barcode
    if (!document.getElementById('barcode').value) {
        showAlert('warning', 'Silakan generate barcode terlebih dahulu!');
        return;
    }
    
    // Show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('api/save_product.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Produk berhasil disimpan!');
            this.reset();
            document.getElementById('barcodePreview').innerHTML = '<p class="text-muted"><i class="bi bi-upc-scan" style="font-size: 3rem;"></i><br>Generate barcode untuk melihat preview</p>';
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'daftar_produk.php';
            }, 2000);
        } else {
            showAlert('danger', data.message || 'Gagal menyimpan produk');
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
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertSection.innerHTML = '';
    }, 5000);
}
</script>

<?php include 'includes/footer.php'; ?>
