    </div>
    
    <!-- Footer -->
    <footer class="mt-5 py-4" style="background: linear-gradient(135deg, #4f46e5, #4338ca); color: white;">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Kasir Hafiz Stationary. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for DataTables) -->
    <?php if (isset($useDataTables) && $useDataTables): ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <?php endif; ?>
    
    <!-- JsBarcode -->
    <?php if (isset($useBarcode) && $useBarcode): ?>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="assets/js/barcode-generator.js"></script>
    <?php endif; ?>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
