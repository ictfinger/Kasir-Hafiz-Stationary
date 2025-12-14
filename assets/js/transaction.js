// Transaction System JavaScript
let cart = [];
let currentTransaction = null;

// Auto-focus barcode input
document.addEventListener('DOMContentLoaded', function () {
    const barcodeInput = document.getElementById('barcodeInput');

    // Focus on load
    barcodeInput.focus();

    // Re-focus when modal closes
    document.addEventListener('hidden.bs.modal', function () {
        setTimeout(() => barcodeInput.focus(), 500);
    });

    // Handle barcode input
    barcodeInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            scanProduct();
        }
    });

    // Auto-calculate change
    document.getElementById('paymentAmount').addEventListener('input', calculateChange);
});

// Scan product by barcode
async function scanProduct() {
    const barcodeInput = document.getElementById('barcodeInput');
    const barcode = barcodeInput.value.trim();

    if (!barcode) {
        showAlert('warning', 'Silakan input barcode');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('barcode', barcode);

        const response = await fetch('api/scan_product.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            addToCart(data.product);
            barcodeInput.value = '';
            playBeep();
        } else {
            showAlert('danger', data.message);
            playError();
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
    }

    barcodeInput.focus();
}

// Add product to cart
function addToCart(product) {
    const existingItem = cart.find(item => item.product_id === product.id);

    if (existingItem) {
        // Check stock
        if (existingItem.quantity >= product.stock) {
            showAlert('warning', `Stok ${product.name} tidak cukup! Tersedia: ${product.stock}`);
            return;
        }
        existingItem.quantity++;
    } else {
        cart.push({
            product_id: product.id,
            barcode: product.barcode,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            stock: product.stock
        });
    }

    updateCartDisplay();
    showAlert('success', `${product.name} ditambahkan ke keranjang`, 1500);
}

// Update cart display
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const processBtn = document.getElementById('processBtn');

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <tr class="cart-empty">
                <td colspan="5">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mb-0 mt-2">Keranjang kosong</p>
                    <small>Scan barcode untuk menambah produk</small>
                </td>
            </tr>
        `;
        clearCartBtn.style.display = 'none';
        processBtn.disabled = true;
    } else {
        let html = '';
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            html += `
                <tr>
                    <td>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">${item.barcode}</small>
                    </td>
                    <td>Rp ${formatNumber(item.price)}</td>
                    <td>
                        <div class="qty-control">
                            <button class="btn btn-sm btn-outline-secondary qty-btn" onclick="updateQuantity(${index}, -1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center" value="${item.quantity}" 
                                   onchange="setQuantity(${index}, this.value)" min="1" max="${item.stock}" style="width: 60px;">
                            <button class="btn btn-sm btn-outline-secondary qty-btn" onclick="updateQuantity(${index}, 1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <small class="text-muted">Stok: ${item.stock}</small>
                    </td>
                    <td><strong>Rp ${formatNumber(subtotal)}</strong></td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        cartItems.innerHTML = html;
        clearCartBtn.style.display = 'inline-block';
        processBtn.disabled = false;
    }

    updateTotal();
    calculateChange();
}

// Update quantity
function updateQuantity(index, change) {
    const item = cart[index];
    const newQty = item.quantity + change;

    if (newQty < 1) {
        removeFromCart(index);
        return;
    }

    if (newQty > item.stock) {
        showAlert('warning', `Stok ${item.name} tidak cukup! Tersedia: ${item.stock}`);
        return;
    }

    item.quantity = newQty;
    updateCartDisplay();
}

// Set quantity directly
function setQuantity(index, value) {
    const qty = parseInt(value);
    const item = cart[index];

    if (isNaN(qty) || qty < 1) {
        removeFromCart(index);
        return;
    }

    if (qty > item.stock) {
        showAlert('warning', `Stok ${item.name} tidak cukup! Tersedia: ${item.stock}`);
        item.quantity = item.stock;
    } else {
        item.quantity = qty;
    }

    updateCartDisplay();
}

