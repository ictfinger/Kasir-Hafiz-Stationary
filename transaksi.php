<?php
require_once 'config.php';

$pageTitle = 'Transaksi';

// Check if tables exist
try {
    $conn = getConnection();
    $result = $conn->query("SHOW TABLES LIKE 'transactions'");
    if ($result->num_rows == 0) {
        header('Location: reset_database.php');
        exit;
    }
    $conn->close();
} catch (Exception $e) {
    header('Location: reset_database.php');
    exit;
}

include 'includes/header.php';
?>

<style>
.transaction-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 992px) {
    .transaction-container {
        grid-template-columns: 1fr;
    }
}

.barcode-input-section {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    padding: 2rem;
    border-radius: 1rem;
    color: white;
    box-shadow: var(--card-shadow-hover);
}

.barcode-input-section input {
    font-size: 1.5rem;
    padding: 1rem;
    border: 3px solid white;
}

.cart-table {
    background: white;
}

.cart-empty {
    text-align: center;
    padding: 3rem;
    color: #94a3b8;
}

.total-section {
    background: linear-gradient(135deg, var(--success-color), #059669);
    color: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-top: 1rem;
}

.total-amount {
    font-size: 2.5rem;
    font-weight: 700;
}

.payment-section {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: var(--card-shadow);
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-cart-check"></i> Transaksi Penjualan</h1>
    <p>Scan barcode atau input manual untuk menambah produk</p>
</div>

<!-- Alert Section -->
<div id="alertSection"></div>

<div class="transaction-container">
    <!-- Left Section: Cart -->
    <div>
        <!-- Barcode Scanner -->
        <div class="barcode-input-section">
            <h4 class="mb-3"><i class="bi bi-upc-scan"></i> Scanner Barcode</h4>
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white">
                    <i class="bi bi-upc-scan"></i>
                </span>
                <input type="text" class="form-control" id="barcodeInput" placeholder="Scan atau ketik barcode produk..." autofocus>
                <button class="btn btn-light" type="button" onclick="clearBarcode()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <small class="d-block mt-2 opacity-75">
                <i class="bi bi-info-circle"></i> Tekan Enter atau scan barcode untuk menambah produk
            </small>
        </div>

        <!-- Shopping Cart -->
        <div class="custom-card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart3"></i> Keranjang Belanja</span>
                <button class="btn btn-sm btn-danger" onclick="clearCart()" id="clearCartBtn" style="display: none;">
                    <i class="bi bi-trash"></i> Kosongkan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table cart-table mb-0">
                        <thead>
                            <tr>
                                <th width="40%">Produk</th>
                                <th width="20%">Harga</th>
                                <th width="25%">Qty</th>
                                <th width="15%">Subtotal</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr class="cart-empty">
                                <td colspan="5">
                                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">Keranjang kosong</p>
                                    <small>Scan barcode untuk menambah produk</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div>Total Item: <strong id="totalItems">0</strong></div>
                    <div class="total-amount">Rp <span id="totalAmount">0</span></div>
                </div>
                <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <!-- Right Section: Payment -->
    <div>
        <div class="payment-section">
            <h5 class="mb-3"><i class="bi bi-credit-card"></i> Pembayaran</h5>
            
            <div class="mb-3">
                <label class="form-label">Metode Pembayaran</label>
                <select class="form-select" id="paymentMethod">
                    <option value="Tunai">Tunai</option>
                    <option value="Transfer">Transfer</option>
                    <option value="Kartu">Kartu Debit/Kredit</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Kasir</label>
                <input type="text" class="form-control" id="cashierName" value="Admin">
            </div>

            <div class="mb-3">
                <label class="form-label">Jumlah Bayar</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control form-control-lg" id="paymentAmount" placeholder="0" min="0">
                </div>
            </div>

            <div class="mb-3 p-3 bg-light rounded">
                <div class="d-flex justify-content-between mb-2">
                    <strong>Total Belanja:</strong>
                    <strong>Rp <span id="totalAmountPayment">0</span></strong>
                </div>
                <div class="d-flex justify-content-between text-success">
                    <strong>Kembalian:</strong>
                    <strong class="fs-4">Rp <span id="changeAmount">0</span></strong>
                </div>
            </div>

            <button class="btn btn-success btn-lg w-100" id="processBtn" onclick="processTransaction()" disabled>
                <i class="bi bi-check-circle"></i> Proses Transaksi
            </button>

            <button class="btn btn-warning w-100 mt-2" onclick="location.href='riwayat_transaksi.php'">
                <i class="bi bi-clock-history"></i> Lihat Riwayat
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Transaksi Berhasil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt will be generated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="bi bi-printer"></i> Print Struk
                </button>
                <button type="button" class="btn btn-success" onclick="newTransaction()">
                    <i class="bi bi-plus-circle"></i> Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/transaction.js"></script>

<?php include 'includes/footer.php'; ?>
