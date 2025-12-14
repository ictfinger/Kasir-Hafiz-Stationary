<?php
require_once 'config.php';

$pageTitle = 'Riwayat Transaksi';
$useDataTables = true;

// Check if tables exist
try {
    $conn = getConnection();
    $result = $conn->query("SHOW TABLES LIKE 'transactions'");
    if ($result->num_rows == 0) {
        header('Location: reset_database.php');
        exit;
    }
    
    // Get all transactions
    $transactions = $conn->query("SELECT * FROM transactions ORDER BY created_at DESC");
    
} catch (Exception $e) {
    header('Location: reset_database.php');
    exit;
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-clock-history"></i> Riwayat Transaksi</h1>
            <p>Lihat dan cetak ulang semua transaksi</p>
        </div>
        <div>
            <a href="transaksi.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Transaksi Baru
            </a>
        </div>
    </div>
</div>

<!-- Alert Section -->
<div id="alertSection"></div>

<!-- Transactions Table -->
<div class="custom-card">
    <div class="card-header">
        <i class="bi bi-table"></i> Semua Transaksi
    </div>
    <div class="card-body">
        <?php if ($transactions->num_rows > 0): ?>
        <div class="table-responsive">
            <table id="transactionsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Total Item</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembali</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($transaction['transaction_code']); ?></code></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($transaction['cashier_name']); ?></td>
                        <td><span class="badge bg-primary"><?php echo $transaction['total_items']; ?></span></td>
                        <td><strong>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></strong></td>
                        <td>Rp <?php echo number_format($transaction['payment_amount'], 0, ',', '.'); ?></td>
                        <td class="text-success">Rp <?php echo number_format($transaction['change_amount'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $transaction['payment_method'] == 'Tunai' ? 'success' : 'info'; ?>">
                                <?php echo $transaction['payment_method']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewDetails(<?php echo $transaction['id']; ?>)">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada transaksi. <a href="transaksi.php">Mulai transaksi baru</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Get today's statistics
$today = date('Y-m-d');
$todayStats = $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM transactions WHERE DATE(created_at) = '$today'")->fetch_assoc();
$conn->close();
?>

<!-- Daily Statistics -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stats-title">Transaksi Hari Ini</div>
            <div class="stats-value"><?php echo number_format($todayStats['count'] ?? 0); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stats-title">Pendapatan Hari Ini</div>
            <div class="stats-value">Rp <?php echo number_format($todayStats['revenue'] ?? 0, 0, ',', '.'); ?></div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Detail Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="printDetailBtn" onclick="printDetail()">
                    <i class="bi bi-printer"></i> Print Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#transactionsTable').DataTable({
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
        order: [[1, 'desc']]
    });
});

let currentTransactionDetail = null;

// View transaction details
async function viewDetails(id) {
    try {
        const response = await fetch(`api/get_transaction_details.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            currentTransactionDetail = data;
            showDetail(data.transaction, data.items);
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
    }
}

// Show transaction detail
function showDetail(transaction, items) {
    const detailContent = document.getElementById('detailContent');
    
    let html = `
        <div class="receipt-print" id="detailPrint">
            <div class="text-center mb-4">
                <h3>HAFIZ STATIONARY</h3>
                <p class="mb-0">Jl. Raya Stationary No. 123</p>
                <p class="mb-0">Telp: (021) 1234567</p>
                <hr>
            </div>
            
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="150">No. Transaksi</td>
                    <td>: <strong>${transaction.transaction_code}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: ${new Date(transaction.created_at).toLocaleString('id-ID')}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>: ${transaction.cashier_name}</td>
                </tr>
            </table>
            
            <hr>
            
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    items.forEach(item => {
        html += `
                    <tr>
                        <td>${item.product_name}<br><small class="text-muted">${item.barcode}</small></td>
                        <td class="text-end">${item.quantity}</td>
                        <td class="text-end">Rp ${formatNumber(item.price)}</td>
                        <td class="text-end">Rp ${formatNumber(item.subtotal)}</td>
                    </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
            
            <hr>
            
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Total Item:</strong></td>
                    <td class="text-end"><strong>${transaction.total_items}</strong></td>
                </tr>
                <tr class="fs-5">
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-end"><strong>Rp ${formatNumber(transaction.total_amount)}</strong></td>
                </tr>
                <tr>
                    <td>Bayar (${transaction.payment_method}):</td>
                    <td class="text-end">Rp ${formatNumber(transaction.payment_amount)}</td>
                </tr>
                <tr class="text-success">
                    <td><strong>Kembalian:</strong></td>
                    <td class="text-end"><strong>Rp ${formatNumber(transaction.change_amount)}</strong></td>
                </tr>
            </table>
            
            <hr>
            
            <div class="text-center">
                <p class="mb-0">Terima kasih atas kunjungan Anda!</p>
                <p class="mb-0">Barang yang sudah dibeli tidak dapat dikembalikan</p>
            </div>
        </div>
    `;
    
    detailContent.innerHTML = html;
    
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
}

// Print detail
function printDetail() {
    const detailContent = document.getElementById('detailPrint').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Struk</title>
            <style>
                body { font-family: 'Courier New', monospace; padding: 20px; }
                table { width: 100%; }
                hr { border: 1px dashed #000; }
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .text-success { color: #10b981; }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            ${detailContent}
            <script>
                window.onload = function() {
                    window.print();
                    window.close();
                }
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(Math.round(num));
}

// Show alert
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

<?php include 'includes/footer.php'; ?>