// Remove from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

// Clear cart
function clearCart() {
    if (!confirm('Kosongkan keranjang belanja?')) return;
    cart = [];
    updateCartDisplay();
}

// Clear barcode input
function clearBarcode() {
    document.getElementById('barcodeInput').value = '';
    document.getElementById('barcodeInput').focus();
}

// Update total
function updateTotal() {
    let totalItems = 0;
    let totalAmount = 0;

    cart.forEach(item => {
        totalItems += item.quantity;
        totalAmount += item.price * item.quantity;
    });

    document.getElementById('totalItems').textContent = totalItems;
    document.getElementById('totalAmount').textContent = formatNumber(totalAmount);
    document.getElementById('totalAmountPayment').textContent = formatNumber(totalAmount);
}

// Calculate change
function calculateChange() {
    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const change = paymentAmount - totalAmount;

    document.getElementById('changeAmount').textContent = formatNumber(Math.max(0, change));
}

// Process transaction
async function processTransaction() {
    if (cart.length === 0) {
        showAlert('warning', 'Keranjang belanja kosong!');
        return;
    }

    const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    if (paymentAmount < totalAmount) {
        showAlert('danger', 'Jumlah pembayaran kurang!');
        document.getElementById('paymentAmount').focus();
        return;
    }

    const paymentMethod = document.getElementById('paymentMethod').value;
    const cashierName = document.getElementById('cashierName').value || 'Admin';

    const processBtn = document.getElementById('processBtn');
    const originalHtml = processBtn.innerHTML;
    processBtn.disabled = true;
    processBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    try {
        const response = await fetch('api/process_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart: cart,
                payment_amount: paymentAmount,
                payment_method: paymentMethod,
                cashier_name: cashierName
            })
        });

        const data = await response.json();

        if (data.success) {
            currentTransaction = data.transaction;
            showReceipt(data.transaction);
            cart = [];
            updateCartDisplay();
            document.getElementById('paymentAmount').value = '';
            showAlert('success', 'Transaksi berhasil diproses!');
        } else {
            showAlert('danger', data.message);
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
    } finally {
        processBtn.disabled = false;
        processBtn.innerHTML = originalHtml;
    }
}

// Show receipt
function showReceipt(transaction) {
    const receiptContent = document.getElementById('receiptContent');

    let html = `
        <div class="receipt-print" id="receiptPrint">
            <div class="text-center mb-4">
                <h3>HAFIZ STATIONARY</h3>
                <p class="mb-0">Jl. Raya Stationary No. 123</p>
                <p class="mb-0">Telp: (021) 1234567</p>
                <hr>
            </div>
            
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="150">No. Transaksi</td>
                    <td>: <strong>${transaction.code}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>: ${new Date().toLocaleString('id-ID')}</td>
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

    transaction.items.forEach(item => {
        html += `
                    <tr>
                        <td>${item.name}<br><small class="text-muted">${item.barcode}</small></td>
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

    receiptContent.innerHTML = html;

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

// Print receipt
function printReceipt() {
    const receiptContent = document.getElementById('receiptPrint').innerHTML;
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
            ${receiptContent}
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

// New transaction
function newTransaction() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
    modal.hide();
    document.getElementById('barcodeInput').focus();
}

// Play beep sound on successful scan
function playBeep() {
    const beep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBDGH0fPTgjMGHm7A7+OZURE');
    beep.play().catch(() => { });
}

// Play error sound
function playError() {
    // Simple error beep
    const errorBeep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBDGH0fPTgjMGHm7A7+OZURE');
    errorBeep.play().catch(() => { });
}

// Format number with thousand separator
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(Math.round(num));
}

// Show alert
function showAlert(type, message, duration = 5000) {
    const alertSection = document.getElementById('alertSection');
    const alertId = 'alert_' + Date.now();

    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="${alertId}">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    alertSection.insertAdjacentHTML('beforeend', alertHTML);

    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, duration);
}
